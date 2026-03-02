<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CattlePurchaseOrderItem extends Model
{
    protected $fillable = [
        'cattle_purchase_order_id',
        'cattle_category_id',
        'qty_head',
        'total_weight_kg',
        'price_per_kg',
        'subtotal'
    ];

    public function cattleCategory()
    {
        return $this->belongsTo(CattleCategory::class);
    }
}
