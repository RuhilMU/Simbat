<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->first();
    }
}
