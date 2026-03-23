# دليل إلغاء واسترجاع الطلبات

## 📋 نظرة عامة

النظام يدعم:
1. **إلغاء الطلب الكامل** - خلال ساعة من التسجيل
2. **إلغاء منتج معين** - خلال ساعة من التسجيل
3. **استرجاع الطلب الكامل** - خلال 3 أيام من الاستلام
4. **استرجاع منتج معين** - خلال 3 أيام من الاستلام

---

## ⏰ القواعد الزمنية

### الإلغاء (Cancellation):
- **الحد الزمني**: ساعة واحدة من وقت إنشاء الطلب (`created_at`)
- **الحالات المسموحة**: `pending`, `confirmed`, `processing`
- **النتيجة**: إعادة المخزون تلقائياً + تحديث `total` للطلب

### الاسترجاع (Return):
- **الحد الزمني**: 3 أيام من وقت الاستلام (`delivered_at`)
- **الحالة المطلوبة**: `delivered`
- **النتيجة**: إعادة المخزون تلقائياً + إنشاء سجل استرجاع

---

## 💻 أمثلة الاستخدام

### 1. إلغاء الطلب الكامل

```php
use App\Models\Order;

$order = Order::find($orderId);

// التحقق من إمكانية الإلغاء
if (!$order->canBeCancelled()) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكن إلغاء الطلب. انتهت المدة المسموحة (ساعة واحدة من التسجيل)',
        'remaining_minutes' => $order->getRemainingCancellationMinutes(),
    ], 400);
}

// إلغاء الطلب
if ($order->cancelOrder($userId, 'سبب الإلغاء')) {
    return response()->json([
        'success' => true,
        'message' => 'تم إلغاء الطلب بنجاح',
    ]);
}
```

### 2. إلغاء منتج معين من الطلب

```php
use App\Models\Order;

$order = Order::find($orderId);
$orderItemId = 5; // ID المنتج المراد إلغاؤه

// التحقق من وجود المنتج في الطلب
$item = $order->items()->find($orderItemId);
if (!$item) {
    return response()->json([
        'success' => false,
        'message' => 'المنتج غير موجود في الطلب',
    ], 404);
}

// التحقق من إمكانية الإلغاء
if (!$item->canBeCancelled()) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكن إلغاء هذا المنتج. انتهت المدة المسموحة',
    ], 400);
}

// إلغاء المنتج
if ($order->cancelOrderItem($orderItemId, 'سبب الإلغاء')) {
    return response()->json([
        'success' => true,
        'message' => 'تم إلغاء المنتج بنجاح',
        'remaining_items' => $order->getActiveItemsCount(),
    ]);
}
```

### 3. استرجاع منتج معين من الطلب

```php
use App\Models\Order;

$order = Order::find($orderId);
$orderItemId = 5; // ID المنتج المراد استرجاعه

// التحقق من وجود المنتج في الطلب
$item = $order->items()->find($orderItemId);
if (!$item) {
    return response()->json([
        'success' => false,
        'message' => 'المنتج غير موجود في الطلب',
    ], 404);
}

// التحقق من إمكانية الاسترجاع
if (!$item->canBeReturned()) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكن استرجاع هذا المنتج. انتهت المدة المسموحة (3 أيام من الاستلام)',
        'remaining_days' => $order->getRemainingReturnDays(),
    ], 400);
}

// استرجاع المنتج
if ($order->returnOrderItem($orderItemId, 'سبب الاسترجاع')) {
    return response()->json([
        'success' => true,
        'message' => 'تم تقديم طلب الاسترجاع بنجاح',
        'return_number' => $order->returns()->latest()->first()->return_number,
    ]);
}
```

### 4. استرجاع الطلب الكامل

```php
use App\Models\Order;

$order = Order::find($orderId);

// التحقق من إمكانية الاسترجاع
if (!$order->canBeReturned()) {
    return response()->json([
        'success' => false,
        'message' => 'لا يمكن استرجاع الطلب. انتهت المدة المسموحة (3 أيام من الاستلام)',
        'remaining_days' => $order->getRemainingReturnDays(),
    ], 400);
}

// استرجاع الطلب
if ($order->returnOrder('سبب الاسترجاع')) {
    return response()->json([
        'success' => true,
        'message' => 'تم تقديم طلب الاسترجاع بنجاح',
        'return_number' => $order->returns()->latest()->first()->return_number,
    ]);
}
```

---

## 🔍 Methods المتاحة

### Order Model:

#### `canBeCancelled(): bool`
- يتحقق من إمكانية إلغاء الطلب الكامل (خلال ساعة)

#### `getRemainingCancellationMinutes(): ?int`
- يرجع الدقائق المتبقية للإلغاء

#### `cancelOrder(?int $cancelledBy = null, ?string $reason = null): bool`
- يلغي الطلب الكامل + يعيد المخزون

#### `cancelOrderItem(int $orderItemId, ?string $reason = null): bool`
- يلغي منتج معين + يعيد المخزون + يعيد حساب total

#### `canBeReturned(): bool`
- يتحقق من إمكانية استرجاع الطلب الكامل (خلال 3 أيام)

#### `getRemainingReturnDays(): ?int`
- يرجع الأيام المتبقية للاسترجاع

#### `returnOrder(string $reason): bool`
- يسترجع الطلب الكامل + يعيد المخزون + ينشئ OrderReturn

#### `returnOrderItem(int $orderItemId, string $reason): bool`
- يسترجع منتج معين + يعيد المخزون + ينشئ OrderReturn

#### `recalculateTotal(): void`
- يعيد حساب total للطلب بناءً على العناصر النشطة فقط

#### `getActiveItemsCount(): int`
- يرجع عدد العناصر النشطة

#### `getCancelledItemsCount(): int`
- يرجع عدد العناصر الملغاة

#### `getReturnedItemsCount(): int`
- يرجع عدد العناصر المسترجعة

---

### OrderItem Model:

#### `canBeCancelled(): bool`
- يتحقق من إمكانية إلغاء المنتج (خلال ساعة)

#### `canBeReturned(): bool`
- يتحقق من إمكانية استرجاع المنتج (خلال 3 أيام)

#### `cancel(): bool`
- يلغي المنتج (يحدث status إلى `cancelled`)

#### `markAsReturned(): bool`
- يسترجع المنتج (يحدث status إلى `returned`)

---

### OrderReturn Model:

#### `isValidReturnTime(): bool`
- يتحقق من صحة وقت الاسترجاع

#### `isItemReturn(): bool`
- يتحقق إذا كان الاسترجاع لمنتج معين

#### `isOrderReturn(): bool`
- يتحقق إذا كان الاسترجاع للطلب كاملاً

---

## 📊 حالات OrderItem (status)

- **`active`**: المنتج نشط (افتراضي)
- **`cancelled`**: تم إلغاء المنتج
- **`returned`**: تم استرجاع المنتج

---

## ⚠️ ملاحظات مهمة

1. **إعادة المخزون**: عند الإلغاء أو الاسترجاع، يتم إعادة المخزون تلقائياً
2. **إعادة حساب Total**: عند إلغاء منتج معين، يتم إعادة حساب `total` للطلب تلقائياً
3. **تحديث حالة الطلب**: إذا تم إلغاء/استرجاع جميع المنتجات، يتم تحديث `status` للطلب تلقائياً
4. **OrderReturn**: عند الاسترجاع، يتم إنشاء سجل في `order_returns` تلقائياً

---

## 🎯 سيناريوهات الاستخدام

### سيناريو 1: طلب يحتوي على 3 منتجات
- المستخدم يلغي منتج واحد → يبقى الطلب نشط مع منتجين
- المستخدم يسترجع منتج آخر → يبقى الطلب نشط مع منتج واحد
- المستخدم يلغي آخر منتج → يتم إلغاء الطلب كاملاً

### سيناريو 2: طلب مستلم منذ يومين
- المستخدم يسترجع منتج معين → ✅ مسموح (لم تمر 3 أيام)
- بعد يومين إضافيين → ❌ لا يمكن الاسترجاع (مرت 3 أيام)

### سيناريو 3: طلب تم إنشاؤه منذ 30 دقيقة
- المستخدم يلغي منتج معين → ✅ مسموح (لم تمر ساعة)
- بعد 40 دقيقة إضافية → ❌ لا يمكن الإلغاء (مرت ساعة)

---

## 📝 مثال كامل في Controller

```php
public function cancelOrderItem(Request $request, Order $order, OrderItem $orderItem)
{
    // التحقق من أن المنتج يتبع هذا الطلب
    if ($orderItem->order_id !== $order->id) {
        return response()->json(['success' => false, 'message' => 'المنتج غير موجود في هذا الطلب'], 404);
    }

    // التحقق من الصلاحيات
    if ($order->user_id !== auth()->id()) {
        return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
    }

    // التحقق من إمكانية الإلغاء
    if (!$orderItem->canBeCancelled()) {
        $remainingMinutes = $order->getRemainingCancellationMinutes();
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن إلغاء هذا المنتج. انتهت المدة المسموحة',
            'remaining_minutes' => $remainingMinutes,
        ], 400);
    }

    // إلغاء المنتج
    $reason = $request->input('reason', 'طلب المستخدم');
    if ($order->cancelOrderItem($orderItem->id, $reason)) {
        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء المنتج بنجاح',
            'order' => $order->fresh()->load('items'),
        ]);
    }

    return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الإلغاء'], 500);
}
```

---

**تاريخ الإنشاء**: 11 مارس 2026

