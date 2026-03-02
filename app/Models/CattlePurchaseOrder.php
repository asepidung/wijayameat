<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CattlePurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'term_of_payment',
        'total_amount',
        'status',
        'note',
        'created_by',
        'approved_by'
    ];

    /* Paksa Note jadi HURUF BESAR saat disimpan */
    public function setNoteAttribute($value)
    {
        $this->attributes['note'] = strtoupper($value);
    }

    public function items()
    {
        return $this->hasMany(CattlePurchaseOrderItem::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
