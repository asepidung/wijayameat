<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepackMaterial extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function repack()
    {
        return $this->belongsTo(Repack::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
