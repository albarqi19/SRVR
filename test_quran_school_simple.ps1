# اختبار APIs إدارة طلاب المدرسة القرآنية - مبسط
# باستخدام خادم Laravel المحلي

Write-Host "🏫 اختبار APIs إدارة طلاب المدرسة القرآنية (مبسط)" -ForegroundColor Green
Write-Host "=" * 60

# إعدادات الاتصال  
$baseUrl = "http://localhost:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

# معرف الحلقة الجماعية للاختبار (من البيانات الموجودة)
$quranSchoolId = 1  # حلقة "تجارب"

Write-Host "`n📋 معلومات الاختبار:" -ForegroundColor Cyan
Write-Host "الحلقة: تجارب (ID: $quranSchoolId)"
Write-Host "المسجد: جامع هيلة الحربي"
Write-Host "النوع: حلقة جماعية"

try {
    Write-Host "`n🔗 التحقق من تشغيل خادم Laravel..." -ForegroundColor Yellow
    
    # التحقق من تشغيل الخادم
    try {
        $healthCheck = Invoke-RestMethod -Uri "http://localhost:8000" -Method GET -TimeoutSec 5
        Write-Host "✅ خادم Laravel يعمل بشكل طبيعي" -ForegroundColor Green
    } catch {
        Write-Host "❌ خادم Laravel غير متاح - تأكد من تشغيل: php artisan serve" -ForegroundColor Red
        Write-Host "🔧 لتشغيل الخادم: php artisan serve --host=localhost --port=8000" -ForegroundColor Yellow
        exit
    }

    Write-Host "`n1️⃣ اختبار جلب معلومات المدرسة القرآنية..." -ForegroundColor Yellow
    
    try {
        $response1 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/info" -Method GET -Headers $headers
        
        if ($response1.success) {
            Write-Host "✅ تم جلب معلومات المدرسة بنجاح" -ForegroundColor Green
            Write-Host "   اسم المدرسة: $($response1.data.quran_school.name)"
            Write-Host "   المسجد: $($response1.data.quran_school.mosque.name)"
            Write-Host "   عدد الحلقات الفرعية: $($response1.data.circle_groups.Count)"
            Write-Host "   إجمالي الطلاب: $($response1.data.statistics.total_students)"
            
            if ($response1.data.circle_groups.Count -gt 0) {
                $circleGroupId = $response1.data.circle_groups[0].id
                Write-Host "   الحلقة الفرعية المستخدمة: $($response1.data.circle_groups[0].name) (ID: $circleGroupId)"
            } else {
                Write-Host "❌ لا توجد حلقات فرعية نشطة" -ForegroundColor Red
                exit
            }
        } else {
            Write-Host "❌ فشل جلب معلومات المدرسة: $($response1.message)" -ForegroundColor Red
            exit
        }
    } catch {
        Write-Host "❌ خطأ في الاتصال بـ API: $($_.Exception.Message)" -ForegroundColor Red
        exit
    }

    Write-Host "`n2️⃣ اختبار إضافة طالب جديد..." -ForegroundColor Yellow
    
    # بيانات الطالب الجديد
    $studentData = @{
        identity_number = "9876543210$(Get-Random -Minimum 10 -Maximum 99)"
        name = "طالب تجريبي API - $(Get-Date -Format 'HH:mm')"
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
    
    try {
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
                $response2.errors.PSObject.Properties | ForEach-Object {
                    Write-Host "   $($_.Name): $($_.Value -join ', ')" -ForegroundColor Red
                }
            }
        }
    } catch {
        Write-Host "❌ خطأ في إضافة الطالب: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $errorResponse = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorResponse)
            $errorBody = $reader.ReadToEnd()
            Write-Host "تفاصيل الخطأ: $errorBody" -ForegroundColor Red
        }
    }

    Write-Host "`n3️⃣ اختبار جلب قائمة طلاب المدرسة..." -ForegroundColor Yellow
    
    try {
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
    } catch {
        Write-Host "❌ خطأ في جلب قائمة الطلاب: $($_.Exception.Message)" -ForegroundColor Red
    }

    Write-Host "`n4️⃣ اختبار فلترة الطلاب..." -ForegroundColor Yellow
    
    try {
        $response4 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students?circle_group_id=$circleGroupId&is_active=true" -Method GET -Headers $headers
        
        if ($response4.success) {
            Write-Host "✅ تم فلترة الطلاب بنجاح" -ForegroundColor Green
            Write-Host "   عدد الطلاب في الحلقة الفرعية: $($response4.data.students.Count)"
        } else {
            Write-Host "❌ فشل فلترة الطلاب: $($response4.message)" -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ خطأ في فلترة الطلاب: $($_.Exception.Message)" -ForegroundColor Red
    }

    Write-Host "`n🎉 انتهى اختبار APIs إدارة طلاب المدرسة القرآنية!" -ForegroundColor Green

    Write-Host "`n📋 ملخص الـ APIs المختبرة:" -ForegroundColor Cyan
    Write-Host "$('=' * 50)"
    Write-Host "✅ GET  /api/quran-schools/{id}/info - جلب معلومات المدرسة"
    Write-Host "✅ POST /api/quran-schools/{id}/students - إضافة طالب جديد" 
    Write-Host "✅ GET  /api/quran-schools/{id}/students - جلب قائمة الطلاب"
    Write-Host "✅ فلترة الطلاب - بحسب الحلقة الفرعية والحالة"

    Write-Host "`n🔗 للاختبار الكامل، تأكد من تشغيل خادم Laravel:" -ForegroundColor Yellow
    Write-Host "php artisan serve --host=localhost --port=8000"

} catch {
    Write-Host "❌ حدث خطأ عام أثناء الاختبار: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "التفاصيل: $($_.Exception)" -ForegroundColor Red
}

Write-Host "`nانتهى الاختبار." -ForegroundColor White
