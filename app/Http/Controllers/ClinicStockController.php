<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Clinic;
use App\Models\Inventory\Warehouse;
use App\Models\Master\Drug;
use App\Models\Transaction\Retur;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Transaction\Trash;
use Illuminate\Http\Request;

class ClinicStockController extends Controller
{
    //function API endpoint untuk melakukan live search pada stok obat klinik
    public function searchClinicStock(Request $request)
    {
        $query = $request->input('query');
        $drugs = Drug::where('name', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%")->pluck('id');
        $clinic = Clinic::with('data')->whereIn('drug_id',$drugs)->get();

        return response()->json($clinic);
    }
    public function index()
    {
        $judul = "Stok Obat Klinik";
        $stocks = Clinic::paginate(5);
        return view("pages.clinic.stock",compact('judul','stocks'));
    }
    public function show(Drug $stock)
    {
        $judul = "Stok ".$stock->name;
        $drug = $stock;
        //mengambil data stok obat klinik
        $stock = Clinic::where('drug_id',$drug->id)->first();
        
        $inflow = Transaction::where('destination', 'clinic')->whereIn('variant', ['LPK', 'Trash', 'Retur'])->pluck('id');
        $details = TransactionDetail::where('drug_id',$drug->id)
            ->whereIn('transaction_id',$inflow)
            ->whereNot('stock',0)
            ->selectRaw('MIN(id) as id, expired, SUM(stock) as stock, drug_id')
            ->groupBy('expired', 'drug_id')
            ->orderBy('expired')
            ->paginate(10,['*'],'expired');
        $transactions = TransactionDetail::where('drug_id',$drug->id)
            ->whereIn('transaction_id',$inflow)
            ->orderBy('created_at')
            ->paginate(10,['*'],'transaction');
        return view("pages.clinic.stockDetail",compact('drug','stock','judul','details','transactions'));
    }
    public function retur(Request $request,TransactionDetail $batch)
    {
        $drug = $batch->drug();
        $judul = "Retur Obat ". $drug->name;
        if($request->isMethod('get')){
            return view("pages.clinic.retur",compact('batch','judul'));
        }elseif ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                session()->flash('error', 'Gagal mengembalikan obat');
                return redirect()->back()->withInput();
            }
            $vendor_id = null;
            if ($batch->transaction->variant === 'LPK') {
                $warehouseTransaction = TransactionDetail::where('drug_id', $drug->id)
                    ->where('stock', '>', 0)
                    ->whereHas('transaction', function($q) {
                        $q->where('variant', 'LPB');
                    })
                    ->first();
                
                if (!$warehouseTransaction) {
                    throw new \Exception('Could not find original warehouse transaction for this drug');
                }
                $vendor_id = $warehouseTransaction->transaction->vendor_id;
            } else {
                $vendor_id = $batch->transaction->vendor_id;
            }

            if (!$vendor_id) {
                throw new \Exception('Could not determine vendor for return');
            }

            $transaction = Transaction::create([
                "vendor_id" => $vendor_id,
                "destination" => "clinic",
                "variant" => "Retur",
            ]);
            $detail = TransactionDetail::create([
                "transaction_id"=>$transaction->id,
                "drug_id"=> $drug->id,
                "expired"=>$batch->expired,
                "name"=>$drug->name." 1 pcs",
                "quantity"=>$request->quantity." pcs",
                "piece_price"=>$drug->last_price,
                "total_price"=>$request->quantity * $drug->last_price,
                "flow"=>0
            ]);
            $transaction->generate_code();
            Retur::create([
                "drug_id"=> $drug->id,
                "transaction_id"=>$transaction->id,
                "transaction_detail_id"=>$detail->id,
                "source"=>$batch->id,
                "quantity"=>$request->quantity * $drug->piece_netto,
                "status"=>"Belum Kembali",
                "reason"=>$request->reason,
            ]);
            $batch->stock = $batch->stock - $request->quantity*$drug->piece_netto;
            $batch->save();
            
            $clinic = Clinic::where('drug_id',$drug->id)->first();
            if (!$clinic) {
                throw new \Exception('Clinic stock not found for this drug');
            }
            $clinic->quantity = $clinic->quantity - $request->quantity*$drug->piece_netto;
            $clinic->save();
            
            return redirect()->route('clinic.stocks.show',$drug->id)->with('success','Berhasil melakukan retur');
        }
    }
    public function trash(Request $request,TransactionDetail $batch)
    {
        $drug = $batch->drug();
        $judul = "Buang Obat ". $drug->name;
        if($request->isMethod('get')){
            return view("pages.clinic.trash",compact('batch','judul'));
            //pembuatan barang buang
        }elseif($request->isMethod('post')){
            $validator = \Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                session()->flash('error', 'Gagal membuang obat');
                return redirect()->back()->withInput();
            }
            $vendor_id = null;
            if ($batch->transaction->variant === 'LPK') {
                $warehouseTransaction = TransactionDetail::where('drug_id', $drug->id)
                    ->where('stock', '>', 0)
                    ->whereHas('transaction', function($q) {
                        $q->where('variant', 'LPB');
                    })
                    ->first();
                
                if (!$warehouseTransaction) {
                    throw new \Exception('Could not find original warehouse transaction for this drug');
                }
                $vendor_id = $warehouseTransaction->transaction->vendor_id;
            } else {
                $vendor_id = $batch->transaction->vendor_id;
            }

            if (!$vendor_id) {
                throw new \Exception('Could not determine vendor for trash');
            }

            $transaction = Transaction::create([
                "vendor_id" => $vendor_id,
                "destination"=>"clinic",
                "variant"=>"Trash",
                "loss"=>$request->quantity*$drug->last_price
            ]);
            $detail = TransactionDetail::create([
                "transaction_id"=>$transaction->id,
                "drug_id"=> $drug->id,
                "expired"=>$batch->expired,
                "name"=>$drug->name." 1 pcs",
                "quantity"=>$request->quantity." pcs",
                "piece_price"=>$drug->last_price,
                "total_price"=>$request->quantity * $drug->last_price,
                "flow"=>$request->quantity*$drug->piece_netto*-1
            ]);
            $transaction->generate_code();
            Trash::create([
                "drug_id"=> $drug->id,
                "transaction_id"=>$transaction->id,
                "transaction_detail_id"=>$detail->id,
                "quantity"=>$request->quantity * $drug->piece_netto,
                "reason"=>$request->reason,
            ]);
            $batch->stock = $batch->stock - $request->quantity*$drug->piece_netto;
            $batch->save();
            
            $clinic = Clinic::where('drug_id',$drug->id)->first();
            if (!$clinic) {
                throw new \Exception('Clinic stock not found for this drug');
            }
            $clinic->quantity = $clinic->quantity - $request->quantity*$drug->piece_netto;
            $clinic->save();
            
            return redirect()->route('clinic.stocks.show',$drug->id)->with('success','Berhasil melakukan pembuangan');
        }
    }
}
