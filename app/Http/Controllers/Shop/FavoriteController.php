<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreFavoriteRequest;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FavoriteController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $favorites = Favorite::query()
                ->where('user_id', $user->id)
                ->with(['product' => fn ($q) => $q->where('is_active', true)->with(['category:id,name,slug', 'brand:id,name', 'images'])])
                ->orderByDesc('created_at')
                ->get();

            $items = $favorites->map(function (Favorite $fav) {
                $product = $fav->product;
                if (! $product) {
                    return null;
                }
                $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

                return [
                    'id' => $fav->id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (string) $product->price,
                        'is_in_stock' => $product->is_in_stock,
                        'primary_image_url' => $primary && $primary->image_path ? Storage::url($primary->image_path) : null,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'name' => $product->category->name,
                            'slug' => $product->category->slug,
                        ] : null,
                    ],
                ];
            })->filter()->values();

            return response()->json([
                'success' => true,
                'data' => ['favorites' => $items],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop favorites index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $product = Product::query()
                ->where('id', $request->validated('product_id'))
                ->where('is_active', true)
                ->first();

            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متوفر',
                ], 422);
            }

            $favorite = Favorite::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $favorite->wasRecentlyCreated ? 'تمت الإضافة للمفضلة' : 'المنتج موجود مسبقاً في المفضلة',
                'data' => [
                    'favorite' => [
                        'id' => $favorite->id,
                        'product_id' => $product->id,
                    ],
                ],
            ], $favorite->wasRecentlyCreated ? 201 : 200);
        } catch (\Exception $e) {
            Log::error('Shop favorite store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إضافة المفضلة',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Favorite $favorite): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($favorite->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'تمت الإزالة من المفضلة',
            ]);
        } catch (\Exception $e) {
            Log::error('Shop favorite destroy: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر حذف العنصر',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
