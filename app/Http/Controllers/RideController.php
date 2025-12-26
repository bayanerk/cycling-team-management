<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRideRequest;
use App\Http\Requests\UpdateRideRequest;
use App\Models\Ride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideController extends Controller
{
    /**
     * Public list of rides for Home Screen.
     * Supports search by location and level.
     * 
     * Query parameters:
     * - location: Filter by location (e.g., ?location=جدة)
     * - level: Filter by level (e.g., ?level=Intermediate)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ride::with('creator:id,name,role');

        // Filter by location if provided
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        // Filter by level if provided
        if ($request->filled('level')) {
            $validLevels = ['Beginner', 'Intermediate', 'Advanced'];
            $level = $request->input('level');
            if (in_array($level, $validLevels)) {
                $query->where('level', $level);
            }
        }

        $rides = $query->orderByDesc('start_time')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $rides,
        ]);
    }

    /**
     * Store a newly created ride (admin only).
     */
    public function store(StoreRideRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإنشاء رايد (Admins فقط)',
            ], 403);
        }

        $data = $request->validated();
        $data['created_by'] = $user->id;

        $ride = Ride::create($data);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الرايد بنجاح',
            'data' => $ride->load('creator:id,name,role'),
        ], 201);
    }

    /**
     * Display a single ride.
     */
    public function show(Ride $ride): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $ride->load('creator:id,name,role'),
        ]);
    }

    /**
     * Update a ride (admin only).
     */
    public function update(UpdateRideRequest $request, Ride $ride): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل الرايد (Admins فقط)',
            ], 403);
        }

        $ride->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الرايد بنجاح',
            'data' => $ride->fresh()->load('creator:id,name,role'),
        ]);
    }

    /**
     * Remove a ride (admin only).
     */
    public function destroy(Ride $ride): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف الرايد (Admins فقط)',
            ], 403);
        }

        $ride->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الرايد بنجاح',
        ]);
    }
}
