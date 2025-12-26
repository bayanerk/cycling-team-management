# وثائق API لتتبع GPS (Ride Tracks API)

## نظرة عامة

نظام تتبع GPS للمشاركين في الرايدات. يتم إرسال بيانات GPS بشكل مستمر أثناء الرايد (كل 3 ثواني أو كل 5 أمتار).

---

## الجدول: `ride_tracks`

### الأعمدة:
- `id` (PK)
- `ride_id` (FK → rides)
- `ride_participant_id` (FK → ride_participants) - **مهم جداً**
- `lat` (DECIMAL 10,7) - خط العرض
- `lng` (DECIMAL 10,7) - خط الطول
- `speed` (FLOAT) - السرعة (km/h)
- `recorded_at` (DATETIME) - وقت تسجيل النقطة
- `created_at`, `updated_at`

---

## API Endpoints

### 1. إرسال نقطة GPS واحدة (Single Track Point)

**POST** `/api/ride-participants/{rideParticipant}/track`

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {user_token}
```

**Body:**
```json
{
  "lat": 21.543333,
  "lng": 39.172778,
  "speed": 18.5,
  "recorded_at": "2025-12-20 07:15:30"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "تم حفظ نقطة GPS بنجاح",
  "data": {
    "track": {
      "id": 1,
      "lat": 21.543333,
      "lng": 39.172778,
      "speed": 18.5,
      "recorded_at": "2025-12-20 07:15:30"
    }
  }
}
```

**الاستخدام:**
- إرسال نقطة GPS واحدة
- يتم استدعاؤه كل 3 ثواني أو كل 5 أمتار

---

### 2. إرسال عدة نقاط GPS دفعة واحدة (Bulk Track Points)

**POST** `/api/ride-participants/{rideParticipant}/tracks`

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {user_token}
```

**Body:**
```json
{
  "tracks": [
    {
      "lat": 21.543333,
      "lng": 39.172778,
      "speed": 18.5,
      "recorded_at": "2025-12-20 07:15:30"
    },
    {
      "lat": 21.544000,
      "lng": 39.173500,
      "speed": 19.2,
      "recorded_at": "2025-12-20 07:15:33"
    },
    {
      "lat": 21.545000,
      "lng": 39.174500,
      "speed": 20.1,
      "recorded_at": "2025-12-20 07:15:36"
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "تم حفظ نقاط GPS بنجاح",
  "data": {
    "count": 3,
    "track_ids": [1, 2, 3]
  }
}
```

**الاستخدام:**
- إرسال عدة نقاط GPS دفعة واحدة
- مفيد عند إعادة الإرسال أو عند انقطاع الاتصال

---

### 3. جلب المسار الكامل (Get Full Track)

**GET** `/api/ride-participants/{rideParticipant}/tracks`

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
    "participant_id": 1,
    "ride_id": 1,
    "tracks": [
      {
        "id": 1,
        "lat": 21.543333,
        "lng": 39.172778,
        "speed": 18.5,
        "recorded_at": "2025-12-20 07:15:30"
      },
      {
        "id": 2,
        "lat": 21.544000,
        "lng": 39.173500,
        "speed": 19.2,
        "recorded_at": "2025-12-20 07:15:33"
      }
    ],
    "statistics": {
      "total_points": 150,
      "total_distance_km": 25.5,
      "average_speed_kmh": 18.5,
      "max_speed_kmh": 25.3,
      "min_speed_kmh": 0.0,
      "duration_minutes": 82.5
    }
  }
}
```

**الاستخدام:**
- رسم المسار على الخريطة (Polyline)
- حساب المسافة الإجمالية
- حساب السرعة المتوسطة
- اكتشاف no-show (إذا لم توجد نقاط GPS)

**الصلاحيات:**
- صاحب الحجز يمكنه رؤية مساره
- Admin يمكنه رؤية مسار أي مشارك

---

## استخدامات البيانات:

### 1. رسم المسار على الخريطة (Map Polyline)
```javascript
// Frontend example
const polyline = tracks.map(track => [track.lat, track.lng]);
// Use with Google Maps, Mapbox, etc.
```

### 2. حساب المسافة
- يتم حسابها تلقائياً في `getTracks()` response
- استخدام Haversine formula لحساب المسافة بين النقاط

### 3. حساب السرعة المتوسطة
- يتم حسابها تلقائياً في `getTracks()` response
- `average_speed_kmh` = متوسط جميع قيم `speed`

### 4. اكتشاف No-Show
```php
// إذا لم توجد نقاط GPS بعد انتهاء الرايد
if ($tracks->count() === 0 && $ride->end_time->isPast()) {
    // Mark as no-show
}
```

---

## Validation Rules:

### Single Track:
- `lat`: required, numeric, between -90 and 90
- `lng`: required, numeric, between -180 and 180
- `speed`: required, numeric, min: 0
- `recorded_at`: required, date

### Bulk Tracks:
- `tracks`: required, array, min: 1
- `tracks.*.lat`: required, numeric, between -90 and 90
- `tracks.*.lng`: required, numeric, between -180 and 180
- `tracks.*.speed`: required, numeric, min: 0
- `tracks.*.recorded_at`: required, date

---

## Security:

✅ **Authentication Required**: جميع الـ endpoints تحتاج Token

✅ **Authorization**:
- `storeTrack()` و `storeTracks()`: فقط صاحب الحجز يمكنه الإرسال
- `getTracks()`: صاحب الحجز أو Admin

✅ **Validation**:
- التحقق من أن `status = 'joined'` قبل الإرسال
- التحقق من أن المستخدم هو صاحب الحجز

---

## أمثلة الاستخدام:

### مثال 1: إرسال نقطة واحدة (كل 3 ثواني)
```javascript
// Frontend - React Native / Flutter
setInterval(() => {
  const currentLocation = getCurrentLocation();
  
  fetch('/api/ride-participants/1/track', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      lat: currentLocation.latitude,
      lng: currentLocation.longitude,
      speed: currentLocation.speed,
      recorded_at: new Date().toISOString()
    })
  });
}, 3000); // كل 3 ثواني
```

### مثال 2: إرسال عدة نقاط دفعة واحدة
```javascript
// عند إعادة الاتصال بعد انقطاع
const pendingTracks = getPendingTracks(); // نقاط محفوظة محلياً

fetch('/api/ride-participants/1/tracks', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    tracks: pendingTracks
  })
});
```

### مثال 3: جلب المسار ورسمه على الخريطة
```javascript
// جلب المسار
const response = await fetch('/api/ride-participants/1/tracks', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
const tracks = data.data.tracks;

// رسم Polyline على Google Maps
const path = tracks.map(track => ({
  lat: track.lat,
  lng: track.lng
}));

const polyline = new google.maps.Polyline({
  path: path,
  strokeColor: '#FF0000',
  strokeOpacity: 1.0,
  strokeWeight: 2
});

polyline.setMap(map);
```

---

## ملاحظات مهمة:

✅ **الأداء**: 
- استخدام `storeTracks()` (bulk) أفضل من `storeTrack()` (single) عند إرسال عدة نقاط

✅ **التخزين**:
- كل رايد قد يحتوي على مئات أو آلاف النقاط
- تأكد من تنظيف البيانات القديمة دورياً

✅ **الدقة**:
- استخدام Haversine formula لحساب المسافة بدقة
- المسافة محسوبة بين كل نقطتين متتاليتين

✅ **الاستخدام**:
- البيانات تستخدم لرسم المسار، حساب المسافة، السرعة، واكتشاف no-show

---

**جاهز للاستخدام! 🚀**

