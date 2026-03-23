<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'order_id',
        'user_id',
        'method',
        'amount',
        'status',
        'transaction_id',
        'payment_details',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_details' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the order that owns this payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate payment number.
     */
    public static function generatePaymentNumber(): string
    {
        $date = now()->format('Ymd');
        $time = now()->format('His');
        $random = str_pad((string) rand(0, 9999), 4, '0', STR_PAD_LEFT);
        return "PAY-{$date}-{$time}-{$random}";
    }
}
