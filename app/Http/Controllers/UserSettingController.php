<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserSettingRequest;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserSettingController extends Controller
{
    /**
     * Get user settings
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::user();

            $setting = $user->setting;

            // If no setting exists, create default one
            if (!$setting) {
                $setting = UserSetting::create([
                    'user_id' => $user->id,
                    'language' => 'ar',
                    'notification_enabled' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'setting' => [
                        'id' => $setting->id,
                        'language' => $setting->language,
                        'notification_enabled' => $setting->notification_enabled,
                        'created_at' => $setting->created_at,
                        'updated_at' => $setting->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإعدادات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update user settings
     */
    public function update(UpdateUserSettingRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = $request->validated();

            // Create or update settings
            $setting = UserSetting::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الإعدادات بنجاح',
                'data' => [
                    'setting' => [
                        'id' => $setting->id,
                        'language' => $setting->language,
                        'notification_enabled' => $setting->notification_enabled,
                        'updated_at' => $setting->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating user settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الإعدادات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
