<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogisticReceivingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'logistic_receiving_id',
        'logistic_item_id',
        'qty_received',
        'price',
        'subtotal'
    ];

    public function receiving()
    {
        return $this->belongsTo(LogisticReceiving::class, 'logistic_receiving_id');
    }

    /* Relasi ini nembak ke tabel logistic_items lu */
    public function item()
    {
        return $this->belongsTo(LogisticItem::class, 'logistic_item_id');
    }
}
