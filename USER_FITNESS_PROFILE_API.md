# وثائق API لملف اللياقة البدنية (User Fitness Profile API)

## نظرة عامة

ملف اللياقة البدنية مطلوب من المستخدم قبل التسجيل على رايد لأول مرة. يحتوي على معلومات صحية ورياضية للمستخدم.

---

## الجدول: `user_fitness_profiles`

### الأعمدة:
- `id` (PK)
- `user_id` (FK → users) - **unique** (كل مستخدم له profile واحد فقط)
- `height_cm` (INTEGER) - الطول بالسنتيمتر
- `weight_kg` (DECIMAL 5,2) - الوزن بالكيلوغرام
- `medical_notes` (TEXT) - ملاحظات طبية
- `other_sports` (TEXT) - رياضات أخرى
- `last_ride_date` (DATE) - آخر تاريخ لقيادة الدراجة
- `max_distance_km` (DECIMAL 8,2) - أطول مسافة قطعها المستخدم
- `created_at`, `updated_at`

---

## المنطق المهم:

### `max_distance_km` - للاستخدام الأولي فقط:
- ✅ يُستخدم **فقط عند التسجيل لأول مرة** لتحديد المستوى الأولي
- ✅ إذا كتب 30 كم أو أكثر → المستوى المتوسط
- ✅ إذا أقل من 30 كم → لا يسمح التسجيل على مستوى أعلى من مبتدئ
- ⚠️ **بعد** أن يبدأ المستخدم يسجل رايدات فعلية:
  - النظام يحسب المسافات الفعلية من `ride_participants.distance_km`
  - من خلال المسافات الفعلية، النقاط، وعدد الجولات → يتغير مستوى المستخدم ديناميكياً
  - المستوى الفعلي يُخزن في `user_level` (سيتم إنشاؤه لاحقاً)

---

## API Endpoints

### 1. إنشاء/تحديث ملف اللياقة البدنية

**POST** `/api/users/fitness-profile`

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {user_token}
```

**Body:**
```json
{
  "height_cm": 175,
  "weight_kg": 75.5,
  "medical_notes": "لا توجد مشاكل صحية",
  "other_sports": "كرة القدم، السباحة",
  "last_ride_date": "2024-06-15",
  "max_distance_km": 30.0
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "تم حفظ بيانات اللياقة البدنية بنجاح",
  "data": {
    "fitness_profile": {
      "id": 1,
      "height_cm": 175,
      "weight_kg": 75.5,
      "medical_notes": "لا توجد مشاكل صحية",
      "other_sports": "كرة القدم، السباحة",
      "last_ride_date": "2024-06-15",
      "max_distance_km": 30.0
    }
  }
}
```

**ملاحظات:**
- إذا كان المستخدم لديه profile موجود → يتم تحديثه
- إذا لم يكن موجود → يتم إنشاؤه
- جميع الحقول اختيارية (nullable)

---

### 2. جلب ملف اللياقة البدنية

**GET** `/api/users/me/fitness-profile`

**Headers:**
```
Accept: application/json
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "fitness_profile": {
      "id": 1,
      "height_cm": 175,
      "weight_kg": 75.5,
      "medical_notes": "لا توجد مشاكل صحية",
      "other_sports": "كرة القدم، السباحة",
      "last_ride_date": "2024-06-15",
      "max_distance_km": 30.0,
      "created_at": "2025-12-24 17:20:00",
      "updated_at": "2025-12-24 17:20:00"
    }
  }
}
```

**Response (404) - إذا لم يكن موجود:**
```json
{
  "success": false,
  "message": "لم يتم إنشاء ملف اللياقة البدنية بعد",
  "data": null
}
```

---

## Validation Rules:

- `height_cm`: nullable, integer, min: 50, max: 250
- `weight_kg`: nullable, numeric, min: 20, max: 300
- `medical_notes`: nullable, string, max: 1000
- `other_sports`: nullable, string, max: 500
- `last_ride_date`: nullable, date, before_or_equal: today
- `max_distance_km`: nullable, numeric, min: 0, max: 1000

---

## التحقق من Fitness Profile قبل التسجيل على رايد:

### في `POST /api/rides/{ride}/join`:

**إذا لم يكن لدى المستخدم fitness profile:**
```json
{
  "success": false,
  "message": "يجب ملء بيانات اللياقة البدنية قبل التسجيل على رايد",
  "requires_fitness_profile": true
}
```

**الـ Status Code:** `400 Bad Request`

---

## Flow الكامل:

### 1. المستخدم يسجل في التطبيق
- ✅ إنشاء حساب
- ✅ تسجيل دخول

### 2. المستخدم يحاول التسجيل على رايد لأول مرة
- ❌ **يتم رفض الطلب** إذا لم يكن لديه fitness profile
- ✅ **يتم قبول الطلب** إذا كان لديه fitness profile

### 3. المستخدم يملأ بيانات اللياقة البدنية
- ✅ `POST /api/users/fitness-profile`
- ✅ يتم حفظ البيانات

### 4. المستخدم يحاول التسجيل على رايد مرة أخرى
- ✅ **يتم قبول الطلب** (لديه fitness profile)

### 5. بعد بدء الجولات الفعلية
- ✅ النظام يحسب المسافات من `ride_participants.distance_km`
- ✅ يتغير المستوى ديناميكياً (سيتم تنفيذه لاحقاً)

---

## أمثلة الاستخدام:

### مثال 1: إنشاء ملف اللياقة البدنية
```javascript
// Frontend - React Native / Flutter
const response = await fetch('/api/users/fitness-profile', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    height_cm: 175,
    weight_kg: 75.5,
    medical_notes: 'لا توجد مشاكل صحية',
    other_sports: 'كرة القدم، السباحة',
    last_ride_date: '2024-06-15',
    max_distance_km: 30.0
  })
});

const data = await response.json();
```

### مثال 2: التحقق من وجود Fitness Profile قبل التسجيل
```javascript
// عند محاولة التسجيل على رايد
const joinResponse = await fetch('/api/rides/1/join', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const joinData = await joinResponse.json();

if (joinData.requires_fitness_profile) {
  // توجيه المستخدم لملء بيانات اللياقة البدنية
  navigateToFitnessProfileScreen();
}
```

---

## ملاحظات مهمة:

✅ **مطلوب قبل التسجيل**: لا يمكن التسجيل على رايد بدون fitness profile

✅ **Unique Constraint**: كل مستخدم له profile واحد فقط

✅ **Update or Create**: نفس الـ endpoint يستخدم للإنشاء والتحديث

✅ **جميع الحقول اختيارية**: المستخدم يمكنه ملء ما يريد

✅ **max_distance_km**: يستخدم فقط لتحديد المستوى الأولي (لاحقاً سيتم حساب المستوى من البيانات الفعلية)

---

**جاهز للاستخدام! 🚀**

