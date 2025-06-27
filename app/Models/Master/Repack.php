<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repack extends Model
{
    use HasFactory;
    protected $guarded = [];
    //repack memiliki relasi one-one dengan obat
    public function drug(){
        return $this->belongsTo(Drug::class)->first();
    }
    //kalkulasi stok gudang berdasarkan konfigurasi repack
    public function stock(){
        $warehouse = $this->drug()->warehouse;
        if ($warehouse->quantity == 0) {
            return 0;
        }
        return $warehouse->quantity / $this->quantity;
    }
    //kalkulasi stok klinik berdasarkan konfigurasi repack
    public function clinic_stock(){
        $clinic = $this->drug()->clinic()->first();
        if (!$clinic || $clinic->quantity == 0) {
            return 0;
        }
        return $clinic->quantity / $this->quantity;
    }
    //melakukan perubahan harga repack berdasarkan master data
    public function update_price(){
        $drug = $this->drug();
        if(str_contains($this->name,'1 pack')){
            $this->quantity = $drug->piece_netto*$drug->piece_quantity;
        }
        if($this->quantity==$drug->piece_netto){
            $this->margin = $drug->piece_margin;
        }elseif($this->quantity==($drug->piece_netto*$drug->piece_quantity)){
            $this->margin = $drug->pack_margin;
        }
        $this->price = $drug->calculate_price($this->quantity,$this->margin);
        $this->save();
    }
}
