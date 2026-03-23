<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Create an order from the user's cart, apply coupon, decrement stock, clear cart.
     *
     * @param  array{address_id?: int|null, payment_method: string, coupon_code?: string|null, notes?: string|null}  $data
     */
    public function placeOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cartItems = Cart::query()
                ->where('user_id', $user->id)
                ->with('product')
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \InvalidArgumentException('سلة التسوق فارغة.');
            }

            $shippingAddress = $this->resolveShippingAddress($user, $data['address_id'] ?? null);

            $subtotal = 0;
            foreach ($cartItems as $line) {
                $product = $line->product;
                if (! $product || ! $product->is_active) {
                    throw new \InvalidArgumentException("المنتج رقم {$line->product_id} لم يعد متوفراً.");
                }
                if (! $product->is_in_stock || $product->stock_quantity < $line->quantity) {
                    throw new \InvalidArgumentException("المخزون غير كافٍ لـ «{$product->name}».");
                }
                $subtotal += (float) $product->price * $line->quantity;
            }

            $coupon = null;
            $discount = 0.0;
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::query()->where('code', $data['coupon_code'])->first();
                if (! $coupon) {
                    throw new \InvalidArgumentException('كود الخصم غير صالح.');
                }
                if (! $coupon->isValid()) {
                    throw new \InvalidArgumentException('هذا الكوبون غير فعّال أو منتهي.');
                }
                if ($coupon->minimum_amount && $subtotal < (float) $coupon->minimum_amount) {
                    throw new \InvalidArgumentException('مجموع الطلب لا يحقق الحد الأدنى لهذا الكوبون.');
                }
                if ($coupon->user_limit !== null) {
                    $used = Order::query()
                        ->where('user_id', $user->id)
                        ->where('coupon_id', $coupon->id)
                        ->count();
                    if ($used >= $coupon->user_limit) {
                        throw new \InvalidArgumentException('لقد استخدمت هذا الكوبون الحد الأقصى من المرات.');
                    }
                }
                $discount = $coupon->calculateDiscount($subtotal);
            }

            $total = round(max(0, $subtotal - $discount), 2);

            $order = Order::query()->create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'address_id' => $data['address_id'] ?? null,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'shipping_address' => $shippingAddress,
                'notes' => $data['notes'] ?? null,
                'coupon_id' => $coupon?->id,
            ]);

            foreach ($cartItems as $line) {
                $product = $line->product;
                $lineSubtotal = round((float) $product->price * $line->quantity, 2);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $line->quantity,
                    'subtotal' => $lineSubtotal,
                    'status' => 'active',
                ]);

                $product->decrement('stock_quantity', $line->quantity);
                if ($product->fresh()->stock_quantity <= 0) {
                    $product->update(['is_in_stock' => false]);
                }
            }

            if ($coupon) {
                $coupon->increment('usage_count');
            }

            Payment::query()->create([
                'payment_number' => Payment::generatePaymentNumber(),
                'order_id' => $order->id,
                'user_id' => $user->id,
                'method' => $data['payment_method'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            Cart::query()->where('user_id', $user->id)->delete();

            return $order->load(['items', 'coupon', 'payments']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveShippingAddress(User $user, ?int $addressId): array
    {
        if ($addressId) {
            $address = Address::query()
                ->where('id', $addressId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            return [
                'city' => $address->city,
                'district' => $address->district,
                'street' => $address->street,
            ];
        }

        throw new \InvalidArgumentException('يجب اختيار عنوان الشحن.');
    }
}
