<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountPayable extends Model
{
    use HasFactory;

    // Buka gerbang mass assignment
    protected $guarded = [];

    /**
     * RELASI UNIVERSAL (Polymorphic)
     * Relasi ini memungkinkan AP terhubung ke berbagai model PO:
     * - LogisticPurchaseOrder
     * - BeefPurchaseOrder
     * - CattlePurchaseOrder
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke history pembayaran / cicilan
     */
    public function installments(): HasMany
    {
        return $this->hasMany(AccountPayableInstallment::class, 'account_payable_id');
    }

    /**
     * Relasi ke pembuat data (Finance Admin/System)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
