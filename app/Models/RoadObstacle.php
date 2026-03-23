<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Persisted road hazards (potholes / speed bumps) at a GPS location.
 *
 * Deduplication: {@see static::makeLocationKey()} + unique (location_key, type).
 * Use {@see static::recordDetection()} when the same spot is detected again.
 */
class RoadObstacle extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'location_key',
        'type',
        'confidence',
        'detected_at',
        'metadata',
        'reports_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'confidence' => 'decimal:4',
            'detected_at' => 'datetime',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'reports_count' => 'integer',
        ];
    }

    public static function makeLocationKey(float|string $latitude, float|string $longitude): string
    {
        return sprintf('%.5f_%.5f', (float) $latitude, (float) $longitude);
    }

    /**
     * Insert or merge: same location_key + type increments reports_count and refreshes last-known fields.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function recordDetection(array $attributes): self
    {
        $lat = (float) $attributes['latitude'];
        $lng = (float) $attributes['longitude'];
        $type = $attributes['type'];
        $key = static::makeLocationKey($lat, $lng);

        $existing = static::query()
            ->where('location_key', $key)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->increment('reports_count');
            $existing->fill([
                'latitude' => $lat,
                'longitude' => $lng,
                'confidence' => $attributes['confidence'] ?? $existing->confidence,
                'detected_at' => $attributes['detected_at'] ?? now(),
                'metadata' => $attributes['metadata'] ?? $existing->metadata,
            ]);
            $existing->save();

            return $existing->fresh();
        }

        return static::query()->create(array_merge($attributes, [
            'latitude' => $lat,
            'longitude' => $lng,
            'location_key' => $key,
            'type' => $type,
            'reports_count' => 1,
            'detected_at' => $attributes['detected_at'] ?? now(),
        ]));
    }
}
