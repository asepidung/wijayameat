<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeefPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'beef_requisition_id',
        'supplier_id',
        'approved_by',
        'po_date',
        'total_amount',
        'note',
    ];

    /* Relasi ke Request Beef */
    public function requisition()
    {
        return $this->belongsTo(BeefRequisition::class, 'beef_requisition_id');
    }

    /* Relasi ke Supplier */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /* Relasi ke User Finance yang menyetujui */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* Relasi ke item PO */
    public function items()
    {
        return $this->hasMany(BeefPurchaseOrderItem::class);
    }
}
