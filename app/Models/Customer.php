<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment_id',
        'customer_group_id',
        'name',
        'email',
        'phone',
        'address',
        'is_tukar_faktur',
        'term_of_payment',
        'document_requirements',
        'notes',
    ];

    // Penting: Biar Laravel otomatis ubah JSON jadi Array PHP
    protected $casts = [
        'document_requirements' => 'array',
        'is_tukar_faktur' => 'boolean',
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
