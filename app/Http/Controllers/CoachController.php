<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoachRequest;
use App\Http\Requests\UpdateCoachRequest;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CoachController extends Controller
{
    /**
     * Get all coaches (Public)
     */
    public function index(): JsonResponse
    {
        try {
            $coaches = Coach::with('user:id,name,email,phone,profile_image')
                ->orderBy('rating', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'coaches' => $coaches->map(function ($coach) {
                        return [
                            'id' => $coach->id,
                            'user' => [
                                'id' => $coach->user->id,
                                'name' => $coach->user->name,
                                'email' => $coach->user->email,
                                'phone' => $coach->user->phone,
                                'profile_image' => $coach->user->profile_image,
                            ],
                            'bio' => $coach->bio,
                            'experience_years' => $coach->experience_years,
                            'image_url' => $coach->image_url,
                            'specialty' => $coach->specialty,
                            'certificate' => $coach->certificate,
                            'rating' => $coach->rating,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting coaches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الكوتشات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a single coach (Public)
     */
    public function show(Coach $coach): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'coach' => [
                        'id' => $coach->id,
                        'user' => [
                            'id' => $coach->user->id,
                            'name' => $coach->user->name,
                            'email' => $coach->user->email,
                            'phone' => $coach->user->phone,
                            'profile_image' => $coach->user->profile_image,
                        ],
                        'bio' => $coach->bio,
                        'experience_years' => $coach->experience_years,
                        'image_url' => $coach->image_url,
                        'specialty' => $coach->specialty,
                        'certificate' => $coach->certificate,
                        'rating' => $coach->rating,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting coach: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الكوتش',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new coach (Admin only)
     */
    public function store(StoreCoachRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإنشاء كوتش (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();

            $coach = Coach::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الكوتش بنجاح',
                'data' => [
                    'coach' => $coach->load('user:id,name,email,phone'),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating coach: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الكوتش',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update a coach (Admin only)
     */
    public function update(UpdateCoachRequest $request, Coach $coach): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث كوتش (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();
            $coach->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الكوتش بنجاح',
                'data' => [
                    'coach' => $coach->load('user:id,name,email,phone'),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating coach: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الكوتش',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a coach (Admin only)
     */
    public function destroy(Coach $coach): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف كوتش (Admins فقط)',
                ], 403);
            }

            $coach->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الكوتش بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting coach: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الكوتش',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
