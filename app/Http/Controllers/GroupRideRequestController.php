<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectGroupRideRequest;
use App\Http\Requests\StoreGroupRideRequest;
use App\Http\Requests\UpdateGroupRideRequest;
use App\Models\GroupRideRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class GroupRideRequestController extends Controller
{
    /**
     * طلبات المستخدم الحالي (حجز جماعي — تنسيق فقط).
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = GroupRideRequest::query()
            ->where('user_id', $user->id)
            ->with(['user:id,name,email,phone', 'reviewer:id,name,role'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        $items = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $items->through(fn (GroupRideRequest $r) => $this->transform($r)),
        ]);
    }

    /**
     * إنشاء طلب حجز رايد جماعي.
     */
    public function store(StoreGroupRideRequest $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['status'] = 'pending';

        $record = GroupRideRequest::create($data)->load(['user:id,name,email,phone', 'reviewer:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الحجز بنجاح وهو قيد المراجعة.',
            'data' => $this->transform($record),
        ], 201);
    }

    /**
     * عرض طلب واحد (صاحب الطلب أو الأدمن).
     */
    public function show(GroupRideRequest $groupRideRequest): JsonResponse
    {
        $user = Auth::user();

        if (! $user->isAdmin() && (int) $groupRideRequest->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا الطلب.',
            ], 403);
        }

        $groupRideRequest->load(['user:id,name,email,phone,role', 'reviewer:id,name,role']);

        return response()->json([
            'success' => true,
            'data' => $this->transform($groupRideRequest),
        ]);
    }

    /**
     * تعديل طلب ما زال قيد الانتظار (صاحب الطلب فقط).
     */
    public function update(UpdateGroupRideRequest $request, GroupRideRequest $groupRideRequest): JsonResponse
    {
        $user = Auth::user();

        if ((int) $groupRideRequest->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا الطلب.',
            ], 403);
        }

        if ($groupRideRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تعديل الطلب بعد مراجعته من الإدارة.',
            ], 422);
        }

        $groupRideRequest->update($request->validated());
        $groupRideRequest->load(['user:id,name,email,phone', 'reviewer:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الطلب بنجاح.',
            'data' => $this->transform($groupRideRequest),
        ]);
    }

    /**
     * جميع الطلبات (أدمن) مع فلترة اختيارية بالحالة.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذه القائمة (Admins فقط).',
            ], 403);
        }

        $query = GroupRideRequest::query()
            ->with(['user:id,name,email,phone,role', 'reviewer:id,name,role'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        $items = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $items->through(fn (GroupRideRequest $r) => $this->transform($r)),
        ]);
    }

    /**
     * قبول الطلب (أدمن).
     */
    public function approve(GroupRideRequest $groupRideRequest): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك باعتماد الطلب (Admins فقط).',
            ], 403);
        }

        try {
            $groupRideRequest->approveBy($user);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $groupRideRequest->load(['user:id,name,email,phone,role', 'reviewer:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'تم اعتماد طلب الحجز.',
            'data' => $this->transform($groupRideRequest),
        ]);
    }

    /**
     * رفض الطلب (أدمن).
     */
    public function reject(RejectGroupRideRequest $request, GroupRideRequest $groupRideRequest): JsonResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك برفض الطلب (Admins فقط).',
            ], 403);
        }

        $validated = $request->validated();

        try {
            $groupRideRequest->rejectBy($user, $validated['admin_notes'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $groupRideRequest->load(['user:id,name,email,phone,role', 'reviewer:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب الحجز.',
            'data' => $this->transform($groupRideRequest),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(GroupRideRequest $r): array
    {
        return [
            'id' => $r->id,
            'title' => $r->title,
            'group_name' => $r->group_name,
            'group_type' => $r->group_type,
            'location' => $r->location,
            'scheduled_at' => $r->scheduled_at?->toIso8601String(),
            'people_count' => $r->people_count,
            'people_count_min' => GroupRideRequest::MIN_PEOPLE,
            'people_count_max' => GroupRideRequest::MAX_PEOPLE,
            'status' => $r->status,
            'admin_notes' => $r->admin_notes,
            'reviewed_at' => $r->reviewed_at?->toIso8601String(),
            'created_at' => $r->created_at?->toIso8601String(),
            'updated_at' => $r->updated_at?->toIso8601String(),
            'user' => $r->relationLoaded('user') && $r->user ? [
                'id' => $r->user->id,
                'name' => $r->user->name,
                'email' => $r->user->email,
                'phone' => $r->user->phone,
                'role' => $r->user->role ?? null,
            ] : null,
            'reviewer' => $r->relationLoaded('reviewer') && $r->reviewer ? [
                'id' => $r->reviewer->id,
                'name' => $r->reviewer->name,
                'role' => $r->reviewer->role ?? null,
            ] : null,
        ];
    }
}
