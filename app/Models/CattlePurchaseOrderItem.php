<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Tambahin ini

class CattlePurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes; // <-- Panggil di sini

    protected $fillable = [
        'cattle_purchase_order_id',
        'cattle_category_id',
        'qty_head',
        'price_per_kg',
        'note'
    ];

    public function setNoteAttribute($value)
    {
        $this->attributes['note'] = strtoupper($value);
    }

    public function cattleCategory()
    {
        return $this->belongsTo(CattleCategory::class);
    }
}
