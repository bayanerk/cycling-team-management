<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ValidateCouponRequest;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        try {
            $code = $request->validated('code');
            $subtotalInput = $request->validated('subtotal');

            $coupon = Coupon::query()->where('code', $code)->first();
            if (! $coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'كود الخصم غير صالح',
                ], 422);
            }

            if (! $coupon->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الكوبون غير فعّال أو منتهي',
                ], 422);
            }

            $user = Auth::user();
            if ($coupon->user_limit !== null && $user) {
                $used = Order::query()
                    ->where('user_id', $user->id)
                    ->where('coupon_id', $coupon->id)
                    ->count();
                if ($used >= $coupon->user_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لقد استخدمت هذا الكوبون الحد الأقصى من المرات',
                    ], 422);
                }
            }

            $subtotal = $subtotalInput !== null ? (float) $subtotalInput : 0.0;
            if ($coupon->minimum_amount && $subtotal < (float) $coupon->minimum_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'الحد الأدنى للطلب لا يحقق شروط الكوبون',
                    'data' => [
                        'minimum_amount' => (string) $coupon->minimum_amount,
                    ],
                ], 422);
            }

            $discount = $coupon->calculateDiscount($subtotal);
            $totalAfter = round(max(0, $subtotal - $discount), 2);

            return response()->json([
                'success' => true,
                'message' => 'كوبون صالح',
                'data' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'discount_amount' => $discount,
                    'subtotal' => $subtotal,
                    'total_after_discount' => $totalAfter,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Shop coupon validate: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر التحقق من الكوبون',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
