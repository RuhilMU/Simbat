<?php

namespace App\Exports;

use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Clinic;
use App\Models\Master\Drug;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $drugs = Drug::all();
        
        $stocks = $drugs->map(function($drug) {
            $warehouseStock = Warehouse::where('drug_id', $drug->id)->first();
            $clinicStock = Clinic::where('drug_id', $drug->id)->first();
            
            $totalStock = ($warehouseStock ? $warehouseStock->quantity : 0) + 
                         ($clinicStock ? $clinicStock->quantity : 0);
            
            $oldest = null;
            $latest = null;
            
            if ($totalStock > 0) {
                if ($warehouseStock && $warehouseStock->quantity > 0) {
                    $oldest = $warehouseStock->oldest;
                    $latest = $warehouseStock->latest;
                }
                if ($clinicStock && $clinicStock->quantity > 0) {
                    if ($oldest === null || $clinicStock->oldest < $oldest) {
                        $oldest = $clinicStock->oldest;
                    }
                    if ($latest === null || $clinicStock->latest > $latest) {
                        $latest = $clinicStock->latest;
                    }
                }
            }
            
            return (object)[
                'drug' => $drug,
                'quantity' => $totalStock,
                'oldest' => $oldest,
                'latest' => $latest
            ];
        });

        // Filter by expiration date range if provided
        if ($this->startDate && $this->endDate) {
            $stocks = $stocks->filter(function($stock) {
                $hasExpiringStock = false;
                
                // Check warehouse stock
                $warehouseStock = Warehouse::where('drug_id', $stock->drug->id)->first();
                if ($warehouseStock && $warehouseStock->quantity > 0) {
                    if ($warehouseStock->oldest && $warehouseStock->oldest >= $this->startDate && $warehouseStock->oldest <= $this->endDate) {
                        $hasExpiringStock = true;
                    }
                    if ($warehouseStock->latest && $warehouseStock->latest >= $this->startDate && $warehouseStock->latest <= $this->endDate) {
                        $hasExpiringStock = true;
                    }
                }
                
                // Check clinic stock
                $clinicStock = Clinic::where('drug_id', $stock->drug->id)->first();
                if ($clinicStock && $clinicStock->quantity > 0) {
                    if ($clinicStock->oldest && $clinicStock->oldest >= $this->startDate && $clinicStock->oldest <= $this->endDate) {
                        $hasExpiringStock = true;
                    }
                    if ($clinicStock->latest && $clinicStock->latest >= $this->startDate && $clinicStock->latest <= $this->endDate) {
                        $hasExpiringStock = true;
                    }
                }
                
                return $hasExpiringStock;
            });
        }

        return $stocks;
    }

    public function headings(): array
    {
        return [
            'No', 'Kode Obat', 'Nama Obat', 'Stok', 'Expired Terdekat', 'Expired Terbaru'
        ];
    }

    public function map($item): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter,
            $item->drug->code,
            $item->drug->name,
            floor($item->quantity / $item->drug->piece_netto) . ' pcs',
            $item->oldest ? Carbon::parse($item->oldest)->translatedFormat('j F Y') : '-',
            $item->latest ? Carbon::parse($item->latest)->translatedFormat('j F Y') : '-',
        ];
    }
}
