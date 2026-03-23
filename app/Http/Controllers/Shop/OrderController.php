<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CancelOrderRequest;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = min((int) $request->query('per_page', 15), 50);

            $paginator = Order::query()
                ->where('user_id', $user->id)
                ->with(['items', 'coupon:id,code,name', 'payments'])
                ->orderByDesc('created_at')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $paginator->getCollection()->map(fn (Order $o) => $this->orderSummary($o))->values(),
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop orders index: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل الطلبات',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($order->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $order->load(['items.product', 'coupon', 'payments']);

            return response()->json([
                'success' => true,
                'data' => ['order' => $this->orderDetail($order)],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop order show: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر تحميل الطلب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function store(CheckoutRequest $request, CheckoutService $checkoutService): JsonResponse
    {
        try {
            $order = $checkoutService->placeOrder($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => ['order' => $this->orderDetail($order)],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Shop checkout: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إتمام الطلب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($order->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }

            $reason = $request->validated('cancellation_reason');
            if (! $order->cancelOrder($user->id, $reason)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء هذا الطلب في الوقت الحالي',
                ], 422);
            }

            $order->refresh()->load(['items', 'coupon', 'payments']);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الطلب',
                'data' => ['order' => $this->orderDetail($order)],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop order cancel: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر إلغاء الطلب',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function orderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'subtotal' => (string) $order->subtotal,
            'discount' => (string) $order->discount,
            'total' => (string) $order->total,
            'created_at' => $order->created_at,
            'items_count' => $order->items->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderDetail(Order $order): array
    {
        $base = $this->orderSummary($order);

        return array_merge($base, [
            'shipping_address' => $order->shipping_address,
            'address_id' => $order->address_id,
            'notes' => $order->notes,
            'coupon' => $order->coupon ? [
                'id' => $order->coupon->id,
                'code' => $order->coupon->code,
                'name' => $order->coupon->name,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_price' => (string) $item->product_price,
                'quantity' => $item->quantity,
                'subtotal' => (string) $item->subtotal,
                'status' => $item->status,
            ])->values()->all(),
            'payments' => $order->payments->map(fn ($p) => [
                'id' => $p->id,
                'payment_number' => $p->payment_number,
                'method' => $p->method,
                'amount' => (string) $p->amount,
                'status' => $p->status,
            ])->values()->all(),
            'can_cancel' => $order->canBeCancelled(),
            'remaining_cancellation_minutes' => $order->getRemainingCancellationMinutes(),
        ]);
    }
}
