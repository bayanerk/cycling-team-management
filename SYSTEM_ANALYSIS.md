# تحليل شامل لنظام Mishwar Bicklate

## 📋 نظرة عامة على النظام

نظام **Mishwar Bicklate** هو نظام إدارة متكامل لفريق ركوب الدراجات، يتيح إدارة الرايدات (Rides)، المشاركين، المدربين، الفعاليات، والذكريات. النظام مبني على Laravel 12 مع Filament v4 للوحة التحكم الإدارية.

---

## 👥 الأدوار في النظام (User Roles)

### 1. **Admin (مدير النظام)**
- **الوصول**: Dashboard كامل + جميع API endpoints
- **الصلاحيات**:
  - إنشاء وتعديل وحذف الرايدات
  - إنشاء وتعديل وحذف الفعاليات
  - إدارة المدربين (إنشاء وتعديل ملفات المدربين)
  - إدارة الذكريات (Memories)
  - إدارة جميع المستخدمين
  - تسجيل الحضور يدوياً للمشاركين
  - الاعتذار عن رايد للمشاركين
  - عرض جميع الإحصائيات
  - عرض الكوتشات المسجلين على رايد معين

### 2. **Coach (مدرب)**
- **الوصول**: API فقط (لا Dashboard)
- **الصلاحيات**:
  - التسجيل على رايدات كمدرب (role = 'coach')
  - عرض ملفه الشخصي
  - إدارة إعداداته
  - تتبع GPS أثناء الرايد
  - تسجيل إكمال الرايد
  - عرض رايداته

### 3. **Rider (راكب)**
- **الوصول**: API فقط (لا Dashboard)
- **الصلاحيات**:
  - التسجيل على رايدات كراكب (role = 'rider')
  - عرض ملفه الشخصي
  - إدارة إعداداته
  - تتبع GPS أثناء الرايد
  - تسجيل إكمال الرايد
  - عرض رايداته
  - إلغاء التسجيل (خلال ساعتين)

---

## 🗄️ الجداول والعلاقات (Database Tables)

### 1. **users** (المستخدمون)
**الأعمدة الأساسية:**
- `id`, `name`, `email`, `phone`, `password`
- `gender` (male/female), `age`, `birthday`, `profession`
- `profile_image`, `role` (admin/coach/rider)
- `language` (ar/en), `is_active`, `is_coach_approved`
- `email_verified_at`, `phone_verified_at`

**العلاقات:**
- `hasMany` → `rides` (الرايدات التي أنشأها)
- `hasMany` → `participatedRides` (RideParticipant)
- `hasOne` → `fitnessProfile` (UserFitnessProfile)
- `hasMany` → `addresses` (Address)
- `hasOne` → `setting` (UserSetting)
- `hasOne` → `coachProfile` (Coach)
- `hasOne` → `level` (UserLevel)

---

### 2. **user_fitness_profiles** (ملفات اللياقة البدنية)
**الأعمدة:**
- `user_id` (FK → users.id)
- `height_cm`, `weight_kg`
- `medical_notes`, `other_sports`
- `last_ride_date`, `max_distance_km`

**الغرض:**
- تخزين بيانات اللياقة البدنية للمستخدم
- **مطلوب** قبل التسجيل على أول رايد
- `max_distance_km` يُستخدم لتحديد المستوى الابتدائي

**العلاقات:**
- `belongsTo` → `user`

---

### 3. **user_levels** (مستويات المستخدمين)
**الأعمدة:**
- `user_id` (FK → users.id, unique)
- `level_name` (Beginner/Intermediate/Advanced)
- `level_number` (1, 2, 3...)
- `total_distance`, `total_rides`, `total_points`
- `last_updated`

**الغرض:**
- تتبع أداء المستخدم بعد كل رايد
- تحديث المستوى ديناميكياً
- التحقق من صلاحية التسجيل على رايد معين

**قواعد الترقية:**
- **3 رايدات مبتدئة** → ترقية إلى Intermediate 1
- **6 رايدات متوسطة** → ترقية إلى Advanced 1

**العلاقات:**
- `belongsTo` → `user`

---

### 4. **rides** (الرايدات)
**الأعمدة:**
- `id`, `title`, `location`, `level` (Beginner/Intermediate/Advanced)
- `distance`, `start_time`, `gathering_time`, `end_time`
- `image_url`, `break_location`, `cost`
- `start_lat`, `start_lng`, `end_lat`, `end_lng`
- `created_by` (FK → users.id)

**الغرض:**
- تخزين معلومات الرايدات
- فقط Admin يمكنه إنشاء/تعديل/حذف رايدات

**العلاقات:**
- `belongsTo` → `creator` (User)
- `hasMany` → `participants` (RideParticipant)

---

### 5. **ride_participants** (المشاركون في الرايدات)
**الأعمدة:**
- `id`, `ride_id` (FK → rides.id), `user_id` (FK → users.id)
- `role` (rider/coach) - **مهم**: يحدد إذا كان المشارك رايدر أو كوتش
- `status` (joined/cancelled/excused/completed/no_show)
- `joined_at`, `cancelled_at`, `excused_at`, `completed_at`, `checked_at`
- `distance_km`, `avg_speed_kmh`, `calories_burned`, `points_earned`

**الغرض:**
- تتبع مشاركة المستخدمين في الرايدات
- تخزين بيانات الأداء بعد إكمال الرايد
- التمييز بين الرايدر والكوتش

**العلاقات:**
- `belongsTo` → `ride`
- `belongsTo` → `user`
- `hasMany` → `tracks` (RideTrack)

**قواعد العمل:**
- يمكن الإلغاء خلال **ساعتين** من التسجيل
- يمكن الاعتذار قبل **بداية الرايد** (Admin only)

---

### 6. **ride_tracks** (تتبع GPS)
**الأعمدة:**
- `id`, `ride_id` (FK → rides.id)
- `ride_participant_id` (FK → ride_participants.id)
- `lat`, `lng`, `speed`, `recorded_at`

**الغرض:**
- تخزين نقاط GPS أثناء الرايد
- تتبع المسار الفعلي للمشارك
- حساب المسافة والسرعة

**العلاقات:**
- `belongsTo` → `ride`
- `belongsTo` → `participant` (RideParticipant)

---

### 7. **coaches** (المدربون)
**الأعمدة:**
- `id`, `user_id` (FK → users.id, unique)
- `bio`, `experience_years`, `image_url`
- `specialty`, `certificate`, `rating`

**الغرض:**
- تخزين معلومات المدربين التعريفية
- المدرب هو مستخدم فعلي في النظام (role = 'coach')
- Admin فقط يمكنه إدارة ملفات المدربين

**العلاقات:**
- `belongsTo` → `user`

---

### 8. **events** (الفعاليات)
**الأعمدة:**
- `id`, `title`, `description`, `image_url`
- `location`, `cost`, `start_time`, `end_time`
- `created_by` (FK → users.id)

**الغرض:**
- تخزين معلومات الفعاليات التي ينظمها الفريق
- Admin فقط يمكنه إنشاء/تعديل/حذف فعاليات
- متاحة للعرض العام في Home Screen

**العلاقات:**
- `belongsTo` → `creator` (User)

---

### 9. **memories** (الذكريات)
**الأعمدة:**
- `id`, `image_path`, `description`
- `is_active` (boolean), `photo_date`

**الغرض:**
- تخزين صور الذكريات القديمة للفريق
- عامة للفريق (غير مرتبطة برايد أو مستخدم)
- Admin فقط يمكنه إدارة الذكريات
- فقط الذكريات النشطة (`is_active = true`) تظهر في التطبيق

---

### 10. **addresses** (العناوين)
**الأعمدة:**
- `id`, `user_id` (FK → users.id)
- `city`, `district`, `street`

**الغرض:**
- تخزين عناوين المستخدمين
- للملف الشخصي فقط (غير مطلوبة للتسجيل على رايد)
- يمكن للمستخدم إضافة عدة عناوين

**العلاقات:**
- `belongsTo` → `user`

---

### 11. **user_settings** (إعدادات المستخدم)
**الأعمدة:**
- `id`, `user_id` (FK → users.id, unique)
- `language` (ar/en), `notification_enabled` (boolean)

**الغرض:**
- تخزين إعدادات المستخدم
- اللغة المفضلة
- تفعيل/تعطيل الإشعارات

**العلاقات:**
- `belongsTo` → `user`

---

### 12. **otp_verifications** (التحقق من OTP)
**الأعمدة:**
- `id`, `user_id`, `phone`, `otp_code`, `expires_at`, `verified_at`

**الغرض:**
- التحقق من رقم الهاتف عبر OTP

---

## 🔐 نظام المصادقة (Authentication)

### **Laravel Passport**
- استخدام OAuth2 للـ API
- Token-based authentication
- Routes محمية بـ `auth:api` middleware

### **OTP Verification**
- إرسال OTP عبر SMS
- التحقق من رقم الهاتف
- `phone_verified_at` يتم تحديثه بعد التحقق

---

## 📱 API Endpoints

### **Public Routes (غير محمية)**

#### **Authentication:**
- `POST /api/auth/register` - تسجيل مستخدم جديد
- `POST /api/auth/login` - تسجيل الدخول
- `POST /api/auth/otp/send` - إرسال OTP
- `POST /api/auth/otp/verify` - التحقق من OTP

#### **Public Content:**
- `GET /api/rides` - قائمة الرايدات (مع فلترة: location, level)
- `GET /api/rides/{id}` - تفاصيل رايد
- `GET /api/events` - قائمة الفعاليات
- `GET /api/events/{id}` - تفاصيل فعالية
- `GET /api/coaches` - قائمة المدربين
- `GET /api/coaches/{id}` - تفاصيل مدرب
- `GET /api/memories` - قائمة الذكريات النشطة فقط

---

### **Protected Routes (محمية - تتطلب تسجيل دخول)**

#### **User Profile:**
- `GET /api/users/me/profile` - عرض الملف الشخصي الكامل
- `PUT /api/users/me/profile` - تحديث الملف الشخصي (name, phone, profession, birthday, profile_image)
- `DELETE /api/users/me/account` - حذف الحساب

#### **Fitness Profile:**
- `POST /api/users/fitness-profile` - إنشاء/تحديث ملف اللياقة البدنية
- `GET /api/users/me/fitness-profile` - عرض ملف اللياقة البدنية

#### **Addresses:**
- `GET /api/users/me/addresses` - قائمة العناوين
- `POST /api/users/addresses` - إضافة عنوان جديد
- `PUT /api/users/addresses/{id}` - تحديث عنوان
- `DELETE /api/users/addresses/{id}` - حذف عنوان

#### **Settings:**
- `GET /api/users/me/settings` - عرض الإعدادات
- `PUT /api/users/me/settings` - تحديث الإعدادات

#### **User Level:**
- `GET /api/users/me/level` - عرض المستوى الحالي

#### **Ride Participation:**
- `POST /api/rides/{ride}/join` - التسجيل على رايد
  - **Validation**: يجب وجود fitness profile
  - **Validation**: يجب أن يكون المستوى مناسب
  - **Auto Role**: إذا كان المستخدم coach → role = 'coach'، وإلا role = 'rider'
- `POST /api/ride-participants/{id}/cancel` - إلغاء التسجيل (خلال ساعتين)
- `GET /api/users/me/rides` - قائمة رايدات المستخدم
- `POST /api/ride-participants/{id}/mark-completed` - تسجيل إكمال الرايد (GPS)

#### **GPS Tracking:**
- `POST /api/ride-participants/{id}/track` - إرسال نقطة GPS واحدة
- `POST /api/ride-participants/{id}/tracks` - إرسال عدة نقاط GPS
- `GET /api/ride-participants/{id}/tracks` - جلب نقاط GPS للمشارك

---

### **Admin Only Routes (محمية - Admin فقط)**

#### **Rides Management:**
- `POST /api/rides` - إنشاء رايد
- `PUT /api/rides/{id}` - تحديث رايد
- `DELETE /api/rides/{id}` - حذف رايد

#### **Events Management:**
- `POST /api/events` - إنشاء فعالية
- `PUT /api/events/{id}` - تحديث فعالية
- `DELETE /api/events/{id}` - حذف فعالية

#### **Coaches Management:**
- `POST /api/coaches` - إنشاء ملف مدرب
- `PUT /api/coaches/{id}` - تحديث ملف مدرب
- `DELETE /api/coaches/{id}` - حذف ملف مدرب

#### **Memories Management:**
- `GET /api/admin/memories` - قائمة جميع الذكريات (بما فيها غير النشطة)
- `POST /api/memories` - إضافة ذكرى
- `PUT /api/memories/{id}` - تحديث ذكرى
- `DELETE /api/memories/{id}` - حذف ذكرى

#### **Ride Participants Management:**
- `GET /api/rides/{ride}/participants` - قائمة المشاركين في رايد
  - **Filter**: `?role=rider` أو `?role=coach`
- `POST /api/rides/{ride}/check-attendance` - تسجيل الحضور يدوياً
- `POST /api/ride-participants/{id}/excuse` - الاعتذار عن رايد (قبل البدء)

---

## 🎯 نظام المستويات (Leveling System)

### **المستويات المتاحة:**
1. **Beginner** (مبتدئ)
   - Beginner 1, Beginner 2, Beginner 3...
2. **Intermediate** (متوسط)
   - Intermediate 1, Intermediate 2, Intermediate 3...
3. **Advanced** (متقدم)
   - Advanced 1, Advanced 2, Advanced 3...

### **آلية العمل:**

#### **1. التهيئة الأولية (Initialization):**
- عند أول تسجيل على رايد، النظام يقرأ `max_distance_km` من `user_fitness_profile`
- **إذا `max_distance_km < 30 كم`** → Beginner 1
- **إذا `max_distance_km >= 30 كم`** → Intermediate 1

#### **2. التحديث الديناميكي (Dynamic Update):**
- بعد كل رايد مكتمل (`status = 'completed'`):
  - `total_distance += distance_km`
  - `total_rides += 1`
  - `total_points += points_earned`
  - `last_updated = now()`

#### **3. قواعد الترقية (Promotion Rules):**
- **3 رايدات مبتدئة مكتملة** → ترقية إلى Intermediate 1
- **6 رايدات متوسطة مكتملة** → ترقية إلى Advanced 1

#### **4. التحقق من الصلاحية (Validation):**
- عند التسجيل على رايد، النظام يتحقق من:
  - `user_level.level_name` يجب أن يكون >= `ride.level`
  - **Beginner** < **Intermediate** < **Advanced**

---

## 🏃 نظام اللياقة البدنية (Fitness Profile)

### **البيانات المطلوبة:**
- `height_cm` (الطول بالسنتيمتر)
- `weight_kg` (الوزن بالكيلوجرام)
- `max_distance_km` (أقصى مسافة يمكن قطعها) - **مهم جداً**
- `medical_notes` (ملاحظات طبية)
- `other_sports` (رياضات أخرى)
- `last_ride_date` (تاريخ آخر رايد)

### **القواعد:**
- **إجباري** قبل التسجيل على أول رايد
- `max_distance_km` يُستخدم لتحديد المستوى الابتدائي
- يمكن تحديثه في أي وقت

---

## 📍 نظام تتبع GPS (GPS Tracking)

### **الوظائف:**
1. **تسجيل نقاط GPS أثناء الرايد:**
   - `POST /api/ride-participants/{id}/track` - نقطة واحدة
   - `POST /api/ride-participants/{id}/tracks` - عدة نقاط (batch)

2. **جلب المسار:**
   - `GET /api/ride-participants/{id}/tracks` - جميع نقاط GPS للمشارك

3. **تسجيل إكمال الرايد:**
   - `POST /api/ride-participants/{id}/mark-completed`
   - يرسل: `distance_km`, `avg_speed_kmh`, `calories_burned`, `points_earned`
   - يتم تحديث `user_level` تلقائياً

---

## 🎛️ Admin Dashboard (Filament v4)

### **الوصول:**
- URL: `/admin`
- **Admin فقط** يمكنه الوصول (middleware: `EnsureUserIsAdmin`)

### **الصفحات المتاحة:**

#### **1. Dashboard الرئيسي:**
- **العنوان**: "Mishwar Bicklate"
- **Widgets**:
  - Total Users (إجمالي المستخدمين)
  - Total Rides (إجمالي الرايدات)
  - Total Participants (إجمالي المشاركات)
  - Active Users with Levels (المستخدمين ذوي المستويات)

#### **2. إدارة المستخدمين (User Management):**
- **UserResource**: CRUD كامل للمستخدمين
  - عرض، إنشاء، تعديل، حذف
  - فلترة حسب role (admin/coach/rider)
  - فلترة حسب is_active
- **UserFitnessProfileResource**: إدارة ملفات اللياقة البدنية
- **UserLevelResource**: إدارة مستويات المستخدمين

#### **3. إدارة المدربين (Coaches Management):**
- **CoachResource**: CRUD كامل للمدربين
  - ربط مع User
  - إدارة: bio, experience_years, specialty, rating, image_url, certificate

#### **4. إدارة الرايدات (Rides Management):**
- **RideResource**: CRUD كامل للرايدات
  - إنشاء رايدات جديدة
  - تعديل وحذف رايدات
  - فلترة حسب level و start_time
- **RideParticipantResource**: إدارة المشاركين
  - عرض جميع المشاركين
  - فلترة حسب role (rider/coach) و status
  - تعديل بيانات المشاركة
- **RideTrackResource**: إدارة نقاط GPS
  - عرض جميع نقاط التتبع
  - فلترة حسب ride_id
- **Coaches Who Joined** (صفحة مخصصة):
  - اختيار رايد من القائمة
  - عرض الكوتشات المسجلين على هذا الرايد فقط
  - فلترة حسب status

#### **5. إدارة الفعاليات (Events Management):**
- **EventResource**: CRUD كامل للفعاليات
  - إنشاء فعاليات جديدة
  - فلترة حسب start_time

#### **6. إدارة الذكريات (Memories Management):**
- **MemoryResource**: CRUD كامل للذكريات
  - إضافة صور الذكريات
  - تفعيل/تعطيل الذكريات (is_active)
  - فلترة حسب photo_date و is_active

---

## 🔍 البحث والفلترة (Search & Filters)

### **في Home Screen (API):**
- **Rides**: البحث حسب `location` و `level`
  - `GET /api/rides?location=جدة`
  - `GET /api/rides?level=Intermediate`

### **في Dashboard:**
- جميع Resources تدعم:
  - **Search**: البحث في الأعمدة القابلة للبحث
  - **Filters**: فلترة حسب الحقول المختلفة
  - **Sorting**: ترتيب حسب أي عمود

---

## 📊 الإحصائيات (Statistics)

### **في Dashboard:**
- **Total Users**: إجمالي المستخدمين المسجلين
- **Total Rides**: إجمالي الرايدات المنشأة
- **Total Participants**: إجمالي المشاركات في الرايدات
- **Active Users with Levels**: المستخدمين الذين لديهم مستويات

---

## 🔄 سير العمل (Workflow)

### **للمستخدم الجديد (Rider/Coach):**

1. **التسجيل:**
   - `POST /api/auth/register`
   - إدخال: name, email, phone, password, role

2. **التحقق من الهاتف:**
   - `POST /api/auth/otp/send` → إرسال OTP
   - `POST /api/auth/otp/verify` → التحقق

3. **ملء ملف اللياقة البدنية:**
   - `POST /api/users/fitness-profile`
   - **إجباري** قبل التسجيل على أول رايد
   - إدخال: height_cm, weight_kg, max_distance_km, medical_notes, other_sports

4. **التسجيل على رايد:**
   - `POST /api/rides/{ride}/join`
   - النظام يتحقق من:
     - وجود fitness profile ✓
     - المستوى مناسب للرايد ✓
   - يتم إنشاء `user_level` تلقائياً إذا لم يكن موجوداً

5. **أثناء الرايد:**
   - تتبع GPS: `POST /api/ride-participants/{id}/track`
   - إرسال نقاط GPS بشكل مستمر

6. **بعد إكمال الرايد:**
   - `POST /api/ride-participants/{id}/mark-completed`
   - إرسال: distance_km, avg_speed_kmh, calories_burned, points_earned
   - يتم تحديث `user_level` تلقائياً:
     - `total_distance += distance_km`
     - `total_rides += 1`
     - `total_points += points_earned`
   - يتم التحقق من الترقية تلقائياً

---

### **للـ Admin:**

1. **إنشاء رايد:**
   - Dashboard → Rides → Create
   - إدخال: title, location, level, distance, start_time, gathering_time, end_time, image_url, cost, coordinates

2. **إدارة المشاركين:**
   - Dashboard → Ride Participants
   - عرض جميع المشاركين
   - فلترة حسب role (rider/coach)
   - تسجيل الحضور يدوياً: `POST /api/rides/{ride}/check-attendance`

3. **عرض الكوتشات:**
   - Dashboard → Coaches Who Joined
   - اختيار رايد من القائمة
   - عرض الكوتشات المسجلين على هذا الرايد

4. **إدارة المدربين:**
   - Dashboard → Coaches
   - إنشاء ملف مدرب جديد
   - ربط مع User موجود

5. **إدارة الفعاليات:**
   - Dashboard → Events
   - إنشاء فعاليات جديدة

6. **إدارة الذكريات:**
   - Dashboard → Memories
   - إضافة صور الذكريات
   - تفعيل/تعطيل الذكريات

---

## 🎨 الميزات الإضافية

### **1. نظام الذكريات (Memories):**
- صور عامة للفريق
- غير مرتبطة برايد أو مستخدم
- فقط الذكريات النشطة (`is_active = true`) تظهر في التطبيق
- Admin فقط يمكنه إدارتها

### **2. نظام العناوين (Addresses):**
- يمكن للمستخدم إضافة عدة عناوين
- للملف الشخصي فقط (غير مطلوبة للتسجيل على رايد)
- Fields: city, district, street

### **3. نظام الإعدادات (Settings):**
- اللغة المفضلة (ar/en)
- تفعيل/تعطيل الإشعارات
- يتم إنشاء إعدادات افتراضية تلقائياً

### **4. نظام الملف الشخصي (Profile):**
- عرض شامل: name, email, phone, profile_image, profession, birthday, gender, age, role, language
- يتضمن: fitness profile, level, addresses, settings
- يمكن تحديث: name, phone, profession, birthday, profile_image
- **لا يمكن تحديث email**

---

## 🔒 الأمان والصلاحيات (Security & Permissions)

### **API Authentication:**
- Laravel Passport (OAuth2)
- Token-based authentication
- جميع الـ routes المحمية تتطلب `auth:api`

### **Admin Dashboard:**
- Filament authentication منفصل
- Middleware: `EnsureUserIsAdmin`
- فقط المستخدمين بـ `role = 'admin'` يمكنهم الوصول

### **Validation Rules:**
- **Join Ride**: 
  - يجب وجود fitness profile
  - يجب أن يكون المستوى مناسب
- **Cancel**: 
  - فقط خلال ساعتين من التسجيل
  - فقط صاحب الحجز
- **Excuse**: 
  - Admin only
  - فقط قبل بداية الرايد

---

## 📈 التكاملات والخدمات

### **UserLevelService:**
- `initializeUserLevel()`: تهيئة المستوى الأولي
- `updateUserLevelAfterRide()`: تحديث المستوى بعد رايد
- `checkAndPromoteLevel()`: التحقق من الترقية
- `canUserJoinRide()`: التحقق من صلاحية التسجيل

---

## 🎯 الخلاصة

النظام يوفر:
- ✅ إدارة كاملة للرايدات والمشاركين
- ✅ نظام مستويات ديناميكي
- ✅ تتبع GPS في الوقت الفعلي
- ✅ Dashboard إداري متكامل
- ✅ API شامل لجميع الوظائف
- ✅ نظام أدوار واضح (Admin/Coach/Rider)
- ✅ إدارة المدربين والفعاليات والذكريات
- ✅ نظام ملفات شخصية شامل

---

**تاريخ التحليل**: 29 ديسمبر 2025
**الإصدار**: 1.0
**Framework**: Laravel 12.44.0
**Admin Panel**: Filament v4

