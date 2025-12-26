<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFitnessProfileRequest;
use App\Models\UserFitnessProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserFitnessProfileController extends Controller
{
    /**
     * Create or update fitness profile
     */
    public function store(StoreFitnessProfileRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = $request->validated();

            // Create or update fitness profile
            $fitnessProfile = UserFitnessProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ بيانات اللياقة البدنية بنجاح',
                'data' => [
                    'fitness_profile' => [
                        'id' => $fitnessProfile->id,
                        'height_cm' => $fitnessProfile->height_cm,
                        'weight_kg' => $fitnessProfile->weight_kg,
                        'medical_notes' => $fitnessProfile->medical_notes,
                        'other_sports' => $fitnessProfile->other_sports,
                        'last_ride_date' => $fitnessProfile->last_ride_date,
                        'max_distance_km' => $fitnessProfile->max_distance_km,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error storing fitness profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ بيانات اللياقة البدنية',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user's fitness profile
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            $fitnessProfile = $user->fitnessProfile;

            if (!$fitnessProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم إنشاء ملف اللياقة البدنية بعد',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'fitness_profile' => [
                        'id' => $fitnessProfile->id,
                        'height_cm' => $fitnessProfile->height_cm,
                        'weight_kg' => $fitnessProfile->weight_kg,
                        'medical_notes' => $fitnessProfile->medical_notes,
                        'other_sports' => $fitnessProfile->other_sports,
                        'last_ride_date' => $fitnessProfile->last_ride_date,
                        'max_distance_km' => $fitnessProfile->max_distance_km,
                        'created_at' => $fitnessProfile->created_at,
                        'updated_at' => $fitnessProfile->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting fitness profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات اللياقة البدنية',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
