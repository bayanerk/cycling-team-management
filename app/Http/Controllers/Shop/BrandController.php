<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $brands = Brand::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'logo', 'description']);

            return response()->json([
                'success' => true,
                'data' => ['brands' => $brands],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop brands index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل العلامات التجارية',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
