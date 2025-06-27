<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Transaction\TransactionDetail;
use Illuminate\Http\Request;
use PDF;

class PdfCheckoutController extends Controller
{
    public function generatePdf($transaction_id)
    {
        // Ambil satu TransactionDetail sebagai acuan (pakai first)
        $transactionDetail = TransactionDetail::where('transaction_id', $transaction_id)
            ->with('transaction')
            ->first();

        // Pastikan transaksi ditemukan
        if (!$transactionDetail || !$transactionDetail->transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }

        // Ambil data transaksi
        $transaction = $transactionDetail->transaction;
        $no_invoice = $transaction->code ?? 'N/A';
        $tanggal_transaksi = $transaction->created_at
            ? \Carbon\Carbon::parse($transaction->created_at)->format('d F Y')
            : now()->format('d F Y');

        // Ambil semua detail transaksi dengan kode dan nama obat
        $details = TransactionDetail::select(
                'transaction_details.*',
                'drugs.code as drug_code',
                'drugs.name as drug_name'
            )
            ->leftJoin('drugs', 'transaction_details.drug_id', '=', 'drugs.id')
            ->where('transaction_details.transaction_id', $transaction_id)
            ->get();

        // Hitung total
        $subtotal = $details->sum('total_price');
        $discount = $transaction->discount ?? 0;
        $total = $subtotal - $discount;

        // Ambil data profil
        $profile = Profile::first();

        // Generate PDF
        $pdf = PDF::loadView('pages.transaction.pdf.checkout', [
            'profile' => $profile,
            'no_invoice' => $no_invoice,
            'tanggal_transaksi' => $tanggal_transaksi,
            'details' => $details,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total
        ]);

        // Download PDF
        return $pdf->download('invoice_' . $no_invoice . '.pdf');
    }
} 