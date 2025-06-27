<?php

namespace App\Exports;

use App\Models\Profile;
use App\Models\Transaction\Bill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BillExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $bill_id;
    protected $bill;

    public function __construct($bill_id)
    {
        $this->bill_id = $bill_id;
        $this->bill = Bill::find($bill_id);
    }

    public function collection(): Collection
    {
        if (!$this->bill) {
            Log::warning("Export gagal: Tidak ada bill dengan ID " . $this->bill_id);
            return collect([]);
        }

        $transaction = $this->bill->transaction();
        $vendor = $transaction->vendor();
        $details = $transaction->details();
        
        $data = [];

        $profile = Profile::first();
        // Header Klinik
        $data[] = [$profile->name, '', '', '', '', 'No. LPB : ' . $transaction->code];
        $data[] = [$profile->address, '', '', '', '', 'Tanggal: ' . \Carbon\Carbon::parse($this->bill->created_at)->translatedFormat('j F Y')];
        $data[] = [$profile->phone, '', '', '', '', ''];
        $data[] = ['']; // Baris kosong
        $data[] = ['', '', 'LAPORAN PENERIMAAN BARANG', '', '', ''];
        $data[] = ['']; // Baris kosong
        $data[] = ['', '', '', '', '', 'Vendor: ' . $vendor->name];
        $data[] = ['', '', '', '', '', $vendor->address];
        $data[] = ['', '', '', '', '', $vendor->phone];
        $data[] = ['']; // Baris kosong

        // Header Tabel
        $data[] = ['No', 'Kode Obat', 'Nama Obat', 'Jumlah', 'Harga Satuan', 'Subtotal'];

        // Isi Data
        $grand_total = 0;
        foreach ($details as $index => $item) {
            $data[] = [
                $index + 1,
                $item->drug()->code,
                $item->drug()->name,
                $item->quantity,
                'Rp ' . number_format($item->piece_price, 0, ',', '.'),
                'Rp ' . number_format($item->total_price, 0, ',', '.'),
            ];
            $grand_total += $item->total_price;
        }

        // Summary
        $data[] = ['']; // Baris kosong setelah data
        $data[] = ['', '', '', '', 'Grand Total:', 'Rp ' . number_format($transaction->outcome, 0, ',', '.')];
        
        // Status pembayaran
        if ($this->bill->status == 'Belum Bayar') {
            $data[] = ['', '', '', '', 'Status:', 'Belum Bayar'];
        }

        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $data = $this->collection()->toArray();
        $rowCount = count($data);

        $styles = [
            5 => ['font' => ['bold' => true, 'size' => 14]], // Judul laporan
            10 => ['font' => ['bold' => true, 'size' => 12]], // Header tabel
            11 => ['font' => ['bold' => true]], // Kolom judul tabel
        ];

        // Grand Total row
        $grandTotalRow = $rowCount - 1;
        if ($this->bill->status == 'Belum Bayar') {
            $grandTotalRow = $rowCount - 2; // If status exists, grand total is second to last
        }
        $styles[$grandTotalRow] = ['font' => ['bold' => true]];

        // Status row (if exists)
        if ($this->bill->status == 'Belum Bayar') {
            $styles[$rowCount] = ['font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']]];
        }

        // Alignment styles
        $styles['A10:F10'] = ['alignment' => ['horizontal' => 'center']]; // Header tabel
        $styles['A11:A' . $rowCount] = ['alignment' => ['horizontal' => 'center']]; // No urut
        $styles['B11:B' . $rowCount] = ['alignment' => ['horizontal' => 'center']]; // Kode Obat (tengah)
        $styles['C11:C' . $rowCount] = ['alignment' => ['horizontal' => 'left']]; // Nama Obat (kiri)
        $styles['D11:F' . $rowCount] = ['alignment' => ['horizontal' => 'center']]; // Jumlah, Harga, dan Subtotal
        $styles['F' . $grandTotalRow] = ['alignment' => ['horizontal' => 'right']]; // Grand Total (kanan)
        
        if ($this->bill->status == 'Belum Bayar') {
            $styles['F' . $rowCount] = ['alignment' => ['horizontal' => 'center']]; // Status (kanan)
        }

        return $styles;
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
        return 'Laporan Penerimaan Barang';
    }
} 