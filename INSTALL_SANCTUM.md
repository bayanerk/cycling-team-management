# تثبيت Laravel Sanctum - خطوات سريعة

## ⚠️ مهم جداً: يجب تثبيت Sanctum أولاً!

الكود تم تحديثه لاستخدام Sanctum، لكن يجب تثبيت الحزمة أولاً.

## الخطوات:

### 1. افتح Terminal في مجلد المشروع:
```bash
cd C:\laragon\www\Mishwar-Bicklate
```

### 2. تثبيت Sanctum:
```bash
composer require laravel/sanctum
```

### 3. نشر ملفات Sanctum:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 4. تشغيل Migration:
```bash
php artisan migrate
```

### 5. مسح Cache:
```bash
php artisan config:clear
php artisan cache:clear
```

## بعد التثبيت:

✅ سيعمل تسجيل الدخول ويعيد token  
✅ يمكنك استخدام token في Postman  
✅ جميع الـ API routes المحمية ستعمل مع token

## استخدام Token في Postman:

بعد تسجيل الدخول، ستحصل على:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

استخدم Token في Header:
```
Authorization: Bearer {token}
```

---

**بعد تثبيت Sanctum، كل شيء سيعمل! 🚀**

