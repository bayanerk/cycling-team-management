<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'status',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'shipping_address',
        'notes',
        'coupon_id',
        'delivered_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'shipping_address' => 'array',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address for this order.
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get the coupon used in this order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who cancelled this order.
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get all items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all payments for this order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all returns for this order.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Generate order number.
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $time = now()->format('His');
        $random = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return "ORD-{$date}-{$time}-{$random}";
    }

    /**
     * Check if order can be returned (within 3 days of delivery).
     */
    public function canBeReturned(): bool
    {
        // يجب أن يكون الطلب مستلم (delivered)
        if ($this->status !== 'delivered') {
            return false;
        }

        // يجب أن يكون هناك delivered_at
        if (!$this->delivered_at) {
            return false;
        }

        // التحقق من أن 3 أيام لم تمر بعد الاستلام
        $threeDaysAfterDelivery = $this->delivered_at->copy()->addDays(3);
        return now()->isBefore($threeDaysAfterDelivery);
    }

    /**
     * Get remaining days for return.
     */
    public function getRemainingReturnDays(): ?int
    {
        if (!$this->canBeReturned()) {
            return null;
        }

        $threeDaysAfterDelivery = $this->delivered_at->copy()->addDays(3);
        $remainingDays = now()->diffInDays($threeDaysAfterDelivery, false);
        
        return $remainingDays > 0 ? $remainingDays : 0;
    }

    /**
     * Check if order can be cancelled (within 1 hour of creation).
     */
    public function canBeCancelled(): bool
    {
        // يجب أن يكون الطلب في حالة يمكن إلغاؤها
        if (!in_array($this->status, ['pending', 'confirmed', 'processing'])) {
            return false;
        }

        // التحقق من أن ساعة واحدة لم تمر بعد إنشاء الطلب
        $oneHourAfterOrder = $this->created_at->copy()->addHour();
        return now()->isBefore($oneHourAfterOrder);
    }

    /**
     * Get remaining minutes for cancellation.
     */
    public function getRemainingCancellationMinutes(): ?int
    {
        if (!$this->canBeCancelled()) {
            return null;
        }

        $oneHourAfterOrder = $this->created_at->copy()->addHour();
        $remainingMinutes = now()->diffInMinutes($oneHourAfterOrder, false);
        
        return $remainingMinutes > 0 ? $remainingMinutes : 0;
    }

    /**
     * Cancel the entire order.
     */
    public function cancelOrder(?int $cancelledBy = null, ?string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // إلغاء جميع العناصر النشطة
        $this->items()->where('status', 'active')->each(function ($item) {
            $item->cancel();
        });

        // تحديث حالة الطلب
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        // إعادة المخزون للمنتجات
        $this->items()->where('status', 'cancelled')->each(function ($item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
                if ($item->product->stock_quantity > 0) {
                    $item->product->update(['is_in_stock' => true]);
                }
            }
        });

        return true;
    }

    /**
     * Cancel a specific order item.
     */
    public function cancelOrderItem(int $orderItemId, ?string $reason = null): bool
    {
        $item = $this->items()->find($orderItemId);
        
        if (!$item) {
            return false;
        }

        if (!$item->canBeCancelled()) {
            return false;
        }

        // إلغاء العنصر
        $item->cancel();

        // إعادة المخزون
        if ($item->product) {
            $item->product->increment('stock_quantity', $item->quantity);
            if ($item->product->stock_quantity > 0) {
                $item->product->update(['is_in_stock' => true]);
            }
        }

        // إذا تم إلغاء جميع العناصر، إلغاء الطلب كاملاً
        $activeItemsCount = $this->items()->where('status', 'active')->count();
        if ($activeItemsCount === 0) {
            $this->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason ?? 'تم إلغاء جميع المنتجات',
            ]);
        } else {
            // إعادة حساب total للطلب
            $this->recalculateTotal();
        }

        return true;
    }

    /**
     * Return a specific order item.
     */
    public function returnOrderItem(int $orderItemId, string $reason): bool
    {
        $item = $this->items()->find($orderItemId);
        
        if (!$item) {
            return false;
        }

        if (!$item->canBeReturned()) {
            return false;
        }

        // استرجاع العنصر
        $item->markAsReturned();

        // إعادة المخزون
        if ($item->product) {
            $item->product->increment('stock_quantity', $item->quantity);
            if ($item->product->stock_quantity > 0) {
                $item->product->update(['is_in_stock' => true]);
            }
        }

        // إنشاء سجل استرجاع
        OrderReturn::create([
            'return_number' => OrderReturn::generateReturnNumber(),
            'order_id' => $this->id,
            'order_item_id' => $item->id,
            'user_id' => $this->user_id,
            'reason' => $reason,
            'status' => 'pending',
            'refund_amount' => $item->subtotal,
        ]);

        // إذا تم استرجاع جميع العناصر، تحديث حالة الطلب
        $activeItemsCount = $this->items()->where('status', 'active')->count();
        if ($activeItemsCount === 0) {
            $this->update(['status' => 'returned']);
        }

        return true;
    }

    /**
     * Return the entire order.
     */
    public function returnOrder(string $reason): bool
    {
        if (!$this->canBeReturned()) {
            return false;
        }

        $allItemsReturned = true;
        $totalRefundAmount = 0;

        // استرجاع جميع العناصر النشطة
        foreach ($this->items()->where('status', 'active')->get() as $item) {
            if ($item->canBeReturned()) {
                $item->markAsReturned();
                
                // إعادة المخزون
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                    if ($item->product->stock_quantity > 0) {
                        $item->product->update(['is_in_stock' => true]);
                    }
                }

                $totalRefundAmount += $item->subtotal;
            } else {
                $allItemsReturned = false;
            }
        }

        if ($allItemsReturned) {
            // إنشاء سجل استرجاع للطلب كاملاً
            OrderReturn::create([
                'return_number' => OrderReturn::generateReturnNumber(),
                'order_id' => $this->id,
                'order_item_id' => null, // null يعني استرجاع الطلب كاملاً
                'user_id' => $this->user_id,
                'reason' => $reason,
                'status' => 'pending',
                'refund_amount' => $totalRefundAmount,
            ]);

            $this->update(['status' => 'returned']);
        }

        return $allItemsReturned;
    }

    /**
     * Recalculate order total based on active items.
     */
    public function recalculateTotal(): void
    {
        $activeItemsSubtotal = $this->items()
            ->where('status', 'active')
            ->sum('subtotal');

        $this->update([
            'subtotal' => $activeItemsSubtotal,
            'total' => $activeItemsSubtotal - $this->discount,
        ]);
    }

    /**
     * Get active items count.
     */
    public function getActiveItemsCount(): int
    {
        return $this->items()->where('status', 'active')->count();
    }

    /**
     * Get cancelled items count.
     */
    public function getCancelledItemsCount(): int
    {
        return $this->items()->where('status', 'cancelled')->count();
    }

    /**
     * Get returned items count.
     */
    public function getReturnedItemsCount(): int
    {
        return $this->items()->where('status', 'returned')->count();
    }
}
