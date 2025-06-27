<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Warehouse;
use App\Models\Master\Drug;
use App\Models\Transaction\Retur;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use App\Models\Transaction\Trash;
use Illuminate\Http\Request;

class InventoryStockController extends Controller
{
    //function API endpoint untuk melakukan live search pada inventori stok
    public function searchInventoryStock(Request $request)
    {
        $query = $request->input('query');
        $drugs = Drug::where('name', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%")->pluck('id');
        $warehouse = Warehouse::with('data')->whereIn('drug_id',$drugs)->get();

        return response()->json($warehouse);
    }
    public function index()
    {
        $judul = "Stok Obat Gudang";
        $stocks = Warehouse::paginate(5);
        return view("pages.inventory.stock",compact('judul','stocks'));
    }
    public function show(Drug $stock)
    {
        $judul = "Stok ".$stock->name;
        $drug = $stock;
        $stock = Warehouse::where('drug_id',$drug->id)->first();
        $inflow = Transaction::where('variant','LPB')->pluck('id');
        $details = TransactionDetail::where('drug_id',$drug->id)
            ->whereIn('transaction_id',$inflow)
            ->whereNot('stock',0)
            ->selectRaw('MIN(id) as id, expired, SUM(stock) as stock, drug_id')
            ->groupBy('expired', 'drug_id')
            ->orderBy('expired')
            ->paginate(10,['*'],'expired');
        $transactions = TransactionDetail::with('transaction')->where('drug_id',$drug->id)->orderBy('created_at')->paginate(5,['*'],'transaction');
        return view("pages.inventory.stockDetail",compact('drug','stock','judul','details','transactions'));
    }
    public function retur(Request $request,TransactionDetail $batch)
    {
        $drug = $batch->drug();
        $judul = "Retur Obat ". $drug->name;
        if($request->isMethod('get')){
            return view("pages.inventory.retur",compact('batch','judul'));
        }elseif ($request->isMethod('post')) {
            $validator = \Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                session()->flash('error', 'Gagal mengembalikan obat');
                return redirect()->back()->withInput();
            }
            $transaction = Transaction::create([
                "vendor_id"=>$batch->transaction->vendor()->id,
                "destination"=>"warehouse",
                "variant"=>"Retur",
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
            $warehouse = Warehouse::where('drug_id',$drug->id)->first();
            $warehouse->quantity = $warehouse->quantity - $request->quantity*$drug->piece_netto;
            $warehouse->save();
            if (auth()->user()->role=='doctor') {
                return redirect()->route('clinic.stocks.show',$drug->id)->with('success','Berhasil melakukan retur');
            }
            
            return redirect()->route('inventory.stocks.show',$drug->id)->with('success','Berhasil melakukan retur');
        }
    }
    public function trash(Request $request,TransactionDetail $batch)
    {
        $drug = $batch->drug();
        $judul = "Buang Obat ". $drug->name;
        if($request->isMethod('get')){
            return view("pages.inventory.trash",compact('batch','judul'));
        }elseif($request->isMethod('post')){
            $validator = \Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                session()->flash('error', 'Gagal membuang obat');
                return redirect()->back()->withInput();
            }
            // dd($request);
            $transaction = Transaction::create([
                "vendor_id"=>$batch->transaction->vendor()->id,
                "destination"=>"warehouse",
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
            $warehouse = Warehouse::where('drug_id',$drug->id)->first();
            $warehouse->quantity = $warehouse->quantity - $request->quantity*$drug->piece_netto;
            $warehouse->save();
            if (auth()->user()->role=='doctor') {
                return redirect()->route('clinic.stocks.show',$drug->id)->with('success','Berhasil melakukan retur');
            }
            return redirect()->route('inventory.stocks.show',$drug->id)->with('success','Berhasil melakukan pembuangan');
        }
    }
}