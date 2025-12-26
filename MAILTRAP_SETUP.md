# دليل إعداد Mailtrap لإرسال الإيميلات

## الخطوة 1: إنشاء حساب Mailtrap (مجاني)

1. اذهب إلى: https://mailtrap.io/
2. اضغط على **Sign Up** (أو **Sign In** إذا كان لديك حساب)
3. سجّل بحسابك (Google, GitHub, أو Email)
4. بعد التسجيل، ستنتقل إلى Dashboard

## الخطوة 2: الحصول على بيانات SMTP

1. في Dashboard، اضغط على **Email Testing** من القائمة الجانبية
2. اختر **Inboxes** من القائمة
3. سترى inbox افتراضي باسم **My Inbox** (أو أنشئ inbox جديد)
4. اضغط على **My Inbox**
5. اختر تبويب **SMTP Settings**
6. اختر **Laravel** من القائمة المنسدلة
7. سترى البيانات التالية (مثال):
   ```
   Host: sandbox.smtp.mailtrap.io
   Port: 2525
   Username: xxxxxxxx (مثال: 1a2b3c4d5e6f7g)
   Password: xxxxxxxx (مثال: 1a2b3c4d5e6f7g)
   ```

## الخطوة 3: إضافة البيانات إلى ملف .env

افتح ملف `.env` في مشروع Laravel وأضف/عدّل الإعدادات التالية:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxxxxxxxxxx  # ضع الـ Username من Mailtrap
MAIL_PASSWORD=xxxxxxxxxxxxx  # ضع الـ Password من Mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mishwar-bicklate.com
MAIL_FROM_NAME="${APP_NAME}"
```

### مثال:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=1a2b3c4d5e6f7g
MAIL_PASSWORD=1a2b3c4d5e6f7g
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mishwar-bicklate.com
MAIL_FROM_NAME="Mishwar Bicklate"
```

## الخطوة 4: اختبار الإرسال

### 1. تأكد من تشغيل Laravel:
```bash
php artisan serve
```

### 2. استخدم Postman لإرسال OTP:

**POST** `http://localhost:8000/api/auth/otp/send`

**Headers:**
```
Accept: application/json
Content-Type: application/json
```

**Body:**
```json
{
  "identifier": "your-email@example.com",
  "type": "email"
}
```

### 3. تحقق من Mailtrap:

1. اذهب إلى Mailtrap Dashboard
2. اضغط على **My Inbox**
3. ستجد الإيميل المرسل هناك! 📧

## ملاحظات مهمة:

✅ **Mailtrap مجاني** للاختبار (500 إيميل/شهر في الخطة المجانية)

✅ **لا يرسل إيميلات حقيقية** - فقط للاختبار والتطوير

✅ **الإيميلات تظهر فوراً** في صندوق Mailtrap

✅ **آمن تماماً** - لا يصل الإيميل للمستخدم الحقيقي

## للإنتاج (Production):

عند الانتقال للإنتاج، استبدل Mailtrap بـ:
- **SendGrid** (موصى به)
- **Mailgun**
- **Amazon SES**
- **Gmail SMTP** (للمشاريع الصغيرة)

## استكشاف الأخطاء:

### المشكلة: الإيميل لا يصل
1. تحقق من بيانات SMTP في `.env`
2. تأكد من تشغيل `php artisan config:clear`
3. تحقق من Laravel logs: `storage/logs/laravel.log`

### المشكلة: خطأ في الاتصال
1. تأكد من استخدام `tls` في `MAIL_ENCRYPTION`
2. تأكد من أن Port هو `2525`
3. تحقق من إعدادات Firewall

## الخطوات السريعة:

1. ✅ سجّل في Mailtrap
2. ✅ انسخ SMTP credentials
3. ✅ أضفها في `.env`
4. ✅ شغّل `php artisan config:clear`
5. ✅ جرّب إرسال OTP
6. ✅ تحقق من Mailtrap Inbox

---

**جاهز للاستخدام! 🚀**

