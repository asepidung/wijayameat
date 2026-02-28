<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeefRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'user_id',
        'supplier_id',
        'due_date',
        'total_amount',
        'note',
        'status',
        'reject_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(BeefRequisitionItem::class);
    }
}
