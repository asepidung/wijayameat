<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_group_id',
        'segment_id',
        'name',
        'address',
        'phone',
        'top_days',
        'req_po',       // Pastikan 8 dokumen ini masuk semua!
        'req_invoice',
        'req_halal',
        'req_uji_lab',
        'req_nkv',
        'req_sv',
        'req_phd',
        'req_joss',
        'is_active',
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
