<?php

namespace App\Http\Controllers;

use App\Models\UserLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserLevelController extends Controller
{
    /**
     * Get user's level
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            $userLevel = $user->level;

            if (!$userLevel) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم إنشاء مستوى المستخدم بعد',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'level' => [
                        'id' => $userLevel->id,
                        'level_name' => $userLevel->level_name,
                        'level_number' => $userLevel->level_number,
                        'total_distance' => $userLevel->total_distance,
                        'total_rides' => $userLevel->total_rides,
                        'total_points' => $userLevel->total_points,
                        'last_updated' => $userLevel->last_updated,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user level: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مستوى المستخدم',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
