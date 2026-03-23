<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreCartItemRequest;
use App\Http\Requests\Shop\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $items = Cart::query()
                ->where('user_id', $user->id)
                ->with(['product' => fn ($q) => $q->with(['category:id,name,slug', 'brand:id,name', 'images'])])
                ->orderBy('updated_at', 'desc')
                ->get();

            $lines = $items->map(function (Cart $line) {
                $product = $line->product;
                if (! $product || ! $product->is_active) {
                    return null;
                }
                $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

                return [
                    'id' => $line->id,
                    'quantity' => $line->quantity,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (string) $product->price,
                        'is_in_stock' => $product->is_in_stock,
                        'stock_quantity' => $product->stock_quantity,
                        'primary_image_url' => $primary && $primary->image_path ? Storage::url($primary->image_path) : null,
                    ],
                    'line_total' => round((float) $product->price * $line->quantity, 2),
                ];
            })->filter()->values();

            $subtotal = $lines->sum('line_total');

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $lines,
                    'subtotal' => round((float) $subtotal, 2),
                    'items_count' => $lines->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop cart index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل السلة',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            $product = Product::query()->where('id', $data['product_id'])->where('is_active', true)->first();
            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متوفر',
                ], 422);
            }

            $existing = Cart::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->first();

            $targetQty = $existing ? $existing->quantity + $data['quantity'] : $data['quantity'];

            if (! $product->is_in_stock || $product->stock_quantity < $targetQty) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكمية المطلوبة غير متوفرة في المخزون',
                ], 422);
            }

            $line = Cart::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $targetQty,
                ]
            );

            $line->load(['product.images']);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث السلة',
                'data' => [
                    'cart_item' => [
                        'id' => $line->id,
                        'quantity' => $line->quantity,
                        'product_id' => $product->id,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Shop cart store: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إضافة المنتج للسلة',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function update(UpdateCartItemRequest $request, Cart $cart): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($cart->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $product = Product::query()->where('id', $cart->product_id)->where('is_active', true)->first();
            if (! $product) {
                $cart->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'تمت إزالة منتج غير متوفر من السلة',
                ], 422);
            }

            $quantity = $request->validated('quantity');
            if (! $product->is_in_stock || $product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكمية المطلوبة غير متوفرة في المخزون',
                ], 422);
            }

            $cart->update(['quantity' => $quantity]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الكمية',
                'data' => [
                    'cart_item' => [
                        'id' => $cart->id,
                        'quantity' => $cart->quantity,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop cart update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحديث السلة',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function destroy(Cart $cart): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($cart->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العنصر من السلة',
            ]);
        } catch (\Exception $e) {
            Log::error('Shop cart destroy: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر حذف العنصر',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
