# وثائق API للمشاركين في الرايدات (Ride Participants API)

## نظرة عامة

تم إنشاء نظام كامل لإدارة مشاركة المستخدمين في الرايدات مع جميع الحالات المطلوبة.

## الجدول: `ride_participants`

### الأعمدة:
- `id` (PK)
- `ride_id` (FK → rides)
- `user_id` (FK → users)
- `status` (enum: joined, cancelled, excused, completed, no_show)
- `joined_at` (datetime)
- `cancelled_at` (datetime, nullable)
- `excused_at` (datetime, nullable)
- `completed_at` (datetime, nullable)
- `checked_at` (datetime, nullable)
- `distance_km` (decimal 5,2, nullable)
- `avg_speed_kmh` (decimal 5,2, nullable)
- `calories_burned` (int, nullable)
- `points_earned` (int, nullable)
- `created_at`, `updated_at`

---

## API Endpoints

### 1. تسجيل على رايد (Join Ride)

**POST** `/api/rides/{ride}/join`

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}
```

**Response (201):**
```json
{
  "success": true,
  "message": "تم التسجيل على الرايد بنجاح",
  "data": {
    "participant": {
      "id": 1,
      "ride_id": 1,
      "status": "joined",
      "joined_at": "2025-12-17 15:00:00"
    }
  }
}
```

**ملاحظات:**
- المستخدم لا يمكنه التسجيل مرتين على نفس الرايد
- `status` يبدأ بـ `joined`

---

### 2. إلغاء الحجز (Cancel Participation)

**POST** `/api/ride-participants/{rideParticipant}/cancel`

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم إلغاء الحجز بنجاح",
  "data": {
    "participant": {
      "id": 1,
      "status": "cancelled",
      "cancelled_at": "2025-12-17 16:30:00"
    }
  }
}
```

**الشروط:**
- ✅ فقط صاحب الحجز يمكنه الإلغاء
- ✅ يجب أن يكون خلال أول ساعتين من `joined_at`
- ❌ بعد مرور ساعتين: خطأ "لا يمكن إلغاء الحجز بعد مرور ساعتين"

---

### 3. الاعتذار عن الرايد (Excuse)

**POST** `/api/ride-participants/{rideParticipant}/excuse`

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم الاعتذار عن الرايد بنجاح",
  "data": {
    "participant": {
      "id": 1,
      "status": "excused",
      "excused_at": "2025-12-17 17:00:00"
    }
  }
}
```

**الشروط:**
- ✅ فقط صاحب الحجز يمكنه الاعتذار
- ✅ يجب أن يكون قبل `start_time` للرايد
- ✅ يمكن الاعتذار حتى لو كان `cancelled`

---

### 4. رايدات المستخدم (My Rides)

**GET** `/api/users/me/rides`

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "rides": [
      {
        "id": 1,
        "title": "Sunrise Ride",
        "level": "Beginner",
        "start_time": "2025-12-20 06:30:00",
        "status": "joined",
        "joined_at": "2025-12-17 15:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total": 10,
      "per_page": 20
    }
  }
}
```

---

### 5. قائمة المشاركين (Admin Only)

**GET** `/api/rides/{ride}/participants`

**Headers:**
```
Accept: application/json
Authorization: Bearer {admin_token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "participants": [
      {
        "id": 1,
        "user": {
          "id": 5,
          "name": "أحمد محمد",
          "email": "ahmed@example.com",
          "phone": "+966501234567"
        },
        "status": "joined",
        "joined_at": "2025-12-17 15:00:00",
        "distance_km": null,
        "avg_speed_kmh": null,
        "calories_burned": null,
        "points_earned": null
      }
    ]
  }
}
```

---

### 6. تسجيل الحضور (Admin Only)

**POST** `/api/rides/{ride}/check-attendance`

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {admin_token}
```

**Body:**
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
    }
  ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الحضور بنجاح",
  "data": {
    "updated_count": 2,
    "updated_ids": [1, 2]
  }
}
```

---

### 7. تسجيل إكمال الرايد (من GPS Tracking)

**POST** `/api/ride-participants/{rideParticipant}/mark-completed`

**Headers:**
```
Accept: application/json
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل إكمال الرايد بنجاح",
  "data": {
    "participant": {
      "id": 1,
      "status": "completed",
      "completed_at": "2025-12-20 09:00:00"
    }
  }
}
```

**ملاحظات:**
- يتم استدعاؤه تلقائياً من GPS tracking عند الوصول للنهاية
- فقط صاحب الحجز يمكنه تسجيل الإكمال

---

## منطق الحالات (Status Flow)

### 1. **joined** (افتراضي)
- عند التسجيل على رايد
- `joined_at = now()`

### 2. **cancelled**
- إلغاء خلال أول ساعتين من `joined_at`
- `cancelled_at = now()`

### 3. **excused**
- اعتذار قبل `start_time` للرايد
- `excused_at = now()`

### 4. **completed**
- حضر وأكمل الرايد (GPS tracking أو Admin check)
- `completed_at = now()`
- يتم حفظ: `distance_km`, `avg_speed_kmh`, `calories_burned`, `points_earned`

### 5. **no_show** (تلقائي)
- بعد `end_time` للرايد
- Job/Scheduler يفحص:
  - `cancelled_at == null`
  - `excused_at == null`
  - `checked_at == null`
- → `status = 'no_show'`, `checked_at = now()`

---

## Job/Scheduler

تم إعداد Job تلقائي (`MarkNoShowParticipants`) يعمل كل ساعة:

- يفحص الرايدات التي انتهت في الساعة الماضية
- يحدد المشاركين الذين لم يلغيوا ولم يعتذروا ولم يتم فحصهم
- يحدث `status = 'no_show'` تلقائياً

**لتشغيل Scheduler:**
```bash
php artisan schedule:work
```

أو في Production:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## ملاحظات مهمة:

✅ **Unique Constraint**: المستخدم لا يمكنه التسجيل مرتين على نفس الرايد

✅ **Time Validation**: 
- الإلغاء: خلال ساعتين من `joined_at`
- الاعتذار: قبل `start_time`

✅ **Security**: 
- فقط صاحب الحجز يمكنه الإلغاء/الاعتذار
- فقط Admin يمكنه رؤية المشاركين وتسجيل الحضور

✅ **Automatic Processing**:
- `no_show` يتم تلقائياً عبر Job
- `completed` يمكن أن يكون تلقائي (GPS) أو يدوي (Admin)

---

**جاهز للاستخدام! 🚀**

