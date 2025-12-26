# 🧪 اختبار Passport - خطوات واضحة

## ✅ ما تم إنجازه:

1. ✅ تم تفعيل Passport في User Model
2. ✅ تم تحديث AuthController لإرجاع token
3. ✅ تم تحديث Routes لاستخدام `auth:api`
4. ✅ تم مسح Cache

## 🚀 خطوات الاختبار:

### 1. تأكد من تشغيل Laravel Server:

```bash
php artisan serve
```

يجب أن يظهر:
```
Starting Laravel development server: http://127.0.0.1:8000
```

---

### 2. اختبار تسجيل الدخول في Postman:

#### Request:
- **Method:** `POST`
- **URL:** `http://localhost:8000/api/auth/login`
- **Headers:**
  ```
  Accept: application/json
  Content-Type: application/json
  ```
- **Body (raw JSON):**
  ```json
  {
    "email": "ahmed@example.com",
    "password": "password123"
  }
  ```

#### Response المتوقع (نجاح):
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "phone": "+966501234567",
      "role": "rider",
      "language": "ar",
      "email_verified": false,
      "phone_verified": false
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."  ← هذا ما تحتاجه!
  }
}
```

---

### 3. اختبار Route محمي (يحتاج Token):

#### Request:
- **Method:** `GET`
- **URL:** `http://localhost:8000/api/auth/me`
- **Headers:**
  ```
  Accept: application/json
  Authorization: Bearer {token}
  ```
  (ضع الـ token الذي حصلت عليه من تسجيل الدخول)

#### Response المتوقع:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      ...
    }
  }
}
```

---

### 4. اختبار تسجيل الخروج:

#### Request:
- **Method:** `POST`
- **URL:** `http://localhost:8000/api/auth/logout`
- **Headers:**
  ```
  Accept: application/json
  Authorization: Bearer {token}
  ```

#### Response المتوقع:
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح"
}
```

---

## 🔍 استكشاف الأخطاء:

### خطأ: "Unauthenticated"
- تأكد من إضافة Header: `Authorization: Bearer {token}`
- تأكد من أن Token صحيح (انسخه من response تسجيل الدخول)

### خطأ: "Route [login] not defined"
- شغّل: `php artisan route:clear`
- شغّل: `php artisan config:clear`

### خطأ: "Class 'Laravel\Passport\HasApiTokens' not found"
- تأكد من تثبيت Passport: `composer require laravel/passport`
- شغّل: `composer dump-autoload`

### Token لا يعمل
- تأكد من تشغيل: `php artisan passport:install`
- تأكد من تشغيل migrations: `php artisan migrate`

---

## ✅ قائمة التحقق:

- [ ] Passport مثبت: `composer show laravel/passport`
- [ ] Migrations تم تشغيلها: `php artisan migrate:status`
- [ ] Passport keys تم إنشاؤها: `php artisan passport:install`
- [ ] Laravel server يعمل: `php artisan serve`
- [ ] User Model يحتوي على `HasApiTokens`
- [ ] Routes تستخدم `auth:api`

---

## 🎉 إذا حصلت على Token في response:

**مبروك! Passport يعمل بشكل صحيح! 🚀**

الآن يمكنك استخدام Token في جميع الـ API requests المحمية.

---

## 📝 ملاحظات:

- Token صالح حتى يتم تسجيل الخروج أو حذفه
- يمكن للمستخدم إنشاء أكثر من token
- Token آمن ومشفر

---

**جاهز للاختبار! 🎯**

