<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    /**
     * Get user profile information
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Load related data
            $user->load(['fitnessProfile', 'level', 'setting', 'addresses']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'gender' => $user->gender,
                        'age' => $user->age,
                        'birthday' => $user->birthday,
                        'profession' => $user->profession,
                        'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                        'role' => $user->role,
                        'language' => $user->language,
                        'is_active' => $user->is_active,
                        'email_verified_at' => $user->email_verified_at,
                        'phone_verified_at' => $user->phone_verified_at,
                    ],
                    'fitness_profile' => $user->fitnessProfile ? [
                        'height_cm' => $user->fitnessProfile->height_cm,
                        'weight_kg' => $user->fitnessProfile->weight_kg,
                        'medical_notes' => $user->fitnessProfile->medical_notes,
                        'other_sports' => $user->fitnessProfile->other_sports,
                        'last_ride_date' => $user->fitnessProfile->last_ride_date,
                        'max_distance_km' => $user->fitnessProfile->max_distance_km,
                    ] : null,
                    'level' => $user->level ? [
                        'level_name' => $user->level->level_name,
                        'level_number' => $user->level->level_number,
                        'total_distance' => $user->level->total_distance,
                        'total_rides' => $user->level->total_rides,
                        'total_points' => $user->level->total_points,
                    ] : null,
                    'addresses' => $user->addresses->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'city' => $address->city,
                            'district' => $address->district,
                            'street' => $address->street,
                        ];
                    }),
                    'settings' => $user->setting ? [
                        'language' => $user->setting->language,
                        'notification_enabled' => $user->setting->notification_enabled,
                    ] : null,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب معلومات الملف الشخصي',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = $request->validated();

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                // Store new image
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
                $data['profile_image'] = $profileImagePath;
            }

            // Update user
            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'profession' => $user->profession,
                        'birthday' => $user->birthday,
                        'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating user profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الملف الشخصي',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function destroy(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Delete profile image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Delete user (cascade will handle related records)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الحساب بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting user account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
