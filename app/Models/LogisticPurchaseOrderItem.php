<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogisticPurchaseOrderItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(LogisticPurchaseOrder::class, 'logistic_purchase_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LogisticItem::class, 'logistic_item_id');
    }
}
