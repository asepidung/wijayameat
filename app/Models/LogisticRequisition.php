<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB; // Tambahkan ini

class LogisticRequisition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_number',
        'user_id',
        'supplier_id',
        'due_date',
        'note',
        'terms_of_payment',
        'tax_type',
        'tax_amount',
        'total_amount',
        'status'
    ];

    /* LOGIKA AUTO-GENERATE NOMOR REQUEST (AMAN DARI BENTROK) */
    protected static function booted()
    {
        static::creating(function ($model) {
            DB::transaction(function () use ($model) {
                $currentYear = date('Y');

                // Hitung jumlah data tahun ini dan kunci row agar tidak bentrok
                $count = DB::table('logistic_requisitions')
                    ->whereYear('created_at', $currentYear)
                    ->lockForUpdate()
                    ->count();

                $nextNumber = $count + 1;
                $requestNumber = sprintf("%04s", $nextNumber);

                // Masukkan langsung ke model sebelum di-save ke database
                $model->document_number = "LRQ-" . substr($currentYear, 2) . $requestNumber;
            });
        });
    }

    public function items()
    {
        return $this->hasMany(LogisticRequisitionItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
