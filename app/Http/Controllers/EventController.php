<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Get all events (Public)
     */
    public function index(): JsonResponse
    {
        try {
            $events = Event::with('creator:id,name,email')
                ->orderBy('start_time', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $events,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting events: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأحداث',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a single event (Public)
     */
    public function show(Event $event): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $event->load('creator:id,name,email'),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الحدث',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new event (Admin only)
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإنشاء حدث (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();
            $data['created_by'] = $user->id;

            $event = Event::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحدث بنجاح',
                'data' => $event->load('creator:id,name,role'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحدث',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update an event (Admin only)
     */
    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث حدث (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();
            $event->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحدث بنجاح',
                'data' => $event->load('creator:id,name,role'),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحدث',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete an event (Admin only)
     */
    public function destroy(Event $event): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف حدث (Admins فقط)',
                ], 403);
            }

            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الحدث بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الحدث',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
