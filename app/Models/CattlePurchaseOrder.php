<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Tambahin ini

class CattlePurchaseOrder extends Model
{
    use HasFactory, SoftDeletes; // <-- Panggil di sini

    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'note',
        'created_by'
    ];

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
}
