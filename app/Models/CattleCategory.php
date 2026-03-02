<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CattleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    // Umumnya kategori ini akan digunakan di detail PO Cattle nanti
    public function purchaseOrderItems()
    {
        return $this->hasMany(CattlePurchaseOrderItem::class);
    }
}
