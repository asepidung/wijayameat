<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeefRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'beef_requisition_id',
        'product_id',
        'qty',
        'price',
    ];

    public function requisition()
    {
        return $this->belongsTo(BeefRequisition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
