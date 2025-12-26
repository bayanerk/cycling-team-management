# تثبيت وإعداد Laravel Passport - خطوات كاملة

## ✅ ما تم إنجازه:

1. ✅ تحديث User Model لاستخدام `HasApiTokens` من Passport
2. ✅ تحديث AuthController لإرجاع token من Passport
3. ✅ تحديث Routes لاستخدام `auth:api`
4. ✅ تحديث config/auth.php لإضافة API guard
5. ✅ تحديث AppServiceProvider لدعم Passport

## 📋 الخطوات المتبقية (يجب تنفيذها):

### 1. تثبيت Laravel Passport:

افتح Terminal في مجلد المشروع وشغّل:

```bash
cd C:\laragon\www\Mishwar-Bicklate
composer require laravel/passport
```

### 2. نشر ملفات Passport:

```bash
php artisan vendor:publish --tag=passport-migrations
```

### 3. تشغيل Migrations:

```bash
php artisan migrate
```

### 4. إنشاء Encryption Keys:

```bash
php artisan passport:install
```

هذا الأمر سينشئ:
- Client IDs و Secrets
- Encryption keys

### 5. مسح Cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 🎯 بعد التثبيت:

### تسجيل الدخول سيعيد:

```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."  ← Token من Passport
  }
}
```

### استخدام Token في Postman:

أضف Header:
```
Authorization: Bearer {token}
```

## 📝 ملاحظات مهمة:

- ✅ Passport يوفر OAuth2 server كامل
- ✅ أكثر أماناً من Sanctum للـ APIs الكبيرة
- ✅ يدعم Scopes و Permissions
- ✅ مناسب للإنتاج

## 🔧 في حالة وجود مشاكل:

إذا واجهت خطأ في `passport:install`، جرب:

```bash
php artisan passport:keys
```

---

**بعد تنفيذ هذه الخطوات، سيعمل النظام مع Passport! 🚀**

