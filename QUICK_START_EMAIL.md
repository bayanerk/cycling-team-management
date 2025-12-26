# 🚀 دليل سريع لإعداد إرسال الإيميلات

## ✅ ما تم إعداده:

1. ✅ تم إنشاء `OtpMail` class لإرسال الإيميلات
2. ✅ تم إنشاء template جميل للإيميل (`resources/views/emails/otp.blade.php`)
3. ✅ تم تحديث `OtpController` لإرسال الإيميلات فعلياً
4. ✅ تم إعداد كل شيء جاهز للاستخدام

## 📋 الخطوات المتبقية (5 دقائق فقط):

### 1. إنشاء حساب Mailtrap (مجاني):
- اذهب إلى: https://mailtrap.io/
- سجّل بحسابك (Google/GitHub/Email)
- مجاني تماماً للاختبار

### 2. الحصول على بيانات SMTP:
- في Dashboard → Email Testing → Inboxes
- اختر Inbox → SMTP Settings
- اختر Laravel من القائمة
- انسخ: Host, Port, Username, Password

### 3. إضافة البيانات في `.env`:

افتح ملف `.env` وأضف/عدّل:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=ضع_username_من_mailtrap
MAIL_PASSWORD=ضع_password_من_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mishwar-bicklate.com
MAIL_FROM_NAME="Mishwar Bicklate"
```

### 4. مسح Cache:
```bash
php artisan config:clear
```

### 5. تجربة الإرسال:

**في Postman:**
```
POST http://localhost:8000/api/auth/otp/send
Content-Type: application/json
Accept: application/json

{
  "identifier": "your-email@example.com",
  "type": "email"
}
```

### 6. التحقق من Mailtrap:
- اذهب إلى Mailtrap Inbox
- ستجد الإيميل هناك! 📧

## 🎉 جاهز!

بعد إضافة بيانات Mailtrap في `.env`، سيعمل إرسال الإيميلات فوراً!

---

**ملاحظة:** في وضع التطوير (`APP_DEBUG=true`)، سيظهر OTP code في الـ response أيضاً للاختبار السريع.

