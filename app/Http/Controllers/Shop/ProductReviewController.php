<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreProductReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductReviewController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        try {
            if (! $product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متوفر',
                ], 404);
            }

            $perPage = min((int) $request->query('per_page', 15), 50);

            $paginator = ProductReview::query()
                ->where('product_id', $product->id)
                ->where('is_approved', true)
                ->where('is_visible', true)
                ->with(['user:id,name'])
                ->orderByDesc('created_at')
                ->paginate($perPage);

            $reviews = $paginator->getCollection()->map(fn (ProductReview $r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'review' => $r->review,
                'created_at' => $r->created_at,
                'user' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                ] : null,
            ])->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => $reviews,
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop product reviews index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل التقييمات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(StoreProductReviewRequest $request, Product $product): JsonResponse
    {
        try {
            if (! $product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متوفر',
                ], 404);
            }

            $user = Auth::user();

            if (ProductReview::query()->where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لقد قيّمت هذا المنتج مسبقاً',
                ], 422);
            }

            $orderId = $request->validated('order_id');
            if ($orderId !== null) {
                $order = Order::query()
                    ->where('id', $orderId)
                    ->where('user_id', $user->id)
                    ->first();

                if (! $order || $order->status !== 'delivered') {
                    return response()->json([
                        'success' => false,
                        'message' => 'طلب غير صالح لربط التقييم',
                    ], 422);
                }

                $hasProduct = $order->items()->where('product_id', $product->id)->exists();
                if (! $hasProduct) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هذا الطلب لا يتضمن هذا المنتج',
                    ], 422);
                }
            }

            $review = ProductReview::query()->create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'order_id' => $orderId,
                'rating' => $request->validated('rating'),
                'review' => $request->validated('review'),
                'is_approved' => false,
                'is_visible' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التقييم وهو قيد المراجعة',
                'data' => [
                    'review' => [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'is_approved' => $review->is_approved,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Shop product review store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال التقييم',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
