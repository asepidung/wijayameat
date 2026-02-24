<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'user_id',
        'quote_text',
        'author_name',
    ];

    /* Relasi untuk mengambil data user yang membuat quote */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}