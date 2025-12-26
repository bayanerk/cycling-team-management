# إعداد Laravel Sanctum للـ Token Authentication

## الخطوة 1: تثبيت Sanctum

قم بتشغيل الأمر التالي في Terminal:

```bash
composer require laravel/sanctum
```

أو إذا كنت تستخدم Laragon:

```bash
cd C:\laragon\www\Mishwar-Bicklate
composer require laravel/sanctum
```

## الخطوة 2: نشر ملفات Sanctum

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## الخطوة 3: تشغيل Migration

```bash
php artisan migrate
```

## الخطوة 4: تحديث User Model

أضف `HasApiTokens` trait إلى User model:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

## الخطوة 5: تحديث AuthController

في دالة `login`، أضف:

```php
$token = $user->createToken('auth-token')->plainTextToken;

return response()->json([
    'success' => true,
    'message' => 'تم تسجيل الدخول بنجاح',
    'data' => [
        'user' => [...],
        'token' => $token,  // ← أضف هذا
    ]
]);
```

## الخطوة 6: تحديث Routes

غيّر middleware من `auth:web` إلى `auth:sanctum`:

```php
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    // Protected routes
});
```

## الخطوة 7: استخدام Token في Postman

في Postman، أضف Header:

```
Authorization: Bearer {token}
```

---

**بعد إكمال هذه الخطوات، سيعمل النظام مع Token-based authentication! 🚀**

