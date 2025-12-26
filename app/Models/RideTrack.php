<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideTrack extends Model
{
    protected $fillable = [
        'ride_id',
        'ride_participant_id',
        'lat',
        'lng',
        'speed',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'speed' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Get the ride that this track belongs to.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Get the participant that this track belongs to.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(RideParticipant::class, 'ride_participant_id');
    }
}
