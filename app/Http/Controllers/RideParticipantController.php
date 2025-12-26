<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckAttendanceRequest;
use App\Http\Requests\JoinRideRequest;
use App\Http\Requests\MarkCompletedRequest;
use App\Models\Ride;
use Illuminate\Http\Request;
use App\Models\RideParticipant;
use App\Models\User;
use App\Services\UserLevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RideParticipantController extends Controller
{
    /**
     * Join a ride
     */
    public function join(Ride $ride, JoinRideRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has fitness profile (required for first ride)
            $fitnessProfile = $user->fitnessProfile;
            
            if (!$fitnessProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب ملء بيانات اللياقة البدنية قبل التسجيل على رايد',
                    'requires_fitness_profile' => true,
                ], 400);
            }

            // Check if user level allows joining this ride
            $userLevelService = new UserLevelService();
            if (!$userLevelService->canUserJoinRide($user, $ride->level)) {
                return response()->json([
                    'success' => false,
                    'message' => 'مستواك الحالي لا يسمح لك بالتسجيل على هذا الرايد. المستوى المطلوب: ' . $ride->level,
                ], 403);
            }

            // Check if user already joined this ride
            $existingParticipant = RideParticipant::where('ride_id', $ride->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingParticipant) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد سجلت على هذا الرايد مسبقاً',
                ], 400);
            }

            // Determine role based on user role
            $role = $user->role === 'coach' ? 'coach' : 'rider';

            // Create participant record
            $participant = RideParticipant::create([
                'ride_id' => $ride->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم التسجيل على الرايد بنجاح',
                'data' => [
                    'participant' => [
                        'id' => $participant->id,
                        'ride_id' => $participant->ride_id,
                        'role' => $participant->role,
                        'status' => $participant->status,
                        'joined_at' => $participant->joined_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error joining ride: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التسجيل على الرايد',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Cancel participation (within 2 hours of joining)
     */
    public function cancel(RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this participant record
            if ($rideParticipant->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإلغاء هذا الحجز',
                ], 403);
            }

            // Check if can cancel (within 2 hours)
            if (!$rideParticipant->canCancel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء الحجز بعد مرور ساعتين من التسجيل',
                ], 400);
            }

            // Update status
            $rideParticipant->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الحجز بنجاح',
                'data' => [
                    'participant' => [
                        'id' => $rideParticipant->id,
                        'status' => $rideParticipant->status,
                        'cancelled_at' => $rideParticipant->cancelled_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error cancelling participation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء الحجز',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Excuse from ride (Admin only - before ride start time)
     */
    public function excuse(RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتنفيذ هذه العملية (Admins فقط)',
                ], 403);
            }

            // Check if can excuse (before ride start)
            if (!$rideParticipant->canExcuse()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن الاعتذار بعد بداية الرايد',
                ], 400);
            }

            // Update status
            $rideParticipant->update([
                'status' => 'excused',
                'excused_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم الاعتذار عن الرايد بنجاح',
                'data' => [
                    'participant' => [
                        'id' => $rideParticipant->id,
                        'user_id' => $rideParticipant->user_id,
                        'status' => $rideParticipant->status,
                        'excused_at' => $rideParticipant->excused_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error excusing from ride: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الاعتذار عن الرايد',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user's rides (rides they participated in)
     */
    public function myRides(): JsonResponse
    {
        try {
            $user = Auth::user();

            $participants = RideParticipant::where('user_id', $user->id)
                ->with(['ride.creator:id,name,email'])
                ->orderBy('joined_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'rides' => $participants->map(function ($participant) {
                        return [
                            'id' => $participant->ride->id,
                            'title' => $participant->ride->title,
                            'level' => $participant->ride->level,
                            'start_time' => $participant->ride->start_time,
                            'status' => $participant->status,
                            'joined_at' => $participant->joined_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $participants->currentPage(),
                        'total' => $participants->total(),
                        'per_page' => $participants->perPage(),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting user rides: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الرايدات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get ride participants (Admin only)
     * Can filter by role (rider/coach) using ?role=rider or ?role=coach
     */
    public function getParticipants(Request $request, Ride $ride): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالوصول إلى هذه البيانات',
                ], 403);
            }

            // Get query builder
            $query = RideParticipant::where('ride_id', $ride->id)
                ->with(['user:id,name,email,phone']);

            // Filter by role if provided
            if ($request->has('role') && in_array($request->role, ['rider', 'coach'])) {
                $query->where('role', $request->role);
            }

            $participants = $query->orderBy('joined_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'participants' => $participants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'user' => [
                                'id' => $participant->user->id,
                                'name' => $participant->user->name,
                                'email' => $participant->user->email,
                                'phone' => $participant->user->phone,
                            ],
                            'role' => $participant->role,
                            'status' => $participant->status,
                            'joined_at' => $participant->joined_at,
                            'cancelled_at' => $participant->cancelled_at,
                            'excused_at' => $participant->excused_at,
                            'completed_at' => $participant->completed_at,
                            'distance_km' => $participant->distance_km,
                            'avg_speed_kmh' => $participant->avg_speed_kmh,
                            'calories_burned' => $participant->calories_burned,
                            'points_earned' => $participant->points_earned,
                        ];
                    }),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting participants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المشاركين',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check attendance (Admin only)
     * 
     * الطريقة الثانية: يدوي من Admin
     * في حالة فشل GPS أو مشاكل تقنية، Admin يقوم بتسجيل الحضور يدوياً
     */
    public function checkAttendance(Ride $ride, CheckAttendanceRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتنفيذ هذه العملية',
                ], 403);
            }

            $participantsData = $request->participants;
            $updated = [];

            foreach ($participantsData as $data) {
                $participant = RideParticipant::where('ride_id', $ride->id)
                    ->where('user_id', $data['user_id'])
                    ->first();

                if ($participant) {
                    $updateData = [
                        'status' => $data['status'],
                        'checked_at' => now(),
                    ];

                    if ($data['status'] === 'completed') {
                        $updateData['completed_at'] = now();
                        $updateData['distance_km'] = $data['distance_km'] ?? null;
                        $updateData['avg_speed_kmh'] = $data['avg_speed_kmh'] ?? null;
                        $updateData['calories_burned'] = $data['calories_burned'] ?? null;
                        $updateData['points_earned'] = $data['points_earned'] ?? null;
                    } elseif ($data['status'] === 'no_show') {
                        // Already handled by job, but admin can manually mark
                    }

                    $participant->update($updateData);
                    $updated[] = $participant->id;

                    // Update user level if status is completed
                    if ($data['status'] === 'completed') {
                        $userLevelService = new UserLevelService();
                        $userLevelService->updateUserLevelAfterRide($participant);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الحضور بنجاح',
                'data' => [
                    'updated_count' => count($updated),
                    'updated_ids' => $updated,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error checking attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الحضور',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Mark participant as completed (from GPS tracking)
     * 
     * الطريقة الأولى: تلقائي عبر GPS
     * عندما يكمل المستخدم الرايد، GPS tracking يرسل البيانات تلقائياً
     */
    public function markCompleted(MarkCompletedRequest $request, RideParticipant $rideParticipant): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user owns this participant record
            if ($rideParticipant->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتنفيذ هذه العملية',
                ], 403);
            }

            // Only allow if status is joined
            if ($rideParticipant->status !== 'joined') {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تحديث حالة الرايد',
                ], 400);
            }

            $data = $request->validated();

            // Update to completed with GPS data
            $rideParticipant->update([
                'status' => 'completed',
                'completed_at' => now(),
                'distance_km' => $data['distance_km'] ?? null,
                'avg_speed_kmh' => $data['avg_speed_kmh'] ?? null,
                'calories_burned' => $data['calories_burned'] ?? null,
                'points_earned' => $data['points_earned'] ?? null,
            ]);

            // Update user level after completing ride
            $userLevelService = new UserLevelService();
            $userLevelService->updateUserLevelAfterRide($rideParticipant);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل إكمال الرايد بنجاح (GPS)',
                'data' => [
                    'participant' => [
                        'id' => $rideParticipant->id,
                        'status' => $rideParticipant->status,
                        'completed_at' => $rideParticipant->completed_at,
                        'distance_km' => $rideParticipant->distance_km,
                        'avg_speed_kmh' => $rideParticipant->avg_speed_kmh,
                        'calories_burned' => $rideParticipant->calories_burned,
                        'points_earned' => $rideParticipant->points_earned,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error marking completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل إكمال الرايد',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
