<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoryRequest;
use App\Http\Requests\UpdateMemoryRequest;
use App\Models\Memory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MemoryController extends Controller
{
    /**
     * Get all memories (Public - only active)
     */
    public function index(): JsonResponse
    {
        try {
            // Get only active memories for public
            $memories = Memory::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'memories' => $memories->map(function ($memory) {
                        return [
                            'id' => $memory->id,
                            'image_path' => Storage::url($memory->image_path),
                            'description' => $memory->description,
                            'photo_date' => $memory->photo_date,
                            'created_at' => $memory->created_at,
                        ];
                    }),
                    'pagination' => [
                        'current_page' => $memories->currentPage(),
                        'total' => $memories->total(),
                        'per_page' => $memories->perPage(),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting memories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الذكريات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a single memory (Public)
     */
    public function show(Memory $memory): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'memory' => [
                        'id' => $memory->id,
                        'image_path' => Storage::url($memory->image_path),
                        'description' => $memory->description,
                        'is_active' => $memory->is_active,
                        'photo_date' => $memory->photo_date,
                        'created_at' => $memory->created_at,
                        'updated_at' => $memory->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting memory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الذكرى',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new memory (Admin only)
     */
    public function store(StoreMemoryRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بإضافة ذكرى (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('memories', 'public');
            }

            $memory = Memory::create([
                'image_path' => $imagePath,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'photo_date' => $data['photo_date'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الذكرى بنجاح',
                'data' => [
                    'memory' => [
                        'id' => $memory->id,
                        'image_path' => Storage::url($memory->image_path),
                        'description' => $memory->description,
                        'is_active' => $memory->is_active,
                        'photo_date' => $memory->photo_date,
                        'created_at' => $memory->created_at,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating memory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الذكرى',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update a memory (Admin only)
     */
    public function update(UpdateMemoryRequest $request, Memory $memory): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث ذكرى (Admins فقط)',
                ], 403);
            }

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($memory->image_path && Storage::disk('public')->exists($memory->image_path)) {
                    Storage::disk('public')->delete($memory->image_path);
                }

                // Store new image
                $imagePath = $request->file('image')->store('memories', 'public');
                $data['image_path'] = $imagePath;
            }

            $memory->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الذكرى بنجاح',
                'data' => [
                    'memory' => [
                        'id' => $memory->id,
                        'image_path' => Storage::url($memory->image_path),
                        'description' => $memory->description,
                        'is_active' => $memory->is_active,
                        'photo_date' => $memory->photo_date,
                        'updated_at' => $memory->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating memory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الذكرى',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a memory (Admin only)
     */
    public function destroy(Memory $memory): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بحذف ذكرى (Admins فقط)',
                ], 403);
            }

            // Delete image if exists
            if ($memory->image_path && Storage::disk('public')->exists($memory->image_path)) {
                Storage::disk('public')->delete($memory->image_path);
            }

            $memory->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الذكرى بنجاح',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting memory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الذكرى',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
