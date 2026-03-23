<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RideParticipant extends Model
{
    protected $fillable = [
        'ride_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'cancelled_at',
        'excused_at',
        'completed_at',
        'checked_at',
        'distance_km',
        'avg_speed_kmh',
        'calories_burned',
        'points_earned',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'excused_at' => 'datetime',
            'completed_at' => 'datetime',
            'checked_at' => 'datetime',
            'distance_km' => 'decimal:2',
            'avg_speed_kmh' => 'decimal:2',
            'calories_burned' => 'integer',
            'points_earned' => 'integer',
        ];
    }

    /**
     * Get the ride that this participant joined.
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Get the user who is participating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all GPS tracks for this participant.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(RideTrack::class, 'ride_participant_id');
    }

    /**
     * Check if participant can cancel (within 2 hours of joining).
     */
    public function canCancel(): bool
    {
        if ($this->status !== 'joined') {
            return false;
        }

        $twoHoursAgo = now()->subHours(2);

        return $this->joined_at->isAfter($twoHoursAgo);
    }

    /**
     * Check if participant can excuse (before ride start time).
     */
    public function canExcuse(): bool
    {
        if (! in_array($this->status, ['joined', 'cancelled'])) {
            return false;
        }

        return $this->ride->start_time->isFuture();
    }
}
