<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPayableInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    // --- RELASI ---

    // Relasi standar sesuai nama tabel
    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
    }

    // Alias relasi buat dipanggil di halaman Print Voucher
    public function payable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
    }

    // Relasi ke User (Kasir / Finance yang nginput)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // --- LOGIKA OTOMATIS (LIFECYCLE EVENTS) ---

    protected static function booted()
    {
        // 1. Saat pembayaran baru DIBUAT
        static::created(function ($installment) {
            $installment->recalculateAPBalance();
        });

        // 2. Saat pembayaran DIEDIT
        static::updated(function ($installment) {
            $installment->recalculateAPBalance();
        });

        // 3. Saat pembayaran DIHAPUS
        static::deleted(function ($installment) {
            // Karena data ini udah dihapus, kita panggil AP-nya buat hitung ulang sisa cicilan temen-temennya
            if ($installment->accountPayable) {
                $installment->accountPayable->recalculateBalanceFromInstallments();
            }
        });
    }

    // Fungsi helper buat ngitung dan update ke tabel induk
    public function recalculateAPBalance()
    {
        // 1. Pastikan total reduction selalu update
        $reduction = (float)$this->amount_paid + (float)$this->discount_amount + (float)$this->tax_deduction_amount;
        $this->updateQuietly(['total_debt_reduction' => $reduction]);

        // 2. Update saldo di induk AP
        if ($this->accountPayable) {
            $this->accountPayable->recalculateBalanceFromInstallments();
        }
    }
}
