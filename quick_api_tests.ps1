# اختبارات API سريعة - أمثلة مبسطة

# ========================================
# 🚀 اختبار سريع 1: إنشاء جلسة
# ========================================

Write-Host "🚀 اختبار سريع: إنشاء جلسة تسميع" -ForegroundColor Yellow

$session = @{
    student_id = 1
    teacher_id = 2
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 7
    recitation_type = "حفظ"
    grade = 8.0
    evaluation = "ممتاز"
    teacher_notes = "اختبار سريع"
} | ConvertTo-Json

$headers = @{'Content-Type' = 'application/json'}

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Headers $headers -Body $session
    Write-Host "✅ نجح!" -ForegroundColor Green
    Write-Host "معرف الجلسة: $($result.data.session_id)" -ForegroundColor Cyan
    
    # حفظ المعرف للاختبارات التالية
    $global:quickSessionId = $result.data.session_id
    
} catch {
    Write-Host "❌ فشل: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# ========================================
# 📚 اختبار سريع 2: جلب الجلسات
# ========================================

Write-Host "📚 اختبار سريع: جلب الجلسات" -ForegroundColor Yellow

try {
    $sessions = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions?limit=3" -Method GET -Headers $headers
    Write-Host "✅ نجح!" -ForegroundColor Green
    Write-Host "عدد الجلسات: $($sessions.data.data.Count)" -ForegroundColor Cyan
    
    # عرض أول جلسة
    if ($sessions.data.data.Count -gt 0) {
        $firstSession = $sessions.data.data[0]
        Write-Host "أول جلسة: $($firstSession.session_id) - $($firstSession.evaluation)" -ForegroundColor White
    }
    
} catch {
    Write-Host "❌ فشل: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# ========================================
# 🐛 اختبار سريع 3: إضافة خطأ
# ========================================

Write-Host "🐛 اختبار سريع: إضافة خطأ تلاوة" -ForegroundColor Yellow

if ($global:quickSessionId) {
    $errorData = @{
        session_id = $global:quickSessionId
        errors = @(
            @{
                surah_number = 1
                verse_number = 2
                word_text = "الرحمن"
                error_type = "تجويد"
                correction_note = "مد الألف"
                teacher_note = "تطبيق أحكام المد"
                is_repeated = $false
                severity_level = "خفيف"
            }
        )
    } | ConvertTo-Json -Depth 3

    try {
        $result = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/errors" -Method POST -Headers $headers -Body $errorData
        Write-Host "✅ نجح!" -ForegroundColor Green
        Write-Host "عدد الأخطاء المضافة: $($result.total_errors)" -ForegroundColor Cyan
        
    } catch {
        Write-Host "❌ فشل: $($_.Exception.Message)" -ForegroundColor Red
    }
} else {
    Write-Host "⚠️ لا يوجد معرف جلسة" -ForegroundColor Yellow
}

Write-Host ""

# ========================================
# ❌ اختبار سريع 4: بيانات خاطئة
# ========================================

Write-Host "❌ اختبار سريع: بيانات خاطئة (HTTP 422)" -ForegroundColor Yellow

$invalidData = @{
    student_id = 1
    teacher_id = 2
    # quran_circle_id مفقود
    recitation_type = "نوع خاطئ"
    # evaluation مفقود
} | ConvertTo-Json

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Headers $headers -Body $invalidData
    Write-Host "⚠️ نجح بشكل غير متوقع!" -ForegroundColor Yellow
    
} catch {
    Write-Host "✅ فشل كما هو متوقع!" -ForegroundColor Green
    
    if ($_.ErrorDetails.Message) {
        $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
        if ($errorDetails.errors) {
            Write-Host "أخطاء التحقق:" -ForegroundColor Red
            $errorDetails.errors.PSObject.Properties | ForEach-Object {
                Write-Host "  • $($_.Name): $($_.Value[0])" -ForegroundColor Red
            }
        }
    }
}

Write-Host "`n🎉 انتهت الاختبارات السريعة!" -ForegroundColor Green
