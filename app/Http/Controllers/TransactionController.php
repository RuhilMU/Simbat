<?php

namespace App\Http\Controllers;

use App\Models\Master\Repack;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CheckoutExport;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function index()
    {
        $judul = "Log History";
        $transactions = Transaction::where('variant','Checkout')->paginate(20);
        return view('pages.transaction.index',compact('judul','transactions'));
    }
    public function create()
    {
        $judul = "Checkout Barang";
        return view('pages.transaction.checkout',compact('judul'));
    }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $dataInput = json_decode($request->transaction);
            if (!$dataInput || !isset($dataInput->data) || !is_array($dataInput->data)) {
                throw new \Exception('Invalid transaction data format');
            }

            $transaction = Transaction::create([
                'variant' => 'Checkout',
                'destination' => 'customer',
                'method' => 'cash',
                'income' => $dataInput->totalPay,
                'discount' => $dataInput->totalDisc,
                'profit' => 0 
            ]);

            $transaction->generate_code();

        //data yang dikirimkan FE berupa JSON
            $totalProfit = 0;
            foreach ($dataInput->data as $item) {
                if (!isset($item->repackId) || !isset($item->quantity) || !isset($item->piecePrice)) {
                    throw new \Exception('Invalid item data');
                }

                $repack = Repack::find($item->repackId);
                if (!$repack) {
                    throw new \Exception("Repack configuration not found");
                }

                $drug = $repack->drug();
                if (!$drug) {
                    throw new \Exception("Drug not found for repack");
                }
            //kalkulasi keuntungan
                $pieceProfit = $item->piecePrice - ($drug->last_price * ($item->repackQuantity / $drug->piece_netto));
                $itemProfit = ($pieceProfit * $item->quantity) - ($item->priceDiscount ?? 0);
                $totalProfit += $itemProfit;

                if ($dataInput->source === 'warehouse') {
                    $drug->customerUseWarehouse(
                        $transaction,
                        $item->quantity,
                        $item->repackQuantity,
                        $item->repackName,
                        $item->piecePrice,
                        $item->priceDiscount ?? 0
                    );
                } else {
                    $drug->customerUseClinic(
                        $transaction,
                        $item->quantity,
                        $item->repackQuantity,
                        $item->repackName,
                        $item->piecePrice,
                        $item->priceDiscount ?? 0
                    );
                }
            }

            $transaction->update(['profit' => $totalProfit]);

            DB::commit();
            return redirect()->route('transaction.show', $transaction->id)->with('success', 'Transaction completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process transaction: ' . $e->getMessage());
        }
    }
    public function show(Transaction $transaction)
    {
        $judul = "Invoice";
        return view('pages.transaction.show',compact('judul','transaction'));
    }
    public function exportCheckout($transaction_id)
    {
        $transactionDetail = TransactionDetail::where('transaction_id', $transaction_id)
            ->with('transaction')
            ->first();

        // Pastikan transaksi ditemukan
        if (!$transactionDetail || !$transactionDetail->transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }

        // Ambil data transaksi
        $transaction = $transactionDetail->transaction;
        $no_invoice = $transaction->code;
        return Excel::download(new CheckoutExport($transaction_id), 'invoice_' . $no_invoice . '.xlsx');
    }
}
