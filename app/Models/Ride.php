<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    protected $fillable = [
        'title',
        'location',
        'level',
        'distance',
        'start_time',
        'gathering_time',
        'end_time',
        'image_url',
        'break_location',
        'cost',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'distance' => 'decimal:2',
            'cost' => 'decimal:2',
            'start_time' => 'datetime',
            'gathering_time' => 'datetime',
            'end_time' => 'datetime',
            'start_lat' => 'float',
            'start_lng' => 'float',
            'end_lat' => 'float',
            'end_lng' => 'float',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all participants for this ride.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(RideParticipant::class);
    }
}
