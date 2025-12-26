<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Mail\OtpMail;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    /**
     * Send OTP code to email or phone
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $identifier = $request->identifier;
            $type = $request->type;

            // Check if user exists (for login/verification scenarios)
            $user = null;
            if ($type === 'email') {
                $user = User::where('email', $identifier)->first();
            } else {
                $user = User::where('phone', $identifier)->first();
            }

            // Generate 6-digit OTP code
            $otpCode = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Invalidate previous unverified OTPs for this identifier
            OtpVerification::where('identifier', $identifier)
                ->where('type', $type)
                ->where('is_verified', false)
                ->update(['is_verified' => true]); // Mark as used

            // Create new OTP verification record
            $otpVerification = OtpVerification::create([
                'user_id' => $user?->id,
                'identifier' => $identifier,
                'type' => $type,
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10), // OTP expires in 10 minutes
            ]);

            // Send OTP via email or SMS
            if ($type === 'email') {
                try {
                    $userName = $user?->name ?? 'المستخدم';
                    Mail::to($identifier)->send(new OtpMail($otpCode, $userName));
                    Log::info("OTP Email sent successfully to: {$identifier}");
                } catch (\Exception $mailException) {
                    Log::error("Failed to send OTP email: " . $mailException->getMessage());
                    // Still return success but log the error
                    // In production, you might want to handle this differently
                }
            } else {
                // TODO: Implement SMS sending service here
                // For now, log it
                Log::info("OTP SMS for phone {$identifier}: {$otpCode}");
            }

            return response()->json([
                'success' => true,
                'message' => $type === 'email' 
                    ? 'تم إرسال رمز التحقق إلى البريد الإلكتروني' 
                    : 'تم إرسال رمز التحقق إلى رقم الهاتف',
                'data' => [
                    'otp_id' => $otpVerification->id,
                    // Show OTP in debug mode for testing (remove in production)
                    'otp_code' => config('app.debug') ? $otpCode : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال رمز التحقق',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $identifier = $request->identifier;
            $type = $request->type;
            $otpCode = $request->otp_code;

            // Find the latest unverified OTP for this identifier
            $otpVerification = OtpVerification::where('identifier', $identifier)
                ->where('type', $type)
                ->where('otp_code', $otpCode)
                ->where('is_verified', false)
                ->latest()
                ->first();

            if (!$otpVerification) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو غير موجود',
                ], 400);
            }

            // Check if OTP is expired
            if ($otpVerification->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق منتهي الصلاحية',
                ], 400);
            }

            // Mark OTP as verified
            $otpVerification->update([
                'is_verified' => true,
                'verified_at' => Carbon::now(),
            ]);

            // Update user verification status
            if ($otpVerification->user_id) {
                $user = User::find($otpVerification->user_id);
                if ($user) {
                    if ($type === 'email') {
                        $user->update(['email_verified_at' => Carbon::now()]);
                    } else {
                        $user->update(['phone_verified_at' => Carbon::now()]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'تم التحقق بنجاح',
                'data' => [
                    'verified' => true,
                    'user_id' => $otpVerification->user_id,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحقق من رمز التحقق',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
