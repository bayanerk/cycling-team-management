<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',
        'quantity',
        'subtotal',
        'status',
        'cancelled_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'product_price' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
            'cancelled_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns this item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all returns for this order item.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /**
     * Check if order item can be cancelled (within 1 hour of order creation).
     */
    public function canBeCancelled(): bool
    {
        // يجب أن يكون العنصر نشط (لم يتم إلغاؤه أو استرجاعه)
        if ($this->status !== 'active') {
            return false;
        }

        // يجب أن يكون الطلب موجود
        if (!$this->order) {
            return false;
        }

        // التحقق من أن ساعة واحدة لم تمر بعد إنشاء الطلب
        $oneHourAfterOrder = $this->order->created_at->copy()->addHour();
        return now()->isBefore($oneHourAfterOrder);
    }

    /**
     * Check if order item can be returned (within 3 days of order delivery).
     */
    public function canBeReturned(): bool
    {
        // يجب أن يكون العنصر نشط (لم يتم إلغاؤه أو استرجاعه)
        if ($this->status !== 'active') {
            return false;
        }

        // يجب أن يكون الطلب موجود ومستلم
        if (!$this->order || $this->order->status !== 'delivered') {
            return false;
        }

        // يجب أن يكون هناك delivered_at
        if (!$this->order->delivered_at) {
            return false;
        }

        // التحقق من أن 3 أيام لم تمر بعد الاستلام
        $threeDaysAfterDelivery = $this->order->delivered_at->copy()->addDays(3);
        return now()->isBefore($threeDaysAfterDelivery);
    }

    /**
     * Cancel this order item.
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return true;
    }

    /**
     * Return this order item.
     */
    public function markAsReturned(): bool
    {
        if (!$this->canBeReturned()) {
            return false;
        }

        $this->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        return true;
    }
}
