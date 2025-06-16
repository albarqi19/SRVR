# مرجع سريع - APIs المشرف
## Supervisor APIs Quick Reference

**Base URL:** `/api/supervisors`  
**Auth:** Bearer Token + Role: supervisor

---

## 🚀 المسارات السريعة

### 📋 الحلقات والطلاب
```http
GET    /circles                              # الحلقات المشرف عليها
GET    /circles/{id}/students                # طلاب حلقة
GET    /circles/{id}/teachers                # معلمي حلقة
```

### 👨‍🏫 إدارة المعلمين
```http
POST   /teacher-attendance                   # تسجيل حضور معلم
POST   /teacher-report                       # إنشاء تقرير معلم
GET    /teacher-report/{teacherId}           # تقرير شامل لمعلم
```

### 📊 تقييم المعلمين
```http
POST   /teacher-evaluations                 # إنشاء تقييم جديد
GET    /teacher-evaluations/{teacherId}     # تقييمات معلم
PUT    /teacher-evaluations/{evaluationId}  # تحديث تقييم
POST   /teacher-evaluations/{id}/approve    # اعتماد تقييم
DELETE /teacher-evaluations/{evaluationId}  # حذف تقييم
```

### 🔄 طلبات النقل
```http
POST   /student-transfer                     # طلب نقل طالب
GET    /transfer-requests                    # طلبات النقل المقدمة
POST   /transfer-requests/{id}/approve       # موافقة على طلب
POST   /transfer-requests/{id}/reject        # رفض طلب
```

### 📈 الإحصائيات
```http
GET    /dashboard-stats                      # إحصائيات المشرف
```

---

## 📋 نموذج التقييم
**المعايير الستة (كل معيار من 0-20):**
- `performance_evaluation`: تقييم الأداء
- `attendance_evaluation`: تقييم الالتزام بالحضور  
- `student_interaction_evaluation`: تقييم التفاعل مع الطلاب
- `attitude_cooperation_evaluation`: تقييم السمت والتعاون
- `memorization_evaluation`: تقييم الحفظ والتلاوة
- `general_evaluation`: التقييم العام

**المجموع:** 120 نقطة (100%)

---

## 🎯 نماذج البيانات السريعة

### إنشاء تقييم:
```json
{
  "teacher_id": 1,
  "performance_evaluation": 18,
  "attendance_evaluation": 20,
  "student_interaction_evaluation": 17,
  "attitude_cooperation_evaluation": 19,
  "memorization_evaluation": 16,
  "general_evaluation": 18,
  "notes": "ملاحظات التقييم",
  "evaluation_date": "2024-12-12"
}
```

### تسجيل حضور:
```json
{
  "teacher_id": 1,
  "status": "حاضر", // حاضر|غائب|مستأذن|متأخر
  "attendance_date": "2024-12-12",
  "notes": "ملاحظات الحضور"
}
```

### طلب نقل طالب:
```json
{
  "student_id": 1,
  "current_circle_id": 1,
  "requested_circle_id": 2,
  "transfer_reason": "سبب النقل",
  "notes": "ملاحظات إضافية"
}
```

---

## ⚡ أكواد الاستجابة
- `200` - نجح الطلب
- `201` - تم الإنشاء
- `401` - غير مصادق عليه
- `403` - ممنوع الوصول
- `404` - غير موجود
- `422` - خطأ في البيانات
- `500` - خطأ الخادم

---

## 🔒 متطلبات الأمان
- Bearer Token مطلوب
- دور "supervisor" مطلوب
- المشرف يصل فقط للحلقات المسندة إليه
- لا يمكن حذف التقييمات المعتمدة

---

**🚀 جاهز للاستخدام!**
