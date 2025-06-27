<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Warehouse;
use App\Models\Transaction\Bill;
use App\Models\Transaction\Retur;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Trash;
use App\Models\Inventory\Clinic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction\TransactionDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BillExport;

class ManagementController extends Controller
{

    public function searchManagement(Request $request)
    {
        $variant = $request->input('variant');
        $query = $request->input('query');
        $transactions = Transaction::where('code', 'like', "%{$query}%")->pluck('id');
        if($variant=='bill'){
            $bills = Bill::whereIn('transaction_id',$transactions)->with('trans')->get();
            return response()->json($bills);
        }elseif($variant=='retur'){
            $returs = Retur::whereIn('transaction_id',$transactions)->get();
            $formattedReturs = $returs->map(function($retur) {
                return [
                    'id' => $retur->id,
                    'created_at' => $retur->created_at,
                    'arrive' => $retur->arrive,
                    'trans' => [
                        'code' => $retur->transaction()->code
                    ],
                    'drug' => [
                        'name' => $retur->drug()->name
                    ],
                    'detail' => [
                        'quantity' => $retur->detail()->quantity
                    ]
                ];
            });
            return response()->json($formattedReturs);
        }elseif($variant=='trash'){
            $trashes = Trash::whereIn('transaction_id',$transactions)->get();
            $formattedTrashes = $trashes->map(function($trash) {
                return [
                    'id' => $trash->id,
                    'created_at' => $trash->created_at,
                    'trans' => [
                        'code' => $trash->transaction()->code
                    ],
                    'drug' => [
                        'name' => $trash->drug()->name
                    ],
                    'detail' => [
                        'quantity' => $trash->detail()->quantity
                    ]
                ];
            });
            return response()->json($formattedTrashes);
        }
    }

    public function bills(Request $request)
    {
        if($request->has('start') && $request->has('end')){
            $end = Carbon::parse($request->end)->endOfDay();
            $bills = Bill::whereBetween('created_at',[$request->start,$end])->orderBy('status')->paginate(10);
        }else{
            $bills = Bill::orderBy('status')->paginate(10);
        }
        $judul = "Tagihan Obat";
        return view("pages.management.bill", compact('judul', 'bills'));
    }
    public function bill(Bill $bill)
    {
        $judul = "Tagihan Vendor " . $bill->transaction()->vendor()->name;
        return view("pages.management.billDetail", compact('bill', 'judul'));
    }
    public function billPrint() {}
    
    public function exportBillExcel($bill_id)
    {
        $bill = Bill::findOrFail($bill_id);
        $status = $bill->status == 'Belum Bayar' ? 'Tagihan Belum Bayar' : 'Tagihan Done';
        $filename = $status . '_' . $bill->transaction()->code . '.xlsx';
        return Excel::download(new BillExport($bill_id), $filename);
    }
    
    public function exportBillPdf($bill_id)
    {
        $bill = Bill::findOrFail($bill_id);
        
        $data = [
            'bill' => $bill,
            'transaction' => $bill->transaction(),
            'vendor' => $bill->transaction()->vendor(),
            'details' => $bill->transaction()->details(),
        ];

        $pdf = \PDF::loadView('pages.management.pdf.bill', $data);
        
        $status = $bill->status == 'Belum Bayar' ? 'Tagihan Belum Bayar' : 'Tagihan Done';
        $filename = $status . '_' . $bill->transaction()->code . '.pdf';
        
        return $pdf->download($filename);
    }
    
    //melakukan pembayaran tagihan
    public function billPay(Bill $bill)
    {
        
        DB::beginTransaction();
        try {
            $bill->status = "Done";
            $bill->pay = now();
            $bill->save();
            DB::commit();
            return redirect()->route('management.bill.index')->with('success','Tagihan berhasil dibayarkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('management.bill.index')->with('error','Tagihan gagal dibayarkan');
        }
    }
    public function returs(Request $request)
    {
        if($request->has('start') && $request->has('end')){
            $end = Carbon::parse($request->end)->endOfDay();
            $returs = Retur::whereBetween('created_at',[$request->start,$end])->paginate(10);
        }else{
            $returs = Retur::paginate(10);
        }
        $judul = "Manajemen Obat Retur";
        return view("pages.management.retur", compact('returs', 'judul'));
    }
    public function retur(Retur $retur)
    {
        // dd($retur);
        // $retur = $retur->retur();
        $judul = "Laporan Retur Obat " . $retur->drug()->name;
        return view("pages.management.returDetail", compact('retur', 'judul'));
    }
    public function trashes(Request $request)
    {
        if($request->has('start') && $request->has('end')){
            $end = Carbon::parse($request->end)->endOfDay();
            $trashes = Trash::whereBetween('created_at',[$request->start,$end])->paginate(10);
        }else{
            $trashes = Trash::paginate(10);
        }
        $judul = "Manajemen Obat Buang";
        return view("pages.management.trash", compact('trashes', 'judul'));
    }
    public function trash(Trash $trash)
    {
        // $trash = $trash->trash();
        $judul = "Laporan Pembuangan Obat " . $trash->drug()->name;
        return view("pages.management.trashDetail", compact('trash', 'judul'));
    }
    public function returPrint() {}
    //melakukan penerimaan barang retur
    public function returPay(Request $request, Retur $retur)
    {
        // Validate
        $request->validate([
            'new_expired_date' => 'required|date|after:today'
        ]);

        DB::beginTransaction();
        try {
            $sourceBatch = TransactionDetail::find($retur->source);
            if (!$sourceBatch) {
                throw new \Exception('Source batch not found');
            }

            $drug = $sourceBatch->drug();  
            if (!$drug) {
                throw new \Exception('Drug not found in source batch');
            }

            $sourceTransaction = $sourceBatch->transaction()->first();
            if (!$sourceTransaction) {
                throw new \Exception('Source transaction not found');
            }

            $newTransaction = Transaction::create([
                'vendor_id' => $sourceTransaction->vendor_id,
                'destination' => $sourceTransaction->variant === 'LPK' ? 'clinic' : 'warehouse',
                'variant' => $sourceTransaction->variant, 
            ]);
            $newTransaction->generate_code();

          
            $newBatch = TransactionDetail::create([
                'transaction_id' => $newTransaction->id,
                'drug_id' => $drug->id,  
                'expired' => $request->new_expired_date,
                'name' => $drug->name . ' 1 pcs',
                'quantity' => ($retur->quantity / $drug->piece_netto) . ' pcs',
                'piece_price' => $drug->last_price,
                'total_price' => ($retur->quantity / $drug->piece_netto) * $drug->last_price,
                'stock' => $retur->quantity,  
                'flow' => $retur->quantity    
            ]);

            if ($sourceTransaction->variant === 'LPK') {
                $clinic = Clinic::where('drug_id', $drug->id)->first();
                if (!$clinic) {
                    throw new \Exception('Clinic stock not found for this drug');
                }
                $clinic->quantity = $clinic->quantity + $retur->quantity;
                
                if ($clinic->oldest == null || $clinic->oldest > $request->new_expired_date) {
                    $clinic->oldest = $request->new_expired_date;
                }
                if ($clinic->latest == null || $clinic->latest < $request->new_expired_date) {
                    $clinic->latest = $request->new_expired_date;
                }
                $clinic->save();
            } else if ($sourceTransaction->variant === 'LPB') {
                $warehouse = Warehouse::where('drug_id', $drug->id)->first();
                if (!$warehouse) {
                    throw new \Exception('Warehouse stock not found for this drug');
                }
                $warehouse->quantity = $warehouse->quantity + $retur->quantity;
                
                if ($warehouse->oldest == null || $warehouse->oldest > $request->new_expired_date) {
                    $warehouse->oldest = $request->new_expired_date;
                }
                if ($warehouse->latest == null || $warehouse->latest < $request->new_expired_date) {
                    $warehouse->latest = $request->new_expired_date;
                }
                $warehouse->save();
            } else {
                throw new \Exception('Invalid transaction variant for retur');
            }

            $retur->update([
                'status' => 'Done',
                'arrive' => now(),
            ]);    

            DB::commit();
            return redirect()->route('management.retur.index')->with('success','Berhasil mengembalikan obat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error','Gagal mengembalikan obat: ' . $e->getMessage());
        }
    }
}