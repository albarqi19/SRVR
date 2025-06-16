# 🚀 دليل سريع لتجربة API في الموقع

## 📁 الملفات المطلوبة:

### 1. الملف الأساسي موجود:
- **📄 test_session.json** - يحتوي على بيانات إنشاء الجلسة

## 🎯 كيفية التجربة:

### الطريقة الأولى: التشغيل التلقائي
```powershell
# تشغيل الاختبار الشامل
.\website_api_test.ps1
```

### الطريقة الثانية: التجربة اليدوية

#### 1. تأكد من تشغيل الخادم:
```powershell
php artisan serve
```

#### 2. إنشاء جلسة جديدة:
```powershell
curl.exe -X POST "http://127.0.0.1:8000/api/recitation/sessions" -H "Accept: application/json" -H "Content-Type: application/json" --data "@test_session.json"
```

#### 3. جلب قائمة الجلسات:
```powershell
curl.exe -X GET "http://127.0.0.1:8000/api/recitation/sessions" -H "Accept: application/json"
```

#### 4. جلب جلسة محددة (استبدل SESSION_ID بالمعرف الحقيقي):
```powershell
curl.exe -X GET "http://127.0.0.1:8000/api/recitation/sessions/SESSION_ID" -H "Accept: application/json"
```

#### 5. جلب الإحصائيات:
```powershell
curl.exe -X GET "http://127.0.0.1:8000/api/recitation/stats" -H "Accept: application/json"
```

## 📋 مثال عملي كامل:

### إنشاء ملف أخطاء يدوياً:
```json
{
    "session_id": "RS-20250609-XXXXXX-XXXX",
    "errors": [
        {
            "surah_number": 2,
            "verse_number": 5,
            "word_text": "الذين",
            "error_type": "تجويد",
            "correction_note": "عدم تطبيق القلقلة بشكل صحيح",
            "teacher_note": "يحتاج تدريب على أحكام القلقلة",
            "is_repeated": false,
            "severity_level": "متوسط"
        }
    ]
}
```

### إضافة الأخطاء:
```powershell
curl.exe -X POST "http://127.0.0.1:8000/api/recitation/errors" -H "Accept: application/json" -H "Content-Type: application/json" --data "@errors.json"
```

## 🔍 فحص النتائج:

### تحقق من قاعدة البيانات:
```powershell
php artisan tinker --execute="echo 'آخر جلسة: ' . App\Models\RecitationSession::latest()->first()->session_id;"
```

### عدد الجلسات الكلي:
```powershell
php artisan tinker --execute="echo 'عدد الجلسات: ' . App\Models\RecitationSession::count();"
```

## ⚠️ ملاحظات مهمة:

1. **تأكد من تشغيل Laravel Server** قبل التجربة
2. **استخدم session_id وليس id** عند جلب الجلسات الفردية
3. **الأحرف العربية قد تظهر مُرمزة** في PowerShell لكنها صحيحة في قاعدة البيانات
4. **جميع الملفات في نفس المجلد** لضمان عمل الأوامر

## 🎉 الهدف من التجربة:

- ✅ تأكيد عمل API بشكل صحيح
- ✅ إنشاء جلسات جديدة
- ✅ إضافة أخطاء للجلسات
- ✅ استرجاع البيانات والإحصائيات
- ✅ التأكد من جاهزية النظام للاستخدام في الموقع
