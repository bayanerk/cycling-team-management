<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'gender' => $data['gender'] ?? null,
                'age' => $data['age'] ?? null,
                'birthday' => $data['birthday'] ?? null,
                'profession' => $data['profession'] ?? null,
                'profile_image' => $profileImagePath,
                'role' => $data['role'] ?? 'rider',
                'language' => $data['language'] ?? 'ar',
                'is_active' => true,
            ]);

            // Generate token for API authentication (if using Sanctum/Passport)
            // For now, we'll use session-based auth
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'gender' => $user->gender,
                        'age' => $user->age,
                        'birthday' => $user->birthday,
                        'role' => $user->role,
                        'language' => $user->language,
                        'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                    ],
                    'requires_verification' => true,
                    'message' => 'يرجى التحقق من البريد الإلكتروني أو رقم الهاتف',
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            // Attempt to authenticate user
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // Check if user is active
                if (!$user->is_active) {
                    Auth::logout();
                    return response()->json([
                        'success' => false,
                        'message' => 'الحساب معطل. يرجى التواصل مع الإدارة',
                    ], 403);
                }

                // Create API token for the user using Passport
                $token = $user->createToken('auth-token')->accessToken;

                $response = response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل الدخول بنجاح',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'role' => $user->role,
                            'language' => $user->language,
                            'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                            'email_verified' => $user->email_verified_at !== null,
                            'phone_verified' => $user->phone_verified_at !== null,
                            'gender' => $user->gender,
                            'age' => $user->age,
                            'birthday' => $user->birthday,
                        ],
                        'token' => $token,
                    ]
                ], 200);

                // Set CORS headers for API requests
                $response->header('Access-Control-Allow-Credentials', 'true');
                
                return $response;
            }

            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ], 401);

        } catch (\Exception $e) {
            Log::error('Error logging in user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدخول',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Revoke the token that was used to authenticate the current request
            if ($user) {
                $token = $user->token();
                if ($token) {
                    $token->revoke();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الخروج بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error logging out user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الخروج',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'المستخدم غير مسجل الدخول',
                ], 401);
            }

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
                        'role' => $user->role,
                        'language' => $user->language,
                        'profile_image' => $user->profile_image ? Storage::url($user->profile_image) : null,
                        'email_verified' => $user->email_verified_at !== null,
                        'phone_verified' => $user->phone_verified_at !== null,
                        'is_active' => $user->is_active,
                        'is_coach_approved' => $user->is_coach_approved,
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات المستخدم',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
