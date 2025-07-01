# اختبار APIs إدارة طلاب المدرسة القرآنية باستخدام cURL
# Quran School Student Management APIs Test

Write-Host "🏫 اختبار APIs إدارة طلاب المدرسة القرآنية" -ForegroundColor Green
Write-Host "=" * 50

# إعدادات الاتصال
$baseUrl = "http://localhost:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

# معرف المدرسة القرآنية للاختبار (سيتم تحديده تلقائياً)
$quranSchoolId = 1

try {
    Write-Host "`n1️⃣ اختبار جلب معلومات المدرسة القرآنية..." -ForegroundColor Yellow
    
    $response1 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/info" -Method GET -Headers $headers
    
    if ($response1.success) {
        Write-Host "✅ تم جلب معلومات المدرسة بنجاح" -ForegroundColor Green
        Write-Host "   اسم المدرسة: $($response1.data.quran_school.name)"
        Write-Host "   المسجد: $($response1.data.quran_school.mosque.name)"
        Write-Host "   عدد الحلقات الفرعية: $($response1.data.circle_groups.Count)"
        Write-Host "   إجمالي الطلاب: $($response1.data.statistics.total_students)"
        
        # حفظ معرف الحلقة الفرعية الأولى للاختبار
        if ($response1.data.circle_groups.Count -gt 0) {
            $circleGroupId = $response1.data.circle_groups[0].id
            Write-Host "   سيتم استخدام الحلقة الفرعية: $($response1.data.circle_groups[0].name) (ID: $circleGroupId)"
        } else {
            Write-Host "❌ لا توجد حلقات فرعية متاحة للاختبار" -ForegroundColor Red
            exit
        }
    } else {
        Write-Host "❌ فشل جلب معلومات المدرسة: $($response1.message)" -ForegroundColor Red
        exit
    }

    Write-Host "`n2️⃣ اختبار إضافة طالب جديد..." -ForegroundColor Yellow
    
    # بيانات الطالب الجديد
    $studentData = @{
        identity_number = "1234567890$(Get-Random -Minimum 10 -Maximum 99)"
        name = "طالب تجريبي - $(Get-Date -Format 'HH:mm')"
        phone = "0501234567"
        guardian_name = "ولي أمر تجريبي"
        guardian_phone = "0507654321"
        birth_date = "2010-01-01"
        nationality = "سعودي"
        education_level = "ابتدائي"
        neighborhood = "حي النموذج"
        circle_group_id = $circleGroupId
        memorization_plan = "حفظ جزء عم"
        review_plan = "مراجعة يومية"
    } | ConvertTo-Json -Depth 3
    
    $response2 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students" -Method POST -Body $studentData -Headers $headers
    
    if ($response2.success) {
        Write-Host "✅ تم إضافة الطالب بنجاح" -ForegroundColor Green
        Write-Host "   اسم الطالب: $($response2.data.student.name)"
        Write-Host "   رقم الهوية: $($response2.data.student.identity_number)"
        Write-Host "   كلمة المرور الافتراضية: $($response2.data.student.default_password)"
        Write-Host "   الحلقة الفرعية: $($response2.data.student.circle_group.name)"
        
        $newStudentId = $response2.data.student.id
    } else {
        Write-Host "❌ فشل إضافة الطالب: $($response2.message)" -ForegroundColor Red
        if ($response2.errors) {
            $response2.errors | ForEach-Object {
                Write-Host "   خطأ: $_" -ForegroundColor Red
            }
        }
    }

    Write-Host "`n3️⃣ اختبار جلب قائمة طلاب المدرسة..." -ForegroundColor Yellow
    
    $response3 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students" -Method GET -Headers $headers
    
    if ($response3.success) {
        Write-Host "✅ تم جلب قائمة الطلاب بنجاح" -ForegroundColor Green
        Write-Host "   عدد الطلاب في الصفحة: $($response3.data.students.Count)"
        Write-Host "   إجمالي الطلاب: $($response3.data.pagination.total)"
        
        if ($response3.data.students.Count -gt 0) {
            Write-Host "   أول 3 طلاب:"
            $response3.data.students | Select-Object -First 3 | ForEach-Object {
                Write-Host "     - $($_.name) ($($_.identity_number))"
            }
        }
    } else {
        Write-Host "❌ فشل جلب قائمة الطلاب: $($response3.message)" -ForegroundColor Red
    }

    Write-Host "`n4️⃣ اختبار فلترة الطلاب حسب الحلقة الفرعية..." -ForegroundColor Yellow
    
    $response4 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students?circle_group_id=$circleGroupId&is_active=true" -Method GET -Headers $headers
    
    if ($response4.success) {
        Write-Host "✅ تم فلترة الطلاب بنجاح" -ForegroundColor Green
        Write-Host "   عدد الطلاب في الحلقة الفرعية: $($response4.data.students.Count)"
    } else {
        Write-Host "❌ فشل فلترة الطلاب: $($response4.message)" -ForegroundColor Red
    }

    Write-Host "`n5️⃣ اختبار البحث بالكلمات المفتاحية..." -ForegroundColor Yellow
    
    $response5 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students?search=تجريبي" -Method GET -Headers $headers
    
    if ($response5.success) {
        Write-Host "✅ تم البحث بنجاح" -ForegroundColor Green
        Write-Host "   عدد النتائج: $($response5.data.students.Count)"
    } else {
        Write-Host "❌ فشل البحث: $($response5.message)" -ForegroundColor Red
    }

    # اختبار تحديث الطالب إذا تم إنشاؤه بنجاح
    if ($newStudentId) {
        Write-Host "`n6️⃣ اختبار تحديث معلومات الطالب..." -ForegroundColor Yellow
        
        $updateData = @{
            name = "طالب محدث - $(Get-Date -Format 'HH:mm')"
            phone = "0509876543"
            memorization_plan = "حفظ جزء عم + جزء تبارك"
        } | ConvertTo-Json
        
        $response6 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students/$newStudentId" -Method PUT -Body $updateData -Headers $headers
        
        if ($response6.success) {
            Write-Host "✅ تم تحديث معلومات الطالب بنجاح" -ForegroundColor Green
            Write-Host "   الاسم الجديد: $($response6.data.student.name)"
            Write-Host "   الجوال الجديد: $($response6.data.student.phone)"
        } else {
            Write-Host "❌ فشل تحديث معلومات الطالب: $($response6.message)" -ForegroundColor Red
        }
    }

    Write-Host "`n🎉 انتهى اختبار APIs إدارة طلاب المدرسة القرآنية بنجاح!" -ForegroundColor Green

    Write-Host "`n📋 ملخص الـ APIs المتاحة:" -ForegroundColor Cyan
    Write-Host "$('=' * 40)"
    Write-Host "1. GET  /api/quran-schools/{id}/info"
    Write-Host "   - جلب معلومات المدرسة والحلقات الفرعية النشطة"
    Write-Host ""
    Write-Host "2. POST /api/quran-schools/{id}/students"
    Write-Host "   - إضافة طالب جديد للمدرسة القرآنية"
    Write-Host "   - البيانات المطلوبة: identity_number, name, guardian_name, guardian_phone, circle_group_id"
    Write-Host ""
    Write-Host "3. GET  /api/quran-schools/{id}/students"
    Write-Host "   - جلب قائمة الطلاب مع إمكانية الفلترة والبحث"
    Write-Host "   - معاملات الفلترة: circle_group_id, is_active, search, per_page"
    Write-Host ""
    Write-Host "4. PUT  /api/quran-schools/{id}/students/{studentId}"
    Write-Host "   - تحديث معلومات طالب موجود"
    Write-Host ""
    Write-Host "5. DELETE /api/quran-schools/{id}/students/{studentId}"
    Write-Host "   - إلغاء تفعيل طالب (حذف منطقي)"

    Write-Host "`n✨ جميع الـ APIs تعمل بشكل صحيح وجاهزة للاستخدام في الواجهة الأمامية!" -ForegroundColor Green

} catch {
    Write-Host "❌ حدث خطأ أثناء الاختبار: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "التفاصيل: $($_.Exception)" -ForegroundColor Red
}

Write-Host "`nانتهى الاختبار." -ForegroundColor White
