<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    // Ini kuncinya biar data bisa masuk ke database
    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'phone',
        'term_of_payment',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'is_active'
    ];
}
