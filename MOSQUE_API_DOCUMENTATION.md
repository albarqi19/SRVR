# توثيق API المساجد - Mosque API Documentation

## نظرة عامة | Overview
تم تطوير مجموعة شاملة من واجهات برمجة التطبيقات (APIs) لإدارة المساجد والعلاقات المرتبطة بها مع المعلمين والحلقات والطلاب في نظام GARB.

## المسارات المتاحة | Available Endpoints

### 1. إدارة المساجد الأساسية | Basic Mosque Management

#### قائمة المساجد | List Mosques
```
GET /api/mosques
```
- **الوصف**: جلب قائمة جميع المساجد مع إمكانية الفلترة والبحث
- **المعاملات الاختيارية**:
  - `search`: البحث في أسماء المساجد
  - `district`: تصفية حسب الحي
  - `per_page`: عدد النتائج في الصفحة (افتراضي: 15)
  - `page`: رقم الصفحة
- **الاستجابة**: قائمة مقسمة من المساجد مع معلومات أساسية

#### تفاصيل مسجد محدد | Show Specific Mosque
```
GET /api/mosques/{id}
```
- **الوصف**: جلب تفاصيل مسجد محدد
- **المعاملات**: `id` - معرف المسجد
- **الاستجابة**: تفاصيل كاملة للمسجد

#### إنشاء مسجد جديد | Create New Mosque
```
POST /api/mosques
```
- **الوصف**: إنشاء مسجد جديد
- **البيانات المطلوبة**:
  ```json
  {
    "mosque_name": "اسم المسجد",
    "district": "اسم الحي",
    "street": "اسم الشارع (اختياري)",
    "phone": "رقم الهاتف (اختياري)",
    "coordinates": {
      "latitude": "خط العرض (اختياري)",
      "longitude": "خط الطول (اختياري)",
      "map_link": "رابط الخريطة (اختياري)"
    }
  }
  ```

#### تحديث مسجد | Update Mosque
```
PUT /api/mosques/{id}
```
- **الوصف**: تحديث بيانات مسجد موجود
- **المعاملات**: `id` - معرف المسجد
- **البيانات**: نفس بيانات الإنشاء

#### حذف مسجد | Delete Mosque
```
DELETE /api/mosques/{id}
```
- **الوصف**: حذف مسجد (مع التحقق من عدم وجود حلقات نشطة)
- **المعاملات**: `id` - معرف المسجد

### 2. العلاقات والبيانات المرتبطة | Relationships & Related Data

#### حلقات المسجد | Mosque Circles
```
GET /api/mosques/{id}/circles
```
- **الوصف**: جلب جميع الحلقات التابعة لمسجد محدد
- **المعاملات الاختيارية**:
  - `active_only`: عرض الحلقات النشطة فقط (true/false)

#### معلمو المسجد | Mosque Teachers
```
GET /api/mosques/{id}/teachers
```
- **الوصف**: جلب جميع المعلمين العاملين في المسجد
- **المعاملات الاختيارية**:
  - `active_only`: عرض المعلمين النشطين فقط (true/false)

#### طلاب المسجد | Mosque Students
```
GET /api/mosques/{id}/students
```
- **الوصف**: جلب جميع الطلاب المسجلين في المسجد
- **المعاملات الاختيارية**:
  - `active_only`: عرض الطلاب النشطين فقط (true/false)

### 3. الإحصائيات والتقارير | Statistics & Reports

#### إحصائيات المسجد التفصيلية | Detailed Mosque Statistics
```
GET /api/mosques/{id}/statistics
```
- **الوصف**: إحصائيات شاملة لمسجد محدد تتضمن:
  - عدد الحلقات (الإجمالي والنشط)
  - عدد المعلمين والطلاب
  - إحصائيات الحضور
  - متوسط الأداء
  - إحصائيات التسميع

#### إحصائيات جميع المساجد | All Mosques Statistics
```
GET /api/reports/mosque-stats
```
- **الوصف**: إحصائيات عامة لجميع المساجد في النظام

### 4. البحث المتقدم | Advanced Search

#### البحث عن المساجد القريبة | Nearby Mosques Search
```
GET /api/mosques/nearby/search
```
- **الوصف**: البحث عن المساجد القريبة من موقع محدد
- **المعاملات المطلوبة**:
  - `latitude`: خط العرض
  - `longitude`: خط الطول
  - `radius`: نصف القطر بالكيلومتر (افتراضي: 5)
- **المعاملات الاختيارية**:
  - `limit`: عدد النتائج (افتراضي: 10)

## نماذج الاستجابة | Response Examples

### استجابة ناجحة | Success Response
```json
{
  "نجح": true,
  "رسالة": "تم جلب البيانات بنجاح",
  "البيانات": {
    // بيانات المسجد أو المساجد
  },
  "معلومات_التصفح": {
    // معلومات التصفح في حالة القوائم المقسمة
  }
}
```

### استجابة خطأ | Error Response
```json
{
  "نجح": false,
  "رسالة": "رسالة الخطأ",
  "الأخطاء": {
    // تفاصيل الأخطاء
  }
}
```

## الاستخدام | Usage

### مثال بـ JavaScript | JavaScript Example
```javascript
// جلب قائمة المساجد
fetch('/api/mosques')
  .then(response => response.json())
  .then(data => {
    console.log('المساجد:', data.البيانات);
  });

// جلب تفاصيل مسجد محدد
fetch('/api/mosques/1')
  .then(response => response.json())
  .then(data => {
    console.log('تفاصيل المسجد:', data.البيانات);
  });

// إنشاء مسجد جديد
fetch('/api/mosques', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    mosque_name: 'مسجد النور',
    district: 'المروج',
    street: 'شارع الأمير فهد',
    phone: '0112345678'
  })
})
.then(response => response.json())
.then(data => {
  console.log('تم إنشاء المسجد:', data.البيانات);
});
```

### مثال بـ PHP/Laravel | PHP/Laravel Example
```php
use Illuminate\Support\Facades\Http;

// جلب قائمة المساجد
$response = Http::get('/api/mosques');
$mosques = $response->json()['البيانات'];

// جلب إحصائيات مسجد
$stats = Http::get('/api/mosques/1/statistics')->json();

// البحث عن المساجد القريبة
$nearby = Http::get('/api/mosques/nearby/search', [
    'latitude' => 24.7136,
    'longitude' => 46.6753,
    'radius' => 10
])->json();
```

## ملاحظات مهمة | Important Notes

1. **التوثق**: جميع المسارات تتطلب رؤوس HTTP صحيحة:
   ```
   Content-Type: application/json
   Accept: application/json
   ```

2. **اللغة**: جميع الاستجابات تأتي باللغة العربية مع دعم Unicode

3. **التحقق**: النظام يتحقق من صحة البيانات المرسلة ويرجع أخطاء مفصلة

4. **الأمان**: يتم التحقق من صحة المعرفات وحماية من SQL Injection

5. **الأداء**: النتائج مقسمة لتحسين الأداء وتجربة المستخدم

## حالة التطوير | Development Status

✅ **مكتمل**: CRUD operations للمساجد  
✅ **مكتمل**: استعلامات العلاقات (المعلمين، الطلاب، الحلقات)  
✅ **مكتمل**: الإحصائيات الأساسية  
⚠️ **قيد التطوير**: إحصائيات متقدمة  
✅ **مكتمل**: البحث الجغرافي  
✅ **مكتمل**: التحقق من البيانات  
✅ **مكتمل**: معالجة الأخطاء بالعربية  

## اختبار الـ API | API Testing

تم اختبار النظام وهو يعمل بنجاح:
- ✅ قائمة المساجد تعمل (GET /api/mosques)
- ✅ المسارات مسجلة بشكل صحيح
- ✅ الاستجابات تأتي بتنسيق JSON باللغة العربية
- ⚠️ إحصائيات المسجد تحتاج مراجعة (خطأ 500)

تاريخ آخر تحديث: 2025-06-07
