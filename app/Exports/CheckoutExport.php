<?php

namespace App\Exports;

use App\Models\Profile;
use App\Models\Transaction\TransactionDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CheckoutExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $transaction_id;

    public function __construct($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function collection(): Collection
    {
        // Ambil satu TransactionDetail sebagai acuan (pakai first)
        $transactionDetail = TransactionDetail::where('transaction_id', $this->transaction_id)
            ->with('transaction')
            ->first();

        // Pastikan transaksi ditemukan
        if (!$transactionDetail || !$transactionDetail->transaction) {
            Log::warning("Export gagal: Tidak ada transaksi dengan ID " . $this->transaction_id);
            return collect([]);
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
            ->where('transaction_details.transaction_id', $this->transaction_id)
            ->get();

        $data = [];

        $profile = Profile::first();
        // Header Klinik
        $data[] = [$profile->name, '', '', '', '', 'No. Invoice : ' . $no_invoice];
        $data[] = [$profile->address, '', '', '', '', 'Tanggal: ' . $tanggal_transaksi];
        $data[] = [$profile->phone, '', '', '', '', ''];
        $data[] = ['']; // Baris kosong
        $data[] = ['', '', 'INVOICE', '', '', ''];
        $data[] = ['']; // Baris kosong

        // Header Tabel
        $data[] = ['No', 'Kode Obat', 'Nama Obat', 'Jumlah', 'Harga Satuan', 'Total'];

        // Isi Data
        $grand_total = 0;
        foreach ($details as $index => $item) {
            $data[] = [
                $index + 1,
                $item->drug_code ?? '-',
                $item->drug_name ?? '-',
                $item->quantity . ' ',
                'Rp ' . number_format($item->piece_price, 0, ',', '.'),
                'Rp ' . number_format($item->total_price, 0, ',', '.'),
            ];
            $grand_total += $item->total_price;
        }

        // Summary
        $data[] = ['']; // Baris kosong setelah data
        $discount = $transaction->discount ?? 0;
        $subtotal = $grand_total;
        $total = $subtotal - $discount;

        $data[] = ['', '', '', '', 'Subtotal:', 'Rp ' . number_format($subtotal, 0, ',', '.')];
        $data[] = ['', '', '', '', 'Diskon:', 'Rp ' . number_format($discount, 0, ',', '.')];
        $data[] = ['', '', '', '', 'Total:', 'Rp ' . number_format($total, 0, ',', '.')];

        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->collection()) + 1; // Menghitung total baris termasuk header

        return [
            5 => ['font' => ['bold' => true, 'size' => 14]], // Judul invoice
            10 => ['font' => ['bold' => true, 'size' => 12]], // Header tabel
            11 => ['font' => ['bold' => true]], // Kolom judul tabel
            ($rowCount-2) => ['font' => ['bold' => true]], // Subtotal
            ($rowCount-1) => ['font' => ['bold' => true]], // Diskon
            $rowCount => ['font' => ['bold' => true]], // Total

            // Mengatur alignment
            'A10:F10' => ['alignment' => ['horizontal' => 'center']], // Header tabel
            'A11:A' . $rowCount => ['alignment' => ['horizontal' => 'center']], // No urut
            'B11:B' . $rowCount => ['alignment' => ['horizontal' => 'center']], // Kode Obat (tengah)
            'C11:C' . $rowCount => ['alignment' => ['horizontal' => 'left']], // Nama Obat (kiri)
            'D11:F' . $rowCount => ['alignment' => ['horizontal' => 'center']], // Jumlah, Harga, dan Subtotal
            'F' . ($rowCount-2) => ['alignment' => ['horizontal' => 'right']], // Subtotal (kanan)
            'F' . ($rowCount-1) => ['alignment' => ['horizontal' => 'right']], // Diskon (kanan)
            'F' . $rowCount => ['alignment' => ['horizontal' => 'right']], // Total (kanan)
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,    // No
            'B' => 15,   // Kode Obat
            'C' => 30,   // Nama Obat
            'D' => 15,   // Jumlah
            'E' => 15,   // Harga Satuan
            'F' => 20,   // Subtotal
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Invoice';
    }
} 