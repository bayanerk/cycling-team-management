<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * طلب حجز رايد جماعي — لتسهيل التنسيق فقط، ولا يُربط بجدول rides ولا يُحسب ضمن خدمات الرايدات الرسمية.
 *
 * ملاحظة: أقل عدد مقبول لإنشاء/قبول الطلب من ناحية حجم المجموعة هو 10 أشخاص؛ الحد الأقصى 20.
 */
class GroupRideRequest extends Model
{
    public const MIN_PEOPLE = 10;

    public const MAX_PEOPLE = 20;

    protected $fillable = [
        'user_id',
        'title',
        'group_name',
        'group_type',
        'location',
        'scheduled_at',
        'people_count',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * اعتماد الطلب من لوحة الإدارة أو الـ API (نفس منطق الحالة).
     */
    public function approveBy(User $admin): void
    {
        if ($this->status !== 'pending') {
            throw new InvalidArgumentException('لا يمكن اعتماد طلب تمت معالجته مسبقاً.');
        }

        $this->forceFill([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => null,
        ])->save();
    }

    /**
     * رفض الطلب من لوحة الإدارة أو الـ API.
     */
    public function rejectBy(User $admin, ?string $notes = null): void
    {
        if ($this->status !== 'pending') {
            throw new InvalidArgumentException('لا يمكن رفض طلب تمت معالجته مسبقاً.');
        }

        $this->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ])->save();
    }
}
