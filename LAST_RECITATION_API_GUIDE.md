# 📚 دليل APIs آخر تسميع للطالب

## 🎯 **APIs المتاحة لعرض آخر تسميع للطالب:**

### 1. **API آخر تسميع مخصص (الأفضل)**
```
GET /api/students/{id}/last-recitation
```

**الاستخدام:**
```powershell
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/last-recitation" -Method GET -Headers @{"Accept"="application/json"}
```

**النتيجة:**
```json
{
  "success": true,
  "message": "تم جلب آخر جلسة تسميع بنجاح",
  "student_name": "أحمد علي البارقي",
  "data": {
    "id": 176,
    "student_id": 1,
    "teacher_id": 1,
    "session_date": "2025-06-14",
    "session_time": "10:25:33",
    "recitation_type": "حفظ",
    "surah_range": {
      "start_surah": 1,
      "start_verse": 1,
      "end_surah": 1,
      "end_verse": 7
    },
    "content_summary": "سورة 1 آية 1 إلى سورة 1 آية 7",
    "total_verses": 7,
    "grade": 95,
    "evaluation": "ممتاز",
    "status": "مكتملة",
    "has_errors": false,
    "teacher_notes": "جلسة اختبار للتقدم التلقائي",
    "performance_rating": "ممتاز",
    "days_ago": 0.036
  }
}
```

---

### 2. **API جلسات التسميع مع الفلترة**
```
GET /api/students/{id}/recitation-sessions?per_page=1
```

**الاستخدام:**
```powershell
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/recitation-sessions?per_page=1" -Method GET
$lastRecitation = $response.البيانات[0]
```

**مميزات:**
- مرتبة من الأحدث للأقدم
- دعم الصفحات (pagination)
- فلترة بالتاريخ والدرجة

**مثال مع فلترة:**
```powershell
# آخر تسميع بدرجة ≥ 8
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/recitation-sessions?min_quality=8&per_page=1" -Method GET
```

---

### 3. **API إحصائيات الطالب (يشمل آخر جلسة)**
```
GET /api/recitation/sessions/stats/student/{id}
```

**الاستخدام:**
```powershell
$stats = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions/stats/student/1" -Method GET
Write-Output "آخر جلسة: $($stats.data.last_session_date)"
```

**النتيجة:**
```json
{
  "success": true,
  "data": {
    "total_sessions": 96,
    "average_grade": 85.50,
    "last_session_date": "2025-06-14 10:25:33",
    "error_rate_percentage": 0
  }
}
```

---

### 4. **API تفاصيل الطالب (يشمل جلسات حديثة)**
```
GET /api/students/{id}
```

**الاستخدام:**
```powershell
$student = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1" -Method GET
$lastSession = $student.البيانات.جلسات_التسميع_الأخيرة[0]
```

---

### 5. **API المنهج اليومي (يشمل جلسات اليوم)**
```
GET /api/students/{id}/daily-curriculum
```

**الاستخدام:**
```powershell
$curriculum = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/daily-curriculum" -Method GET
$todayRecitation = $curriculum.data.today_recitations.memorization
```

**مفيد لـ:**
- معرفة ما سمعه الطالب اليوم
- المحتوى المطلوب للغد
- التقدم في المنهج

---

## 🏆 **أفضل الممارسات:**

### **للاستخدام العام:**
```powershell
# الحصول على آخر تسميع مع جميع التفاصيل
$lastRecitation = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/last-recitation" -Method GET

Write-Output "=== آخر تسميع للطالب ===" 
Write-Output "الاسم: $($lastRecitation.student_name)"
Write-Output "التاريخ: $($lastRecitation.data.session_date)"
Write-Output "الوقت: $($lastRecitation.data.session_time)"
Write-Output "النوع: $($lastRecitation.data.recitation_type)"
Write-Output "المحتوى: $($lastRecitation.data.content_summary)"
Write-Output "الدرجة: $($lastRecitation.data.grade)"
Write-Output "التقييم: $($lastRecitation.data.evaluation)"
Write-Output "الأداء: $($lastRecitation.data.performance_rating)"
Write-Output "منذ: $([math]::Round($lastRecitation.data.days_ago, 1)) يوم"
```

### **للتطبيقات المتقدمة:**
```powershell
# الحصول على آخر 5 جلسات تسميع
$recentSessions = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/recitation-sessions?per_page=5" -Method GET

foreach ($session in $recentSessions.البيانات) {
    Write-Output "$($session.تاريخ_الجلسة) - $($session.نوع_التسميع) - $($session.الدرجة)"
}
```

### **للإحصائيات:**
```powershell
# تجميع إحصائيات شاملة
$lastRecitation = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/last-recitation" -Method GET
$stats = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions/stats/student/1" -Method GET

Write-Output "=== تقرير شامل ===" 
Write-Output "إجمالي الجلسات: $($stats.data.total_sessions)"
Write-Output "المتوسط العام: $($stats.data.average_grade)"
Write-Output "آخر تسميع: $($lastRecitation.data.session_date)"
Write-Output "آخر درجة: $($lastRecitation.data.grade)"
```

---

## 🔧 **معلومات تقنية:**

### **معاملات الاستعلام المدعومة:**

#### للـ `/recitation-sessions`:
- `per_page`: عدد النتائج (افتراضي: 20)
- `start_date`: فلترة من تاريخ معين
- `end_date`: فلترة حتى تاريخ معين  
- `min_quality`: الحد الأدنى للدرجة

#### مثال:
```powershell
# آخر تسميع خلال الأسبوع الماضي بدرجة ≥ 7
$weekAgo = (Get-Date).AddDays(-7).ToString("yyyy-MM-dd")
$today = (Get-Date).ToString("yyyy-MM-dd")

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/recitation-sessions?start_date=$weekAgo&end_date=$today&min_quality=7&per_page=1" -Method GET
```

### **معالجة الأخطاء:**
```powershell
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/last-recitation" -Method GET
    
    if ($response.success) {
        Write-Output "نجح: $($response.message)"
        # معالجة البيانات
    } else {
        Write-Warning "فشل: $($response.message)"
    }
} catch {
    Write-Error "خطأ في الاتصال: $($_.Exception.Message)"
}
```

---

## 📊 **أمثلة للاستخدامات الشائعة:**

### **1. تطبيق المعلم:**
```powershell
# عرض آخر تسميع لجميع طلاب المعلم
$teacherStudents = @(1, 2, 3, 10) # IDs الطلاب

foreach ($studentId in $teacherStudents) {
    try {
        $lastRecitation = Invoke-RestMethod -Uri "http://localhost:8000/api/students/$studentId/last-recitation" -Method GET
        
        Write-Output "$($lastRecitation.student_name): $($lastRecitation.data.evaluation) ($($lastRecitation.data.grade))"
    } catch {
        Write-Warning "لا توجد بيانات للطالب $studentId"
    }
}
```

### **2. تقرير يومي:**
```powershell
# طلاب سمعوا اليوم
$today = (Get-Date).ToString("yyyy-MM-dd")
$studentsToday = @()

foreach ($studentId in @(1, 2, 3, 10)) {
    $lastRecitation = Invoke-RestMethod -Uri "http://localhost:8000/api/students/$studentId/last-recitation" -Method GET
    
    if ($lastRecitation.data.session_date -eq $today) {
        $studentsToday += $lastRecitation.student_name
    }
}

Write-Output "الطلاب الذين سمعوا اليوم: $($studentsToday -join ', ')"
```

### **3. متابعة التقدم:**
```powershell
# مقارنة آخر 3 جلسات للطالب
$sessions = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/recitation-sessions?per_page=3" -Method GET

Write-Output "=== تطور الأداء ==="
for ($i = 0; $i -lt $sessions.البيانات.Length; $i++) {
    $session = $sessions.البيانات[$i]
    Write-Output "الجلسة $($i+1): $($session.التاريخ) - $($session.الدرجة) - $($session.التقييم)"
}
```

---

## ✅ **الخلاصة:**

**أفضل API لآخر تسميع:**
```
GET /api/students/{id}/last-recitation
```

**مزاياه:**
- ✅ معلومات شاملة ومفصلة
- ✅ تنسيق محدد وواضح
- ✅ معالجة أخطاء متقدمة
- ✅ حساب تلقائي للأيام المنقضية
- ✅ تقييم الأداء مدمج
- ✅ ملخص المحتوى المسموع

**الاستخدام الأمثل:**
```powershell
$lastRecitation = Invoke-RestMethod -Uri "http://localhost:8000/api/students/1/last-recitation" -Method GET
# استخدم $lastRecitation.data للوصول لجميع التفاصيل
```
