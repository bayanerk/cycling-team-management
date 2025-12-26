# إعداد Postman لاختبار API

## ملاحظات مهمة

⚠️ **المشكلة الحالية**: API يستخدم Session-based authentication، وهذا لا يعمل جيداً مع Postman بدون إعداد إضافي.

## الحلول الممكنة:

### الحل 1: استخدام Postman مع Cookies (موصى به للاختبار السريع)

1. افتح Postman
2. أنشئ Request جديد:
   - Method: `POST`
   - URL: `http://localhost:8000/api/auth/login`
   - Headers:
     ```
     Accept: application/json
     Content-Type: application/json
     ```
   - Body (raw JSON):
     ```json
     {
       "email": "test@example.com",
       "password": "password123"
     }
     ```
3. بعد تسجيل الدخول، سيتم حفظ Cookie تلقائياً في Postman
4. للـ requests المحمية (مثل `/api/auth/me`):
   - استخدم نفس الـ Cookie من الـ login
   - أو استخدم Cookie Manager في Postman

### الحل 2: استخدام Laravel Sanctum (موصى به للإنتاج)

للحصول على token-based authentication:

1. تثبيت Sanctum:
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

2. إضافة `HasApiTokens` trait إلى User model

3. تعديل AuthController لإرجاع token بدلاً من session

## خطوات الاختبار السريع:

### 1. تسجيل مستخدم جديد
```
POST http://localhost:8000/api/auth/register
Content-Type: application/json
Accept: application/json

{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "phone": "+966501234567",
  "password": "password123",
  "password_confirmation": "password123",
  "gender": "male",
  "age": 25,
  "role": "rider",
  "language": "ar"
}
```

### 2. تسجيل الدخول
```
POST http://localhost:8000/api/auth/login
Content-Type: application/json
Accept: application/json

{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

### 3. إرسال OTP
```
POST http://localhost:8000/api/auth/otp/send
Content-Type: application/json
Accept: application/json

{
  "identifier": "ahmed@example.com",
  "type": "email"
}
```

### 4. التحقق من OTP
```
POST http://localhost:8000/api/auth/otp/verify
Content-Type: application/json
Accept: application/json

{
  "identifier": "ahmed@example.com",
  "type": "email",
  "otp_code": "123456"
}
```

### 5. الحصول على بيانات المستخدم (يحتاج تسجيل دخول)
```
GET http://localhost:8000/api/auth/me
Accept: application/json
Cookie: laravel_session=... (من الـ login response)
```

## إعدادات Postman الموصى بها:

1. **Enable Cookie Management**:
   - File → Settings → General
   - تأكد من تفعيل "Automatically follow redirects"
   - تأكد من تفعيل "Send cookies"

2. **إضافة Environment Variables**:
   - Base URL: `http://localhost:8000`
   - API Prefix: `/api`

3. **استخدام Collection Variables**:
   - `{{base_url}}/api/auth/login`
   - `{{base_url}}/api/auth/register`

## ملاحظات:

- تأكد من تشغيل Laravel server: `php artisan serve`
- تأكد من تشغيل migrations: `php artisan migrate`
- في وضع التطوير، OTP code سيظهر في Laravel logs
- للتحقق من OTP، افحص ملف `storage/logs/laravel.log`

