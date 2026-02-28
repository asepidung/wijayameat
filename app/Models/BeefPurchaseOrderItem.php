<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeefPurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'beef_purchase_order_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
    ];

    /* Relasi balik ke PO Header */
    public function purchaseOrder()
    {
        return $this->belongsTo(BeefPurchaseOrder::class, 'beef_purchase_order_id');
    }

    /* Relasi ke produk */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
