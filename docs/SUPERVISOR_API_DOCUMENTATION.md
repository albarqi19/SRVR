# توثيق APIs المشرف - Supervisor API Documentation

## نظرة عامة
هذا التوثيق يغطي جميع APIs المتاحة للمشرفين في نظام إدارة الحلقات القرآنية.

**Base URL:** `/api/supervisors`  
**المصادقة:** Bearer Token (Sanctum)  
**Content-Type:** `application/json`

---

## 🔐 المصادقة
جميع المسارات تتطلب مصادقة باستخدام Bearer Token وأن يكون المستخدم لديه دور "supervisor".

```http
Authorization: Bearer {your-token}
```

---

## 📋 فهرس المحتويات

1. [إدارة الحلقات](#إدارة-الحلقات)
2. [إدارة الطلاب](#إدارة-الطلاب)
3. [إدارة المعلمين](#إدارة-المعلمين)
4. [تقييم المعلمين](#تقييم-المعلمين)
5. [إدارة الحضور](#إدارة-الحضور)
6. [طلبات النقل](#طلبات-النقل)
7. [التقارير والإحصائيات](#التقارير-والإحصائيات)

---

## 🏫 إدارة الحلقات

### 1. الحصول على الحلقات المشرف عليها
```http
GET /api/supervisors/circles
```

**الاستجابة:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "حلقة الفجر",
      "mosque": {
        "id": 1,
        "name": "مسجد الرحمن",
        "neighborhood": "الروضة"
      },
      "time_period": "فجر",
      "max_students": 15,
      "current_students_count": 12,
      "groups_count": 2
    }
  ]
}
```

---

## 👥 إدارة الطلاب

### 1. الحصول على طلاب حلقة محددة
```http
GET /api/supervisors/circles/{circleId}/students
```

**المعاملات:**
- `circleId` (integer): معرف الحلقة

**الاستجابة:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "أحمد محمد",
      "phone": "0501234567",
      "guardian_phone": "0509876543",
      "enrollment_date": "2024-01-15",
      "group": {
        "id": 1,
        "name": "المجموعة الأولى"
      }
    }
  ]
}
```

---

## 👨‍🏫 إدارة المعلمين

### 1. الحصول على معلمي حلقة محددة
```http
GET /api/supervisors/circles/{circleId}/teachers
```

**المعاملات:**
- `circleId` (integer): معرف الحلقة

**الاستجابة:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "محمد أحمد",
      "phone": "0501234567",
      "job_title": "معلم تحفيظ",
      "task_type": "معلم بمكافأة",
      "work_time": "فجر",
      "evaluation": 8,
      "start_date": "2024-01-01",
      "attendance_today": "غير محدد"
    }
  ]
}
```

### 2. إنشاء تقرير لمعلم
```http
POST /api/supervisors/teacher-report
```

**البيانات المطلوبة:**
```json
{
  "teacher_id": 1,
  "evaluation_score": 8,
  "performance_notes": "أداء ممتاز في التدريس",
  "attendance_notes": "منتظم في الحضور",
  "recommendations": "يُنصح بإعطائه مزيد من الحلقات"
}
```

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم إنشاء التقرير وتحديث التقييم بنجاح",
  "data": {
    "teacher_id": 1,
    "teacher_name": "محمد أحمد",
    "supervisor_id": 1,
    "supervisor_name": "علي سعد",
    "evaluation_score": 8,
    "performance_notes": "أداء ممتاز في التدريس",
    "attendance_notes": "منتظم في الحضور",
    "recommendations": "يُنصح بإعطائه مزيد من الحلقات",
    "report_date": "2024-12-12 10:30:00"
  }
}
```

### 3. الحصول على تقرير شامل لمعلم
```http
GET /api/supervisors/teacher-report/{teacherId}
```

**المعاملات:**
- `teacherId` (integer): معرف المعلم

**الاستجابة:**
```json
{
  "success": true,
  "data": {
    "teacher_info": {
      "id": 1,
      "name": "محمد أحمد",
      "phone": "0501234567",
      "identity_number": "1234567890",
      "nationality": "سعودي",
      "job_title": "معلم تحفيظ",
      "task_type": "معلم بمكافأة",
      "work_time": "فجر",
      "start_date": "2024-01-01",
      "evaluation": 8
    },
    "workplace_info": {
      "circle_name": "حلقة الفجر",
      "mosque_name": "مسجد الرحمن",
      "mosque_neighborhood": "الروضة"
    },
    "performance_metrics": {
      "current_evaluation": 8,
      "absence_count": 0,
      "ratel_activated": false
    },
    "report_generated": {
      "by": "علي سعد",
      "date": "2024-12-12 10:30:00",
      "supervisor_id": 1
    }
  }
}
```

---

## 📊 تقييم المعلمين

### 1. إنشاء تقييم جديد لمعلم
```http
POST /api/supervisors/teacher-evaluations
```

**البيانات المطلوبة:**
```json
{
  "teacher_id": 1,
  "performance_score": 18,
  "attendance_score": 20,
  "student_interaction_score": 17,
  "behavior_cooperation_score": 19,
  "memorization_recitation_score": 16,
  "general_evaluation_score": 18,
  "notes": "تقييم ممتاز للمعلم",
  "evaluation_date": "2024-12-12",
  "evaluation_period": "شهري",
  "evaluator_role": "مشرف",
  "status": "مسودة"
}
```

**القيم المسموحة لـ status:**
- `مسودة`
- `مكتمل`
- `معتمد`
- `مراجعة`

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم إنشاء التقييم بنجاح",
  "data": {
    "evaluation_id": 1,
    "teacher_name": "محمد أحمد",
    "total_score": 108,
    "evaluation_date": "2024-12-12"
  }
}
```

### 2. الحصول على تقييمات معلم محدد
```http
GET /api/supervisors/teacher-evaluations/{teacherId}
```

**المعاملات:**
- `teacherId` (integer): معرف المعلم

**الاستجابة:**
```json
{
  "success": true,
  "data": {
    "teacher": {
      "id": 1,
      "name": "محمد أحمد"
    },
    "evaluations": [
      {
        "id": 1,
        "evaluator_name": "علي سعد",
        "evaluator_role": "مشرف",
        "performance_score": 18,
        "attendance_score": 20,
        "student_interaction_score": 17,
        "behavior_cooperation_score": 19,
        "memorization_recitation_score": 16,
        "general_evaluation_score": 18,
        "total_score": 108,
        "percentage": 90.0,
        "evaluation_period": "شهري",
        "evaluation_date": "2024-12-12",
        "status": "مكتمل",
        "notes": "تقييم ممتاز للمعلم"
      }
    ],
    "statistics": {
      "total_evaluations": 1,
      "average_score": 108.0,
      "average_percentage": 90.0,
      "latest_evaluation_date": "2024-12-12"
    }
  }
}
```

### 3. تحديث تقييم معلم
```http
PUT /api/supervisors/teacher-evaluations/{evaluationId}
```

**المعاملات:**
- `evaluationId` (integer): معرف التقييم

**البيانات المطلوبة (اختيارية):**
```json
{
  "performance_score": 19,
  "attendance_score": 20,
  "student_interaction_score": 18,
  "behavior_cooperation_score": 19,
  "memorization_recitation_score": 17,
  "general_evaluation_score": 19,
  "evaluation_period": "شهري",
  "notes": "تحديث التقييم",
  "status": "معتمد"
}
```

**القيم المسموحة لـ status:**
- `مسودة`
- `مكتمل`
- `معتمد`
- `مراجعة`

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم تحديث التقييم بنجاح",
  "data": {
    "evaluation_id": 1,
    "total_score": 112,
    "percentage": 93.33,
    "status": "معتمد"
  }
}
```

### 4. اعتماد تقييم معلم
```http
POST /api/supervisors/teacher-evaluations/{evaluationId}/approve
```

**المعاملات:**
- `evaluationId` (integer): معرف التقييم

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم اعتماد التقييم بنجاح"
}
```

### 5. حذف تقييم معلم
```http
DELETE /api/supervisors/teacher-evaluations/{evaluationId}
```

**المعاملات:**
- `evaluationId` (integer): معرف التقييم

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم حذف التقييم بنجاح"
}
```

---

## 📅 إدارة الحضور

### 1. تسجيل حضور معلم
```http
POST /api/supervisors/teacher-attendance
```

**البيانات المطلوبة:**
```json
{
  "teacher_id": 1,
  "status": "حاضر",
  "attendance_date": "2024-12-12",
  "notes": "حضر في الوقت المحدد"
}
```

**القيم المسموحة لـ status:**
- `حاضر`
- `غائب`
- `مستأذن`
- `متأخر`

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم تسجيل الحضور بنجاح",
  "data": {
    "teacher_name": "محمد أحمد",
    "status": "حاضر",
    "attendance_date": "2024-12-12"
  }
}
```

---

## 🔄 طلبات النقل

### 1. طلب نقل طالب
```http
POST /api/supervisors/student-transfer
```

**البيانات المطلوبة:**
```json
{
  "student_id": 1,
  "current_circle_id": 1,
  "requested_circle_id": 2,
  "current_circle_group_id": 1,
  "requested_circle_group_id": 2,
  "transfer_reason": "رغبة الطالب في تغيير التوقيت",
  "notes": "طالب متميز يستحق النقل"
}
```

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم تقديم طلب النقل بنجاح",
  "data": {
    "request_id": 1,
    "status": "pending",
    "request_date": "2024-12-12T10:30:00.000000Z"
  }
}
```

### 2. الحصول على طلبات النقل المقدمة
```http
GET /api/supervisors/transfer-requests
```

**الاستجابة:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "student": {
        "id": 1,
        "name": "أحمد محمد"
      },
      "current_circle": {
        "id": 1,
        "name": "حلقة الفجر"
      },
      "requested_circle": {
        "id": 2,
        "name": "حلقة المغرب"
      },
      "current_group": {
        "id": 1,
        "name": "المجموعة الأولى"
      },
      "requested_group": {
        "id": 2,
        "name": "المجموعة الثانية"
      },
      "status": "pending",
      "transfer_reason": "رغبة الطالب في تغيير التوقيت",
      "request_date": "2024-12-12T10:30:00.000000Z",
      "response_date": null,
      "response_notes": null,
      "transfer_date": null
    }
  ]
}
```

### 3. الموافقة على طلب نقل
```http
POST /api/supervisors/transfer-requests/{requestId}/approve
```

**المعاملات:**
- `requestId` (integer): معرف طلب النقل

**الاستجابة:**
```json
{
  "success": true,
  "message": "تمت الموافقة على طلب النقل بنجاح"
}
```

### 4. رفض طلب نقل
```http
POST /api/supervisors/transfer-requests/{requestId}/reject
```

**المعاملات:**
- `requestId` (integer): معرف طلب النقل

**البيانات المطلوبة:**
```json
{
  "reason": "عدم توفر مكان في الحلقة المطلوبة"
}
```

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم رفض طلب النقل"
}
```

---

## 📈 التقارير والإحصائيات

### 1. إحصائيات لوحة المعلومات
```http
GET /api/supervisors/dashboard-stats
```

**الاستجابة:**
```json
{
  "success": true,
  "data": {
    "circles_count": 3,
    "students_count": 45,
    "transfer_requests": {
      "total": 10,
      "pending": 2,
      "approved": 6,
      "rejected": 1,
      "completed": 1
    }
  }
}
```

---

## 🚫 أكواد الأخطاء

| الكود | الرسالة | الوصف |
|-------|---------|--------|
| 401 | Unauthorized | المستخدم غير مصادق عليه |
| 403 | Forbidden | المستخدم ليس له صلاحية |
| 404 | Not Found | العنصر المطلوب غير موجود |
| 422 | Validation Error | خطأ في التحقق من البيانات |
| 500 | Server Error | خطأ في الخادم |

---

## 📝 ملاحظات مهمة

1. **المصادقة**: جميع المسارات تتطلب Bearer Token صالح
2. **الصلاحيات**: المستخدم يجب أن يكون له دور "supervisor"
3. **التحقق من الصلاحية**: المشرف يمكنه الوصول فقط للحلقات المسندة إليه
4. **التقييمات**: النتيجة الإجمالية تُحسب تلقائياً من مجموع المعايير الستة (كل معيار من 20 نقطة، المجموع 120)
5. **التواريخ**: يُفضل استخدام تنسيق ISO 8601 للتواريخ
6. **حالات التقييم**: يمر التقييم بعدة حالات: "مسودة" ثم "مكتمل" ثم "معتمد" أو "مراجعة"
7. **الحضور**: يتم حفظ الحضور مؤقتاً في ملاحظات المعلم (يمكن تطويره لاحقاً)

---

## 🔄 تحديثات المستقبل

1. تطوير نظام حضور منفصل للمعلمين
2. إضافة APIs لإدارة زيارات المشرفين
3. تطوير نظام الإشعارات الفورية
4. إضافة APIs لإدارة المهام والواجبات
5. تطوير نظام التقارير المتقدمة

---

**تاريخ آخر تحديث:** 12 ديسمبر 2024  
**الإصدار:** 1.0.0
