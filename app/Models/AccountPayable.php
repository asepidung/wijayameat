<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountPayable extends Model
{
    use HasFactory;

    // INI DIA KUNCI JAWABANNYA BRO: Buka gerbang mass assignment!
    protected $guarded = [];

    // Sekalian gue tulisin relasinya nih biar ke depannya gampang narik data
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(LogisticPurchaseOrder::class, 'logistic_purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(AccountPayableInstallment::class, 'account_payable_id');
    }
}
