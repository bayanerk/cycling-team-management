<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $fillable = [
        'return_number',
        'order_id',
        'order_item_id',
        'user_id',
        'reason',
        'status',
        'refund_amount',
        'refund_method',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns this return.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item for this return.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the user that owns this return.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this return.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate return number.
     */
    public static function generateReturnNumber(): string
    {
        $date = now()->format('Ymd');
        $time = now()->format('His');
        $random = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return "RET-{$date}-{$time}-{$random}";
    }

    /**
     * Check if return request is within allowed time (3 days from delivery).
     */
    public function isValidReturnTime(): bool
    {
        $order = $this->order;
        
        if (!$order) {
            return false;
        }

        // إذا كان الاسترجاع لمنتج معين
        if ($this->order_item_id) {
            $item = $this->orderItem;
            return $item && $item->canBeReturned();
        }

        // إذا كان الاسترجاع للطلب كاملاً
        return $order->canBeReturned();
    }

    /**
     * Check if this return is for a specific item or entire order.
     */
    public function isItemReturn(): bool
    {
        return $this->order_item_id !== null;
    }

    /**
     * Check if this return is for entire order.
     */
    public function isOrderReturn(): bool
    {
        return $this->order_item_id === null;
    }
}
