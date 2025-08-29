<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'book_id',
        'page_number',
        'color',
        'note',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
