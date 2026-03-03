<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Tambahin ini

/**
 * @property int $id
 * @property string $po_number
 * @property int $supplier_id
 * @property string $po_date
 * @property string|null $note
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CattlePurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder wherePoDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder wherePoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CattlePurchaseOrder withoutTrashed()
 * @mixin \Eloquent
 */
class CattlePurchaseOrder extends Model
{
    use HasFactory, SoftDeletes; // <-- Panggil di sini

    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'note',
        'created_by'
    ];

    public function setNoteAttribute($value)
    {
        $this->attributes['note'] = strtoupper($value);
    }

    public function items()
    {
        return $this->hasMany(CattlePurchaseOrderItem::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
