<?php

namespace App\Models\Master;

use App\Models\Inventory\Clinic;
use App\Models\Inventory\Warehouse;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;
    protected $guarded = [];
    //mengambil data stok mana yang seharusnya dikeluarkan
    public function used()
    {
        return $this->hasOne(TransactionDetail::class, 'id', 'used')->first();
    }
    //mengambil data kategori obat
    public function category()
    {
        return $this->belongsTo(Category::class)->first();
    }
    //mengambil data jenis obat
    public function variant()
    {
        return $this->belongsTo(Variant::class)->first();
    }
    //mengambil data produsen obat
    public function manufacture()
    {
        return $this->belongsTo(Manufacture::class)->first();
    }
    //mengambil data repack obat
    public function repacks()
    {
        return $this->hasMany(Repack::class)->get();
    }
    //mengambil data stok obat di gudang
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }
    //mengambil data stok obat di klinik
    public function clinic()
    {
        return $this->hasOne(Clinic::class);
    }
    //membuat data repack default yaitu 1pcs dan 1 pack
    public function default_repacks()
    {
        Repack::create([
            "drug_id" => $this->id,
            "name" => $this->name . " 1 pack",
            "quantity" => $this->piece_quantity * $this->piece_netto,
            "margin" => $this->pack_margin,
            "price" => $this->calculate_price($this->piece_quantity * $this->piece_netto, $this->pack_margin)
        ]);
        Repack::create([
            "drug_id" => $this->id,
            "name" => $this->name . " 1 pcs",
            "quantity" => $this->piece_netto,
            "margin" => $this->piece_margin,
            "price" => $this->calculate_price($this->piece_netto, $this->piece_margin)
        ]);
    }
    //membuat data stok default di klinik dan gudang
    public function default_stock()
    {
        Warehouse::create([
            "drug_id" => $this->id,
            "quantity" => 0,
        ]);
        Clinic::create([
            "drug_id" => $this->id,
            "quantity" => 0,
        ]);
    }
    //kalkulasi harga berdasarkan harga netto satuan
    public function calculate_price(int $quantity, int $margin)
    {
        $nett_price = $this->last_price / $this->piece_netto;
        if ($this->last_price != 0) {
            return $quantity * $nett_price * (100 + $margin) / 100;
        }
        return 0;
    }
    public function getBoxPriceAttribute()
    {
        return $this->pack_quantity * $this->piece_quantity * $this->last_price;
    }
    public function getPackPriceAttribute()
    {
        return $this->piece_quantity * $this->last_price;
    }
    //melakukan pemindahan stok yang digunakan berdasarkan expire date(khusus transaksi checkout)
    public function nextStock()
    {
        $inflow = Transaction::where('variant', 'LPB')->pluck('id');
        $details = TransactionDetail::where('drug_id', $this->id)->whereIn('transaction_id', $inflow)->whereNot('stock', 0)->orderBy('expired')->first();
        $used = $this->used();
        $used->used = false;
        $used->save();
        $this->used = $details->id;
        $used = $this->used();
        $used->used = true;
        $used->save();
    }

    //melakukan pengurangan stok pada gudang berdasarkan expire terdekat
    public function customerUseWarehouse(Transaction $transaction, int $require, int $repackQuantity, string $repackName, int $piecePrice, int $priceDiscount)
    {
        $actualQuantity = str_contains($repackName, 'pack') ? 
            $require * ($this->piece_quantity * $this->piece_netto) : 
            $require * $this->piece_netto;
        
        $remain = $actualQuantity;
        $used = $this->used();
        $warehouse = $this->warehouse()->first();
        $warehouse->quantity = $warehouse->quantity - $actualQuantity;
        $warehouse->save();

        $unit = str_contains($repackName, 'pack') ? 'pack' : 'pcs';
        
        TransactionDetail::create([
            "transaction_id" => $transaction->id,
            "drug_id" => $this->id,
            "expired" => $this->warehouse()->first()->oldest,
            "name" => $repackName,
            "quantity" => $require . ' ' . $unit,
            "piece_price" => $piecePrice,
            "total_price" => $piecePrice * $require,
            "discount_price" => $priceDiscount,
            "flow" => -$actualQuantity
        ]);

        if ($used->stock > $actualQuantity) {
            $used->stock = $used->stock - $actualQuantity;
            $used->save();
        } else {
            while ($remain > 0) {
                if ($used->stock > $remain) {
                    $used->stock = $used->stock - $remain;
                    $remain = 0;
                    $used->save();
                } else {
                    $stockQuantity = $remain;
                    $remain = $remain - $used->stock;
                    $used->stock = 0;
                    $used->save();
                    $this->nextStock();
                    $used = $this->used();
                }
            }
        }
    }

    public function customerUseClinic(Transaction $transaction, int $require, int $repackQuantity, string $repackName, int $piecePrice, int $priceDiscount)
    {
        \Log::info('Stock reduction values:', [
            'drug_name' => $this->name,
            'require' => $require,
            'repack_quantity' => $repackQuantity,
            'repack_name' => $repackName,
            'is_pack' => str_contains($repackName, 'pack'),
            'piece_quantity' => $this->piece_quantity,
            'piece_netto' => $this->piece_netto,
            'actual_quantity' => str_contains($repackName, 'pack') ? 
                $require * ($this->piece_quantity * $this->piece_netto) : 
                $require * $this->piece_netto
        ]);

        $actualQuantity = str_contains($repackName, 'pack') ? 
            $require * ($this->piece_quantity * $this->piece_netto) : 
            $require * $this->piece_netto;
        
        $remain = $actualQuantity;
        $used = $this->used();
        $clinic = $this->clinic()->first();
        
        if ($clinic->quantity < $actualQuantity) {
            throw new \Exception("Insufficient stock in clinic for {$this->name}");
        }
        
        $clinic->quantity = $clinic->quantity - $actualQuantity;
        $clinic->save();
        
        $unit = str_contains($repackName, 'pack') ? 'pack' : 'pcs';
        
        TransactionDetail::create([
            "transaction_id" => $transaction->id,
            "drug_id" => $this->id,
            "expired" => $clinic->oldest,
            "name" => $repackName,
            "quantity" => $require . ' ' . $unit,
            "piece_price" => $piecePrice,
            "total_price" => $piecePrice * $require,
            "discount_price" => $priceDiscount,
            "flow" => -$actualQuantity
        ]);
        
        if ($used->stock > $actualQuantity) {
            $used->stock = $used->stock - $actualQuantity;
            $used->save();
        } else {
            while ($remain > 0) {
                if ($used->stock > $remain) {
                    $used->stock = $used->stock - $remain;
                    $remain = 0;
                    $used->save();
                } else {
                    $stockQuantity = $remain;
                    $remain = $remain - $used->stock;
                    $used->stock = 0;
                    $used->save();
                    $this->nextStock();
                    $used = $this->used();
                }
            }
        }
    }

    //melakukan pemindahan stok yang digunakan berdasarkan expire date(khusus transaksi obat masuk klinik)
    public function clinicUseWarehouse(Transaction $transaction, int $require, int $repackQuantity)
    {
        $remain = $require;
        $used = $this->used();
        if ($used->stock > $require) {
            $used->stock = $used->stock - $require;
            $used->save();
            TransactionDetail::create([
                "transaction_id" => $transaction->id,
                "drug_id" => $this->id,
                "name" => $this->name . " 1 pcs",
                "quantity" => $repackQuantity . " pcs",
                "stock" => $require,
                "expired" => $used->expired,
                "piece_price" => $this->last_price,
                "total_price" => $this->last_price * $repackQuantity,
            ]);
        } else {
            while ($remain > 0) {
                $expired = $used->expired;
                if ($used->stock > $remain) {
                    $used->stock = $used->stock - $remain;
                    $usedQuantity = floor($remain / $this->piece_netto);
                    $stockQuantity = $remain;
                    $remain = 0;
                    $used->save();
                } else {
                    $stockQuantity = $remain;
                    $remain = $remain - $used->stock;
                    $stockQuantity = $used->stock;
                    $usedQuantity = ceil($used->stock / $this->piece_netto);
                    $used->stock = 0;
                    $used->save();
                    $this->nextStock();
                    $used = $this->used();
                }
                TransactionDetail::create([
                    "transaction_id" => $transaction->id,
                    "drug_id" => $this->id,
                    "stock" => $stockQuantity,
                    "name" => $this->name . " 1 pcs",
                    "quantity" => $usedQuantity . " pcs",
                    "expired" => $expired,
                    "piece_price" => $this->last_price,
                    "total_price" => $this->last_price * $usedQuantity,
                ]);
            }
        }
    }
}
