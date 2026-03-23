<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description', 'image', 'parent_id']);

            return response()->json([
                'success' => true,
                'data' => ['categories' => $categories],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop categories index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل التصنيفات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
