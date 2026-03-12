<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoningItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'boning_id',
        'product_id',
        'warehouse_id',
        'condition',
        'weight',
        'qty_pcs',
        'ph_level',
        'pack_date',
        'exp_date',
        'barcode',
        'created_by'
    ];

    public function boning(): BelongsTo
    {
        return $this->belongsTo(Boning::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
