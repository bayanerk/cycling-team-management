# طرق تسجيل الحضور (Attendance Check Methods)

## نظرة عامة

نظام تسجيل الحضور يدعم **طريقتين** لتسجيل إكمال الرايد:

---

## الطريقة الأولى: تلقائي عبر GPS Tracking

### متى تستخدم؟
- ✅ عندما يعمل GPS بشكل صحيح
- ✅ عندما يكمل المستخدم الرايد بنجاح
- ✅ عندما يصل المستخدم إلى نقطة النهاية

### API Endpoint:
**POST** `/api/ride-participants/{rideParticipant}/mark-completed`

### Headers:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {user_token}
```

### Body (اختياري - بيانات GPS):
```json
{
  "distance_km": 25.5,
  "avg_speed_kmh": 18.5,
  "calories_burned": 350,
  "points_earned": 12
}
```

### Response:
```json
{
  "success": true,
  "message": "تم تسجيل إكمال الرايد بنجاح (GPS)",
  "data": {
    "participant": {
      "id": 1,
      "status": "completed",
      "completed_at": "2025-12-20 09:00:00",
      "distance_km": 25.5,
      "avg_speed_kmh": 18.5,
      "calories_burned": 350,
      "points_earned": 12
    }
  }
}
```

### كيف يعمل؟
1. المستخدم يبدأ الرايد
2. GPS tracking يبدأ التسجيل تلقائياً
3. عند الوصول إلى نقطة النهاية:
   - التطبيق يحسب: `distance_km`, `avg_speed_kmh`, `calories_burned`, `points_earned`
   - يرسل البيانات تلقائياً إلى هذا الـ endpoint
   - `status` يتغير إلى `completed`

---

## الطريقة الثانية: يدوي من Admin

### متى تستخدم؟
- ❌ عندما يفشل GPS أو لا يعمل
- ❌ عندما يحدث مشكلة تقنية
- ❌ عندما يحتاج Admin لتسجيل الحضور يدوياً
- ❌ عندما يريد Admin تصحيح بيانات

### API Endpoint:
**POST** `/api/rides/{ride}/check-attendance`

### Headers:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {admin_token}
```

### Body:
```json
{
  "participants": [
    {
      "user_id": 5,
      "status": "completed",
      "distance_km": 25.5,
      "avg_speed_kmh": 18.5,
      "calories_burned": 350,
      "points_earned": 12
    },
    {
      "user_id": 6,
      "status": "no_show"
    },
    {
      "user_id": 7,
      "status": "completed",
      "distance_km": 30.0,
      "avg_speed_kmh": 20.0,
      "calories_burned": 400,
      "points_earned": 15
    }
  ]
}
```

### Response:
```json
{
  "success": true,
  "message": "تم تسجيل الحضور بنجاح",
  "data": {
    "updated_count": 3,
    "updated_ids": [1, 2, 3]
  }
}
```

### كيف يعمل؟
1. Admin يفتح قائمة المشاركين: `GET /api/rides/{ride}/participants`
2. Admin يتحقق من الحضور الفعلي
3. Admin يرسل البيانات يدوياً:
   - `status`: `completed` أو `no_show`
   - البيانات الإحصائية (اختياري)
4. النظام يحدث الحالة تلقائياً

---

## مقارنة الطريقتين:

| الميزة | GPS (تلقائي) | Admin (يدوي) |
|--------|--------------|--------------|
| **من يستخدمها؟** | المستخدم (تلقائي) | Admin |
| **متى تستخدم؟** | GPS يعمل بشكل صحيح | GPS فشل أو مشاكل تقنية |
| **البيانات** | تلقائية من GPS | يدوية من Admin |
| **السرعة** | فوري عند الوصول | يدوي بعد انتهاء الرايد |
| **الدقة** | دقيقة (GPS) | تعتمد على Admin |

---

## سيناريوهات الاستخدام:

### السيناريو 1: GPS يعمل بشكل صحيح ✅
1. المستخدم يكمل الرايد
2. GPS يرسل البيانات تلقائياً → `markCompleted()`
3. `status = completed` ✅

### السيناريو 2: GPS فشل ❌
1. المستخدم يكمل الرايد لكن GPS لم يسجل
2. Admin يتحقق من الحضور الفعلي
3. Admin يستخدم `checkAttendance()` يدوياً
4. `status = completed` ✅

### السيناريو 3: GPS سجل بشكل خاطئ ⚠️
1. GPS سجل بيانات خاطئة
2. Admin يتحقق من البيانات الصحيحة
3. Admin يستخدم `checkAttendance()` لتصحيح البيانات
4. البيانات تصبح صحيحة ✅

---

## ملاحظات مهمة:

✅ **الطريقتان متكاملتان**: يمكن استخدام أي منهما حسب الحاجة

✅ **الأولوية**: GPS تلقائي (أسرع وأدق)، لكن Admin يمكنه التصحيح في أي وقت

✅ **البيانات**: كلتا الطريقتين تحفظ نفس البيانات (`distance_km`, `avg_speed_kmh`, `calories_burned`, `points_earned`)

✅ **Security**: 
- GPS: فقط صاحب الحجز يمكنه تسجيل الإكمال
- Admin: فقط Admin يمكنه تسجيل الحضور يدوياً

---

**جاهز للاستخدام! 🚀**

