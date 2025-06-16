# اختبار API مباشر - خطوة بخطوة
# تشغيل: .\demo_api_test.ps1

Write-Host "🎬 عرض مباشر لـ API - نظام جلسات التسميع" -ForegroundColor Blue
Write-Host "=" * 60 -ForegroundColor Blue

$headers = @{'Content-Type' = 'application/json'}
$baseUrl = "http://localhost:8000/api"

try {
    # ==========================================
    # الخطوة 1: إنشاء جلسة تسميع
    # ==========================================
    Write-Host "`n🚀 الخطوة 1: إنشاء جلسة تسميع جديدة" -ForegroundColor Yellow
    Write-Host "-" * 40 -ForegroundColor Gray
    
    $sessionData = @{
        student_id = 1
        teacher_id = 2
        quran_circle_id = 1
        start_surah_number = 2
        start_verse = 1
        end_surah_number = 2
        end_verse = 10
        recitation_type = "مراجعة صغرى"
        grade = 9.0
        evaluation = "ممتاز"
        teacher_notes = "جلسة عرض مباشر - API Demo"
    } | ConvertTo-Json -Depth 3
    
    Write-Host "📤 إرسال البيانات إلى: $baseUrl/recitation/sessions" -ForegroundColor Cyan
    
    $sessionResult = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Headers $headers -Body $sessionData
    
    Write-Host "✅ نجح إنشاء الجلسة!" -ForegroundColor Green
    Write-Host "📋 معرف الجلسة: $($sessionResult.data.session_id)" -ForegroundColor White
    Write-Host "👨‍🎓 الطالب: $($sessionResult.data.student_name)" -ForegroundColor White
    Write-Host "🎯 التقييم: $($sessionResult.data.evaluation)" -ForegroundColor White
    
    $createdSessionId = $sessionResult.data.session_id
    
    # ==========================================
    # الخطوة 2: جلب الجلسات للتحقق
    # ==========================================
    Write-Host "`n📚 الخطوة 2: جلب الجلسات للتحقق" -ForegroundColor Yellow
    Write-Host "-" * 40 -ForegroundColor Gray
    
    $sessionsResult = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions?limit=3" -Method GET -Headers $headers
    
    Write-Host "✅ نجح جلب الجلسات!" -ForegroundColor Green
    Write-Host "📊 عدد الجلسات: $($sessionsResult.data.data.Count)" -ForegroundColor White
    
    # عرض آخر الجلسات
    Write-Host "📋 آخر الجلسات:" -ForegroundColor Cyan
    for ($i = 0; $i -lt [Math]::Min(3, $sessionsResult.data.data.Count); $i++) {
        $session = $sessionsResult.data.data[$i]
        $isOurSession = if ($session.session_id -eq $createdSessionId) { " 👈 الجلسة المنشأة" } else { "" }
        Write-Host "   $($i+1). $($session.session_id) - $($session.evaluation)$isOurSession" -ForegroundColor White
    }
    
    # ==========================================
    # الخطوة 3: إضافة أخطاء للجلسة
    # ==========================================
    Write-Host "`n🐛 الخطوة 3: إضافة أخطاء تلاوة" -ForegroundColor Yellow
    Write-Host "-" * 40 -ForegroundColor Gray
    
    $errorsData = @{
        session_id = $createdSessionId
        errors = @(
            @{
                surah_number = 2
                verse_number = 3
                word_text = "الرحيم"
                error_type = "تجويد"
                correction_note = "عدم إظهار الميم المشددة بوضوح"
                teacher_note = "مراجعة أحكام الإدغام"
                is_repeated = $false
                severity_level = "متوسط"
            },
            @{
                surah_number = 2
                verse_number = 5
                word_text = "هدى"
                error_type = "نطق"
                correction_note = "نطق الهاء غير صحيح"
                teacher_note = "تدريب على مخارج الحروف"
                is_repeated = $true
                severity_level = "خفيف"
            }
        )
    } | ConvertTo-Json -Depth 4
    
    Write-Host "📤 إرسال الأخطاء إلى: $baseUrl/recitation/errors" -ForegroundColor Cyan
    
    $errorsResult = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Headers $headers -Body $errorsData
    
    Write-Host "✅ نجح إضافة الأخطاء!" -ForegroundColor Green
    Write-Host "📊 عدد الأخطاء المضافة: $($errorsResult.total_errors)" -ForegroundColor White
    Write-Host "🔄 تم تحديث الجلسة: $($errorsResult.session_updated)" -ForegroundColor White
    
    # ==========================================
    # التحقق النهائي
    # ==========================================
    Write-Host "`n🔍 التحقق النهائي" -ForegroundColor Yellow
    Write-Host "-" * 40 -ForegroundColor Gray
    
    $finalCheck = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions?limit=1" -Method GET -Headers $headers
    $latestSession = $finalCheck.data.data[0]
    
    Write-Host "📋 آخر جلسة:" -ForegroundColor Cyan
    Write-Host "   المعرف: $($latestSession.session_id)" -ForegroundColor White
    Write-Host "   بها أخطاء: $($latestSession.has_errors)" -ForegroundColor White
    Write-Host "   التقييم: $($latestSession.evaluation)" -ForegroundColor White
    Write-Host "   الطالب: $($latestSession.student_name)" -ForegroundColor White
    
    # ==========================================
    # النتيجة النهائية
    # ==========================================
    Write-Host "`n🎉 نجحت جميع العمليات!" -ForegroundColor Green
    Write-Host "=" * 60 -ForegroundColor Green
    Write-Host "✅ تم إنشاء الجلسة: $createdSessionId" -ForegroundColor Green
    Write-Host "✅ تم جلب الجلسات بنجاح" -ForegroundColor Green
    Write-Host "✅ تم إضافة $($errorsResult.total_errors) أخطاء للجلسة" -ForegroundColor Green
    Write-Host "🎯 API يعمل بشكل مثالي!" -ForegroundColor Green
    
} catch {
    Write-Host "`n❌ حدث خطأ!" -ForegroundColor Red
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.ErrorDetails.Message) {
        Write-Host "`nتفاصيل الخطأ:" -ForegroundColor Yellow
        $errorData = $_.ErrorDetails.Message | ConvertFrom-Json
        $errorData | ConvertTo-Json -Depth 3
    }
}
