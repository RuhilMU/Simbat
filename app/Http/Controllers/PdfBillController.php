<?php

namespace App\Http\Controllers;

use App\Models\Transaction\Bill;
use Illuminate\Http\Request;
use PDF;

class PdfBillController extends Controller
{
    public function generatePdf($bill_id)
    {
        $bill = Bill::findOrFail($bill_id);
        
        $data = [
            'bill' => $bill,
            'transaction' => $bill->transaction(),
            'vendor' => $bill->transaction()->vendor(),
            'details' => $bill->transaction()->details(),
        ];

        $pdf = PDF::loadView('pages.management.pdf.bill', $data);
        
        return $pdf->download('laporan_penerimaan_barang_' . $bill->transaction()->code . '.pdf');
    }
} 