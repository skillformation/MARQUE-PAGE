<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'description',
        'summary',
        'genre',
        'total_pages',
        'current_page',
        'status',
        'cover_image',
        'started_at',
        'completed_at',
        'rating',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
        'rating' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->total_pages || $this->total_pages == 0) {
            return 0;
        }
        
        return min(100, round(($this->current_page / $this->total_pages) * 100));
    }
}
