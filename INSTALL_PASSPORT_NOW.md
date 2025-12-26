# 🚀 تثبيت Laravel Passport - خطوات مفصلة

## الطريقة 1: استخدام Laragon (الأسهل)

### الخطوة 1: افتح Terminal في Laragon
1. افتح **Laragon**
2. اضغط على **Terminal** (أو **Cmder**)
3. تأكد أنك في مجلد المشروع:
   ```bash
   cd C:\laragon\www\Mishwar-Bicklate
   ```

### الخطوة 2: تثبيت Passport
```bash
composer require laravel/passport
```

### الخطوة 3: نشر Migrations
```bash
php artisan vendor:publish --tag=passport-migrations
```

### الخطوة 4: تشغيل Migrations
```bash
php artisan migrate
```

### الخطوة 5: إنشاء Encryption Keys
```bash
php artisan passport:install
```

### الخطوة 6: مسح Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## الطريقة 2: إذا لم يعمل composer في Terminal

### استخدم Command Prompt بدلاً من PowerShell:

1. اضغط `Win + R`
2. اكتب `cmd` واضغط Enter
3. انتقل لمجلد المشروع:
   ```cmd
   cd C:\laragon\www\Mishwar-Bicklate
   ```
4. شغّل الأوامر:
   ```cmd
   composer require laravel/passport
   php artisan vendor:publish --tag=passport-migrations
   php artisan migrate
   php artisan passport:install
   php artisan config:clear
   ```

---

## الطريقة 3: استخدام Laragon GUI

1. افتح **Laragon**
2. اضغط بزر الماوس الأيمن على المشروع
3. اختر **Open Terminal Here**
4. شغّل الأوامر أعلاه

---

## الطريقة 4: تثبيت Composer يدوياً (إذا لم يكن موجوداً)

### تحميل Composer:
1. اذهب إلى: https://getcomposer.org/download/
2. حمّل `Composer-Setup.exe`
3. ثبت Composer
4. أعد تشغيل Terminal

---

## ✅ بعد التثبيت:

### تحقق من التثبيت:
```bash
php artisan route:list | grep passport
```

### جرّب تسجيل الدخول:
```json
POST http://localhost:8000/api/auth/login
{
  "email": "your-email@example.com",
  "password": "password123"
}
```

### يجب أن تحصل على:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."  ← Token من Passport
  }
}
```

---

## 🔧 استكشاف الأخطاء:

### خطأ: "composer not found"
- استخدم Laragon Terminal
- أو ثبت Composer من https://getcomposer.org

### خطأ: "Class not found"
- شغّل: `composer dump-autoload`
- شغّل: `php artisan config:clear`

### خطأ في `passport:install`
- تأكد من تشغيل migrations أولاً
- جرب: `php artisan passport:keys`

---

## 📝 ملاحظات:

- ✅ Passport يحتاج PHP 8.2+ (لديك 8.2.29 ✓)
- ✅ تأكد من تشغيل Laravel server: `php artisan serve`
- ✅ بعد التثبيت، الكود الموجود سيعمل تلقائياً

---

**بعد تنفيذ هذه الخطوات، سيعمل Passport! 🎉**

