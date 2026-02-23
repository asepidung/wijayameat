<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticItem extends Model
{
    protected $fillable = [
        'logistic_category_id',
        'code',
        'name',
        'unit',
        'show_in_stock',
        'is_active'
    ];

    public function logisticCategory()
    {
        return $this->belongsTo(LogisticCategory::class);
    }
}
