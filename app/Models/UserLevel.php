<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevel extends Model
{
    protected $fillable = [
        'user_id',
        'level_name',
        'level_number',
        'total_distance',
        'total_rides',
        'total_points',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'total_distance' => 'decimal:2',
            'total_rides' => 'integer',
            'total_points' => 'integer',
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this level.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
