<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticRequisitionItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'logistic_requisition_id',
        'logistic_item_id',
        'qty',
        'price',
        'note',
    ];

    /**
     * Get the requisition that owns the item.
     */
    public function requisition()
    {
        return $this->belongsTo(LogisticRequisition::class, 'logistic_requisition_id');
    }

    /**
     * Get the logistic item associated with the record.
     */
    public function item()
    {
        return $this->belongsTo(LogisticItem::class, 'logistic_item_id');
    }
}
