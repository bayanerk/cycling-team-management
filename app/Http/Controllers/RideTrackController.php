<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrackRequest;
use App\Http\Requests\StoreTracksRequest;
use App\Models\RideParticipant;
use App\Models\RideTrack;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RideTrackController extends Controller
{
    /**
     * Store a single GPS track point
     * 
     * Used when sending GPS data continuously (every 3 seconds or every 5 meters)
     */
    public function storeTrack(StoreTrackRequest $request, RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this participant record
            if ($rideParticipant->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإرسال بيانات GPS لهذا الرايد',
                ], 403);
            }

            // Check if participant is still in 'joined' status
            if ($rideParticipant->status !== 'joined') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إرسال بيانات GPS (الحالة غير صالحة)',
                ], 400);
            }

            $data = $request->validated();

            // Create track point
            $track = RideTrack::create([
                'ride_id' => $rideParticipant->ride_id,
                'ride_participant_id' => $rideParticipant->id,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'speed' => $data['speed'],
                'recorded_at' => $data['recorded_at'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ نقطة GPS بنجاح',
                'data' => [
                    'track' => [
                        'id' => $track->id,
                        'lat' => $track->lat,
                        'lng' => $track->lng,
                        'speed' => $track->speed,
                        'recorded_at' => $track->recorded_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing track: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ نقطة GPS',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Store multiple GPS track points in bulk
     * 
     * Used for sending multiple points at once (batch upload)
     */
    public function storeTracks(StoreTracksRequest $request, RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this participant record
            if ($rideParticipant->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإرسال بيانات GPS لهذا الرايد',
                ], 403);
            }

            // Check if participant is still in 'joined' status
            if ($rideParticipant->status !== 'joined') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إرسال بيانات GPS (الحالة غير صالحة)',
                ], 400);
            }

            $tracksData = $request->validated()['tracks'];
            $created = [];

            // Insert all tracks in bulk
            foreach ($tracksData as $trackData) {
                $track = RideTrack::create([
                    'ride_id' => $rideParticipant->ride_id,
                    'ride_participant_id' => $rideParticipant->id,
                    'lat' => $trackData['lat'],
                    'lng' => $trackData['lng'],
                    'speed' => $trackData['speed'],
                    'recorded_at' => $trackData['recorded_at'],
                ]);
                $created[] = $track->id;
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ نقاط GPS بنجاح',
                'data' => [
                    'count' => count($created),
                    'track_ids' => $created,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing tracks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ نقاط GPS',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get all GPS tracks for a participant
     * 
     * Used for:
     * - Drawing map polyline
     * - Calculating distance
     * - Calculating average speed
     * - Detecting no-show
     */
    public function getTracks(RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this participant record OR is admin
            if ($rideParticipant->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى بيانات GPS',
                ], 403);
            }

            // Get all tracks ordered by recorded_at
            $tracks = RideTrack::where('ride_participant_id', $rideParticipant->id)
                ->orderBy('recorded_at', 'asc')
                ->get();

            // Calculate statistics
            $totalDistance = $this->calculateDistance($tracks);
            $avgSpeed = $tracks->avg('speed');
            $maxSpeed = $tracks->max('speed');
            $minSpeed = $tracks->min('speed');
            $duration = $this->calculateDuration($tracks);

            return response()->json([
                'success' => true,
                'data' => [
                    'participant_id' => $rideParticipant->id,
                    'ride_id' => $rideParticipant->ride_id,
                    'tracks' => $tracks->map(function ($track) {
                        return [
                            'id' => $track->id,
                            'lat' => (float) $track->lat,
                            'lng' => (float) $track->lng,
                            'speed' => (float) $track->speed,
                            'recorded_at' => $track->recorded_at,
                        ];
                    }),
                    'statistics' => [
                        'total_points' => $tracks->count(),
                        'total_distance_km' => round($totalDistance, 2),
                        'average_speed_kmh' => round($avgSpeed, 2),
                        'max_speed_kmh' => round($maxSpeed, 2),
                        'min_speed_kmh' => round($minSpeed, 2),
                        'duration_minutes' => round($duration, 2),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting tracks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات GPS',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Calculate total distance from GPS tracks using Haversine formula
     */
    private function calculateDistance($tracks): float
    {
        if ($tracks->count() < 2) {
            return 0;
        }

        $totalDistance = 0;
        $previousTrack = null;

        foreach ($tracks as $track) {
            if ($previousTrack) {
                $distance = $this->haversineDistance(
                    $previousTrack->lat,
                    $previousTrack->lng,
                    $track->lat,
                    $track->lng
                );
                $totalDistance += $distance;
            }
            $previousTrack = $track;
        }

        return $totalDistance; // in kilometers
    }

    /**
     * Haversine formula to calculate distance between two GPS points
     */
    private function haversineDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate duration in minutes from first to last track
     */
    private function calculateDuration($tracks): float
    {
        if ($tracks->count() < 2) {
            return 0;
        }

        $firstTrack = $tracks->first();
        $lastTrack = $tracks->last();

        $startTime = $firstTrack->recorded_at;
        $endTime = $lastTrack->recorded_at;

        return $startTime->diffInMinutes($endTime);
    }
}
