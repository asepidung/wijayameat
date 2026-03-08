<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CattleWeighingItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cattle_weighing_id',
        'cattle_receiving_item_id',
        'weight',
        'notes'
    ];

    // Relasi balik ke Header Timbangan
    public function weighing(): BelongsTo
    {
        return $this->belongsTo(CattleWeighing::class, 'cattle_weighing_id');
    }

    // Relasi ke Data Sapi Asli di GRC (PENTING: Buat narik Eartag & Initial Weight)
    public function receivingItem(): BelongsTo
    {
        return $this->belongsTo(CattleReceivingItem::class, 'cattle_receiving_item_id');
    }
}
