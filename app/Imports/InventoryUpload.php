<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class InventoryUpload implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    public function collection(Collection $rows)
    {
        // Proses dan filter data yang valid
        $processedDrugs = [];
        $totalAmount = 0;
        $filteredData = $rows->map(function ($row) use (&$processedDrugs, &$totalAmount) {
            if (
                $this->isEmptyRow($row) ||
                !isset($row['nama_obat']) ||
                !isset($row['jumlah']) ||
                !isset($row['satuan']) ||
                !isset($row['harga_satuan']) ||
                !isset($row['tanggal_exp'])
            ) {
                return null;
            }

            $drugKey = $row['nama_obat'] . '_' . $row['jumlah'] . '_' . $row['satuan'] . '_' . $row['tanggal_exp'];
            if (in_array($drugKey, $processedDrugs)) {
                return null;
            }
            $processedDrugs[] = $drugKey;

            // Format tanggal dari Excel menjadi Y-m-d
            if (isset($row['tanggal_exp']) && is_numeric($row['tanggal_exp'])) {
                $row['tanggal_exp'] = Carbon::instance(Date::excelToDateTimeObject($row['tanggal_exp']))->format('Y-m-d');
            }

            // Pastikan harga satuan adalah angka dan konversi quantity ke PCS jika perlu
            if (isset($row['harga_satuan']) && is_numeric($row['harga_satuan'])) {
                $hargaSatuan = (float) $row['harga_satuan'];
                $jumlah = (float) $row['jumlah'];
                $unit = strtolower($row['satuan']);

                // Simpan jumlah dan satuan asli
                $row['jumlah_asli'] = $jumlah;
                $row['satuan_asli'] = $row['satuan'];

                // Ambil data drug untuk konversi
                $drug = \App\Models\Master\Drug::where('name', $row['nama_obat'])->first();
                if ($drug) {
                    if ($unit === 'pcs') {
                        $pcs_qty = $jumlah;
                    } elseif ($unit === 'pack') {
                        $pcs_qty = $jumlah * $drug->piece_quantity;
                    } elseif ($unit === 'box') {
                        $pcs_qty = $jumlah * $drug->piece_quantity * $drug->pack_quantity;
                    } else {
                        $pcs_qty = $jumlah;
                    }
                    $row['jumlah'] = $pcs_qty; // always in PCS
                    $row['satuan'] = 'pcs';
                    $row['subtotal'] = $pcs_qty * $hargaSatuan;
                    $totalAmount += $row['subtotal'];
                } else {
                    // fallback if drug not found
                    $row['subtotal'] = $hargaSatuan * $jumlah;
                    $totalAmount += $row['subtotal'];
                }
            }

            return $row;
        })->filter(function ($row) { return $row !== null; });

        // Simpan data ke session jika ada
        if ($filteredData->isNotEmpty()) {
            Session::put('imported_data', $filteredData);
            Session::put('total_amount', $totalAmount);
            Session::save();
        }
    }

    /**
     * Mengecek apakah sebuah baris kosong
     */
    private function isEmptyRow($row): bool
    {
        foreach ($row as $value) {
            if (!empty($value) && $value !== null && $value !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Menggunakan header di baris pertama
     */
    public function headingRow(): int
    {
        return 1;
    }
}
