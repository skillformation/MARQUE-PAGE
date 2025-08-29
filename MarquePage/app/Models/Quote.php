<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $fillable = [
        'book_id',
        'content',
        'page_number',
        'context',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
