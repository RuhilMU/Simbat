<?php

namespace App\Exports;

use App\Models\Master\Drug;
use App\Models\Master\Vendor;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class InventoryTemplateExport implements FromArray, WithHeadings, WithEvents
{
    public function array(): array
    {
        return []; // Tidak ada data, hanya header
    }

    public function headings(): array
    {
        return ["Nama Obat", "Jumlah", "Satuan", "Harga Satuan", "Tanggal EXP"];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ambil daftar "Nama Obat" dan harga dari database
                $drugs = Drug::pluck('last_price', 'name')->toArray(); // Key: Nama Obat, Value: Harga

                // Daftar pilihan manual untuk "Satuan"
                $satuan = ['pcs', 'pack', 'box'];

                // Fungsi untuk membuat dropdown di kolom tertentu
                function setDropdown($sheet, $column, $data, $startRow = 2, $endRow = 100)
                {
                    if (!empty($data)) {
                        $list = '"' . implode(',', $data) . '"';
                        for ($row = $startRow; $row <= $endRow; $row++) {
                            $validation = $sheet->getCell($column . $row)->getDataValidation();
                            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                            $validation->setAllowBlank(true);
                            $validation->setShowInputMessage(true);
                            $validation->setShowErrorMessage(true);
                            $validation->setShowDropDown(true);
                            $validation->setFormula1($list);
                        }
                    }
                }

                // Terapkan dropdown ke masing-masing kolom
                setDropdown($sheet, 'A', array_keys($drugs)); // Kolom Nama Obat
                setDropdown($sheet, 'C', $satuan); // Kolom Satuan

                // Terapkan format tanggal dan tambahkan date picker di kolom "Tanggal EXP" (E)
                for ($row = 2; $row <= 100; $row++) {
                    $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode('yyyy-mm-dd');

                    $dateValidation = $sheet->getCell("E$row")->getDataValidation();
                    $dateValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DATE);
                    $dateValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $dateValidation->setAllowBlank(true);
                    $dateValidation->setShowInputMessage(true);
                    $dateValidation->setShowErrorMessage(true);
                    $dateValidation->setFormula1('DATE(1900,1,1)');
                    $dateValidation->setFormula2('DATE(2099,12,31)');
                    $dateValidation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);

                    $sheet->getCell("E$row")->setDataValidation(clone $dateValidation);
                }

                // *** Langkah untuk Menghubungkan Nama Obat dengan Harga Satuan ***
                // 1. Buat Named Range untuk setiap Obat dan Harga
                $namedRangeSheet = $event->sheet->getParent()->createSheet();
                $namedRangeSheet->setTitle('Data Harga Obat');
                $rowIndex = 1;

                // Tambahkan header
                $namedRangeSheet->setCellValue("A1", "Nama Obat");
                $namedRangeSheet->setCellValue("B1", "Harga");
                $rowIndex = 2;

                foreach ($drugs as $name => $price) {
                    $namedRangeSheet->setCellValue("A$rowIndex", $name);
                    $namedRangeSheet->setCellValue("B$rowIndex", $price);
                    $rowIndex++;
                }

                // Buat range data obat
                $drugRange = "Data_Obat";
                $event->sheet->getParent()->addNamedRange(
                    new \PhpOffice\PhpSpreadsheet\NamedRange(
                        $drugRange,
                        $namedRangeSheet,
                        'A2:B' . ($rowIndex - 1)
                    )
                );

                // 2. Gunakan VLOOKUP untuk mengambil harga berdasarkan obat yang dipilih (tanpa mengalikan dengan jumlah)
                for ($row = 2; $row <= 100; $row++) {
                    $formula = "=IF(A$row<>\"\",VLOOKUP(A$row,'Data Harga Obat'!A:B,2,FALSE),\"\")";
                    $sheet->getCell("D$row")->setValue($formula);
                }

                // Sembunyikan sheet data harga obat
                $namedRangeSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}
