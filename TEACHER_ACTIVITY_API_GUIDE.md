# 📊 API تتبع نشاط المعلمين اليومي - دليل المطور

## 🎯 الغرض من API

هذا API يُحل المشكلة الأساسية في متابعة المعلمين: **تتبع النشاط الحقيقي وليس مجرد الحضور**

### ❌ المشكلة السابقة
- لوحة التحكم تعرض بيانات مُحاكاة للمعلمين
- لا يوجد تتبع حقيقي لما إذا كان المعلم:
  - سجل حضور الطلاب
  - أدخل جلسات التسميع
  - قام بواجباته اليومية

### ✅ الحل الجديد
- تتبع حقيقي لنشاط المعلم بناءً على البيانات الفعلية
- مؤشرات أداء واضحة ومفصلة
- إحصائيات دقيقة للمتابعة والتقييم

---

## 🚀 API Endpoints

### 1. نشاط المعلمين اليومي

```http
GET /api/supervisors/teachers-daily-activity
```

**المعاملات:**
- `supervisor_id` (مطلوب): معرف المشرف
- `date` (اختياري): التاريخ بصيغة YYYY-MM-DD (افتراضي: اليوم)

**مثال الطلب:**
```bash
curl -X GET "http://localhost:8000/api/supervisors/teachers-daily-activity?supervisor_id=1&date=2025-07-01"
```

**مثال الاستجابة:**
```json
{
  "success": true,
  "data": {
    "date": "2025-07-01",
    "supervisor": {
      "id": 1,
      "name": "المشرف الأول"
    },
    "teachers_activity": [
      {
        "teacher_id": 1,
        "teacher_name": "أحمد محمد",
        "phone": "0501234567",
        "circle": {
          "id": 1,
          "name": "الحلقة الأولى"
        },
        "mosque": {
          "id": 1,
          "name": "مسجد هيلة الحربي"
        },
        "daily_activity": {
          "has_activity": true,
          "attendance_recorded": true,
          "recitation_recorded": true,
          "students_count": 25,
          "attendance_count": 23,
          "recitation_sessions_count": 15,
          "recited_students_count": 18,
          "attendance_percentage": 92.0,
          "recitation_percentage": 72.0,
          "activity_status": "نشط - مكتمل",
          "status_color": "green",
          "details": {
            "attendance_status": "تم التحضير",
            "recitation_status": "تم التسميع (72%)",
            "completion_summary": "مكتمل - حضور: 92%، تسميع: 72%"
          }
        }
      }
    ],
    "summary": {
      "total_teachers": 5,
      "active_teachers": 4,
      "attendance_recorded": 4,
      "recitation_recorded": 3,
      "completion_rate": 80.0,
      "attendance_percentage": 80.0,
      "recitation_percentage": 60.0
    }
  }
}
```

### 2. إحصائيات المعلمين لفترة زمنية

```http
GET /api/supervisors/teachers-activity-statistics
```

**المعاملات:**
- `supervisor_id` (مطلوب): معرف المشرف  
- `start_date` (اختياري): تاريخ البداية (افتراضي: منذ أسبوع)
- `end_date` (اختياري): تاريخ النهاية (افتراضي: اليوم)

**مثال الطلب:**
```bash
curl -X GET "http://localhost:8000/api/supervisors/teachers-activity-statistics?supervisor_id=1&start_date=2025-06-24&end_date=2025-07-01"
```

**مثال الاستجابة:**
```json
{
  "success": true,
  "data": {
    "period": {
      "start": "2025-06-24",
      "end": "2025-07-01",
      "total_days": 8
    },
    "teachers_statistics": [
      {
        "teacher_id": 1,
        "teacher_name": "أحمد محمد",
        "statistics": {
          "attendance_days": 6,
          "recitation_days": 5,
          "active_days": 6,
          "total_recitation_sessions": 35,
          "recited_students_count": 20,
          "avg_sessions_per_day": 7.0
        },
        "performance_grade": {
          "score": 87.5,
          "grade": "ممتاز",
          "color": "green",
          "breakdown": {
            "attendance_score": 37.5,
            "recitation_score": 31.25
          }
        }
      }
    ],
    "overall_summary": {
      "total_teachers": 5,
      "average_attendance_days": 5.2,
      "average_recitation_days": 4.8,
      "average_performance_score": 82.5,
      "attendance_rate": 65.0,
      "recitation_rate": 60.0,
      "grade_distribution": {
        "ممتاز": 2,
        "جيد": 2,
        "مقبول": 1
      }
    }
  }
}
```

---

## 🏗️ كيفية عمل النظام

### 1. تتبع التحضير (الحضور)
- يتم فحص جدول `student_attendances` للتاريخ المحدد
- البحث عن سجلات الحضور للطلاب الذين يدرسهم المعلم
- حساب نسبة الطلاب الذين تم تسجيل حضورهم

### 2. تتبع التسميع
- يتم فحص جدول `recitation_sessions` للتاريخ المحدد
- البحث عن جلسات التسميع التي سجلها المعلم
- حساب عدد الطلاب الذين تم تسميعهم

### 3. حساب مؤشرات الأداء
- **نشط - مكتمل**: سجل الحضور والتسميع
- **نشط - جزئي**: سجل واحداً منهما فقط  
- **غير نشط**: لم يسجل أي نشاط

### 4. درجة الأداء
- **التحضير**: 50% من الدرجة
- **التسميع**: 50% من الدرجة
- **الدرجات**: ممتاز (90%+)، جيد (75%+)، مقبول (60%+)، ضعيف (<60%)

---

## 🎨 استخدام في الواجهة الأمامية

### JavaScript/jQuery Example

```javascript
// جلب نشاط المعلمين لليوم
async function loadTeachersActivity() {
    try {
        const response = await fetch('/api/supervisors/teachers-daily-activity?supervisor_id=1');
        const data = await response.json();
        
        if (data.success) {
            displayTeachersActivity(data.data);
        }
    } catch (error) {
        console.error('Error loading teachers activity:', error);
    }
}

// عرض البيانات في الواجهة
function displayTeachersActivity(data) {
    const container = document.getElementById('teachers-list');
    
    data.teachers_activity.forEach(teacher => {
        const activity = teacher.daily_activity;
        const card = document.createElement('div');
        card.className = `teacher-card ${activity.status_color}`;
        
        card.innerHTML = `
            <h3>${teacher.teacher_name}</h3>
            <p>الحلقة: ${teacher.circle.name}</p>
            <div class="activity-indicators">
                <span class="indicator ${activity.attendance_recorded ? 'active' : 'inactive'}">
                    التحضير: ${activity.attendance_percentage}%
                </span>
                <span class="indicator ${activity.recitation_recorded ? 'active' : 'inactive'}">
                    التسميع: ${activity.recitation_percentage}%
                </span>
            </div>
            <p class="status">${activity.activity_status}</p>
        `;
        
        container.appendChild(card);
    });
}
```

---

## 🔧 التثبيت والإعداد

### 1. إضافة Routes
تأكد من إضافة الـ routes في `routes/api.php`:

```php
// تتبع نشاط المعلمين اليومي
Route::get('/teachers-daily-activity', [SupervisorController::class, 'getTeacherDailyActivity']);
Route::get('/teachers-activity-statistics', [SupervisorController::class, 'getTeachersActivityStatistics']);
```

### 2. متطلبات قاعدة البيانات
- جدول `student_attendances` مع البيانات الصحيحة
- جدول `recitation_sessions` مع ربط المعلمين والطلاب
- جدول `circle_supervisors` لربط المشرفين بالحلقات

### 3. اختبار API
```bash
# تشغيل اختبار شامل
php artisan serve
./test_teacher_activity_api.ps1
```

---

## 📈 فوائد النظام الجديد

### للمشرفين:
- ✅ متابعة دقيقة لأداء المعلمين
- ✅ تقارير مفصلة وشاملة  
- ✅ مؤشرات واضحة للتدخل عند الحاجة

### للإدارة:
- ✅ إحصائيات شاملة عن جودة التعليم
- ✅ تقييم موضوعي للمعلمين
- ✅ تحسين مستوى الخدمة التعليمية

### للمعلمين:
- ✅ تتبع واضح لأدائهم
- ✅ تحفيز لتحسين الانتظام
- ✅ شفافية في التقييم

---

## 🚨 ملاحظات مهمة

1. **الأمان**: API محمي بمعرف المشرف ولا يعرض إلا البيانات المصرح بها
2. **الأداء**: يتم استخدام فهارس قاعدة البيانات لتحسين الأداء
3. **التوافق**: يعمل مع الواجهة الحالية مع تحسينات إضافية
4. **القابلية للتوسع**: يمكن إضافة مؤشرات أداء إضافية بسهولة

---

🎉 **الآن لديك نظام متابعة متطور وحقيقي لأداء المعلمين!**
