<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFitnessProfile extends Model
{
    protected $fillable = [
        'user_id',
        'height_cm',
        'weight_kg',
        'medical_notes',
        'other_sports',
        'last_ride_date',
        'max_distance_km',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'last_ride_date' => 'date',
            'max_distance_km' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns this fitness profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
