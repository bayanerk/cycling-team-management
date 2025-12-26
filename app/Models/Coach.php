<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coach extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'image_url',
        'specialty',
        'certificate',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'rating' => 'decimal:2',
        ];
    }

    /**
     * Get the user that this coach belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
