<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'unit_id',
        'parent_id',
        'name',
        'code',
        'stock',
        'is_active',
    ];

    // Relasi ke Kategori
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke Satuan
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Relasi ke Bapaknya (Induk)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // Relasi ke Anak-anaknya (Turunan/Varian)
    public function children(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }
}
