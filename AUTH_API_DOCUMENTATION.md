# وثائق API للمصادقة (Authentication API Documentation)

## نظرة عامة
تم إنشاء نظام مصادقة كامل يشمل التسجيل، تسجيل الدخول، والتحقق من البريد الإلكتروني ورقم الهاتف باستخدام OTP.

## الجداول المُنشأة

### 1. جدول `users`
يحتوي على جميع بيانات المستخدم:
- `id`: المعرف الفريد
- `name`: الاسم الكامل
- `email`: البريد الإلكتروني (فريد)
- `phone`: رقم الهاتف (فريد، اختياري)
- `password`: كلمة المرور (مشفرة)
- `gender`: الجنس (male/female)
- `age`: العمر
- `profession`: المهنة/الدراسة
- `profile_image`: مسار الصورة الشخصية
- `role`: الدور (admin/coach/rider) - افتراضي: rider
- `language`: اللغة (ar/en) - افتراضي: ar
- `is_active`: حالة الحساب (نشط/معطل)
- `is_coach_approved`: موافقة على الكوتش (للكوتشز فقط)
- `email_verified_at`: تاريخ التحقق من البريد
- `phone_verified_at`: تاريخ التحقق من الهاتف

### 2. جدول `otp_verifications`
يحتوي على رموز التحقق:
- `id`: المعرف الفريد
- `user_id`: معرف المستخدم (اختياري)
- `identifier`: البريد الإلكتروني أو رقم الهاتف
- `type`: نوع التحقق (email/phone)
- `otp_code`: رمز OTP (6 أرقام)
- `is_verified`: حالة التحقق
- `expires_at`: تاريخ انتهاء الصلاحية
- `verified_at`: تاريخ التحقق

## الـ API Endpoints

### 1. التسجيل (Register)
**POST** `/api/auth/register`

**Body:**
```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "phone": "+966501234567",
  "password": "password123",
  "password_confirmation": "password123",
  "gender": "male",
  "age": 25,
  "profession": "مهندس",
  "role": "rider",
  "language": "ar"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "تم إنشاء الحساب بنجاح",
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "phone": "+966501234567",
      "role": "rider",
      "language": "ar"
    },
    "requires_verification": true,
    "message": "يرجى التحقق من البريد الإلكتروني أو رقم الهاتف"
  }
}
```

### 2. تسجيل الدخول (Login)
**POST** `/api/auth/login`

**Body:**
```json
{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

**Response (200):**
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
    }
  }
}
```

### 3. إرسال رمز OTP
**POST** `/api/auth/otp/send`

**Body:**
```json
{
  "identifier": "ahmed@example.com",
  "type": "email"
}
```
أو
```json
{
  "identifier": "+966501234567",
  "type": "phone"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم إرسال رمز التحقق إلى البريد الإلكتروني",
  "data": {
    "otp_id": 1,
    "otp_code": "123456" // فقط في وضع التطوير
  }
}
```

### 4. التحقق من رمز OTP
**POST** `/api/auth/otp/verify`

**Body:**
```json
{
  "identifier": "ahmed@example.com",
  "type": "email",
  "otp_code": "123456"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم التحقق بنجاح",
  "data": {
    "verified": true,
    "user_id": 1
  }
}
```

### 5. تسجيل الخروج (Logout)
**POST** `/api/auth/logout`

**Headers:**
- `Cookie: laravel_session=...` (Session-based auth)

**Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح"
}
```

### 6. الحصول على بيانات المستخدم الحالي
**GET** `/api/auth/me`

**Headers:**
- `Cookie: laravel_session=...` (Session-based auth)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "phone": "+966501234567",
      "gender": "male",
      "age": 25,
      "profession": "مهندس",
      "role": "rider",
      "language": "ar",
      "profile_image": null,
      "email_verified": true,
      "phone_verified": false,
      "is_active": true,
      "is_coach_approved": false
    }
  }
}
```

## ملاحظات مهمة

1. **التحقق من البيانات**: جميع الـ endpoints تستخدم Form Requests للتحقق من صحة البيانات
2. **OTP**: رمز OTP صالح لمدة 10 دقائق فقط
3. **الصور**: يتم حفظ الصور الشخصية في `storage/app/public/profile_images`
4. **الأدوار**: 
   - `admin`: المدير (يمكنه إدارة التطبيق بالكامل)
   - `coach`: الكوتش (يحتاج موافقة من المدير)
   - `rider`: الراكب (المستخدم العادي)
5. **التحقق**: بعد التسجيل، يجب التحقق من البريد الإلكتروني أو رقم الهاتف قبل استخدام التطبيق

## الخطوات التالية

1. إعداد خدمة إرسال البريد الإلكتروني (Mail Service)
2. إعداد خدمة إرسال الرسائل النصية (SMS Service)
3. إضافة نظام استعادة كلمة المرور
4. إضافة تحديث الملف الشخصي

