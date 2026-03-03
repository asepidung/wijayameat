<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticStock extends Model
{
    use HasFactory;

    protected $fillable = ['logistic_item_id', 'qty'];

    public function item()
    {
        return $this->belongsTo(LogisticItem::class, 'logistic_item_id');
    }
}
