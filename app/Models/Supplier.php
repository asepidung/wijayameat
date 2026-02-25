<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    /**
     * Mendefinisikan atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'phone',
        'term_of_payment',
        'has_tax',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'is_active'
    ];

    /**
     * Mengonversi tipe data atribut saat diakses atau disimpan.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_tax' => 'boolean',
        'is_active' => 'boolean',
    ];
}
