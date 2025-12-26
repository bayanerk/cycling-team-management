<?php

namespace App\Services;

use App\Models\RideParticipant;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Support\Facades\Log;

class UserLevelService
{
    /**
     * Initialize user level based on max_distance_km from fitness profile
     */
    public function initializeUserLevel(User $user): UserLevel
    {
        $fitnessProfile = $user->fitnessProfile;

        if (!$fitnessProfile || !$fitnessProfile->max_distance_km) {
            // Default to Beginner if no fitness profile
            $levelName = 'Beginner';
            $levelNumber = 1;
        } else {
            // Determine initial level based on max_distance_km
            if ($fitnessProfile->max_distance_km < 30) {
                $levelName = 'Beginner';
                $levelNumber = 1;
            } else {
                $levelName = 'Intermediate';
                $levelNumber = 1;
            }
        }

        return UserLevel::create([
            'user_id' => $user->id,
            'level_name' => $levelName,
            'level_number' => $levelNumber,
            'total_distance' => 0,
            'total_rides' => 0,
            'total_points' => 0,
            'last_updated' => now(),
        ]);
    }

    /**
     * Update user level after completing a ride
     */
    public function updateUserLevelAfterRide(RideParticipant $participant): void
    {
        try {
            $user = $participant->user;
            $userLevel = $user->level;

            // If no level exists, initialize it
            if (!$userLevel) {
                $userLevel = $this->initializeUserLevel($user);
            }

            // Update performance metrics
            $userLevel->total_distance += $participant->distance_km ?? 0;
            $userLevel->total_rides += 1;
            $userLevel->total_points += $participant->points_earned ?? 0;
            $userLevel->last_updated = now();
            $userLevel->save();

            // Check for level promotion
            $this->checkAndPromoteLevel($userLevel);

        } catch (\Exception $e) {
            Log::error('Error updating user level: ' . $e->getMessage());
        }
    }

    /**
     * Check if user should be promoted to next level
     */
    protected function checkAndPromoteLevel(UserLevel $userLevel): void
    {
        $currentLevel = $userLevel->level_name;
        $currentNumber = $userLevel->level_number;

        // Count completed rides in current level
        $completedRidesInLevel = $this->countCompletedRidesInLevel($userLevel->user_id, $currentLevel);

        // Promotion rules
        if ($currentLevel === 'Beginner' && $completedRidesInLevel >= 3) {
            // Promote to Intermediate 1
            $userLevel->level_name = 'Intermediate';
            $userLevel->level_number = 1;
            $userLevel->save();
            Log::info("User {$userLevel->user_id} promoted from Beginner to Intermediate 1");

        } elseif ($currentLevel === 'Intermediate' && $completedRidesInLevel >= 6) {
            // Promote to Advanced 1
            $userLevel->level_name = 'Advanced';
            $userLevel->level_number = 1;
            $userLevel->save();
            Log::info("User {$userLevel->user_id} promoted from Intermediate to Advanced 1");
        }
    }

    /**
     * Count completed rides in current level
     */
    protected function countCompletedRidesInLevel(int $userId, string $levelName): int
    {
        return RideParticipant::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereHas('ride', function ($query) use ($levelName) {
                $query->where('level', $levelName);
            })
            ->count();
    }

    /**
     * Check if user can join a ride based on their level
     */
    public function canUserJoinRide(User $user, string $rideLevel): bool
    {
        $userLevel = $user->level;

        // If no level exists, initialize it
        if (!$userLevel) {
            $userLevel = $this->initializeUserLevel($user);
        }

        $userLevelName = $userLevel->level_name;

        // Level hierarchy: Beginner < Intermediate < Advanced
        $levelHierarchy = [
            'Beginner' => 1,
            'Intermediate' => 2,
            'Advanced' => 3,
        ];

        $userLevelValue = $levelHierarchy[$userLevelName] ?? 1;
        $requiredLevelValue = $levelHierarchy[$rideLevel] ?? 1;

        // User can join if their level is equal or higher than required
        return $userLevelValue >= $requiredLevelValue;
    }
}

