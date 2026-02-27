<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahin ini biar pro

class LogisticItem extends Model
{
    protected $fillable = [
        'logistic_category_id',
        'unit_id',
        'code',
        'name',
        'show_in_stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'show_in_stock' => 'boolean',
        'is_active' => 'boolean',
        'min_stock' => 'integer',
    ];

    // Nama fungsi ini harus 'logisticCategory' (camelCase)
    public function logisticCategory(): BelongsTo
    {
        return $this->belongsTo(LogisticCategory::class);
    }

    // Nama fungsi ini harus 'unit' (huruf kecil semua)
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
