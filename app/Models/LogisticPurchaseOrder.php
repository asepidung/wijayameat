<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogisticPurchaseOrder extends Model
{
    // Izinkan semua kolom diisi otomatis oleh sistem
    protected $guarded = [];

    /**
     * Konversi tipe data otomatis
     */
    protected $casts = [
        'po_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relasi ke permohonan asalnya
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(LogisticRequisition::class, 'logistic_requisition_id');
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Siapa Finance yang melakukan approval
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Daftar item yang dibeli
     */
    public function items(): HasMany
    {
        return $this->hasMany(LogisticPurchaseOrderItem::class, 'logistic_purchase_order_id');
    }
}
