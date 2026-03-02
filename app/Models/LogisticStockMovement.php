<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistic_item_id',
        'transaction_type',
        'reference_document',
        'qty_in',
        'qty_out',
        'balance',
        'note',
        'created_by',
    ];

    public function item()
    {
        return $this->belongsTo(LogisticItem::class, 'logistic_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
