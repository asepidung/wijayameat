<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeefStock extends Model
{
    protected $fillable = [
        'barcode',
        'product_id',
        'warehouse_id',
        'condition',
        'weight',
        'qty_pcs',
        'ph_level',
        'pack_date',
        'exp_date',
        'origin',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
