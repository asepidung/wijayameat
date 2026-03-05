<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPayableInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
    }

    protected static function booted()
    {
        static::created(function ($installment) {
            $ap = $installment->accountPayable;

            // 1. Hitung total pengurangan hutang (Uang keluar + Potongan)
            $reduction = $installment->amount_paid + $installment->discount_amount + $installment->tax_deduction_amount;

            // Simpan nominal reduction ke kolomnya sendiri (update tanpa trigger booted lagi)
            $installment->updateQuietly(['total_debt_reduction' => $reduction]);

            // 2. Update status dan saldo di tabel AccountPayable
            $totalPaidSoFar = $ap->installments()->sum('total_debt_reduction');
            $newBalance = $ap->total_amount - $totalPaidSoFar;

            $ap->update([
                'paid_amount' => $totalPaidSoFar,
                'balance_due' => $newBalance,
                // Status otomatis berubah
                'status' => $newBalance <= 0 ? 'PAID' : ($totalPaidSoFar > 0 ? 'PARTIAL' : 'UNPAID'),
            ]);
        });
    }
}
