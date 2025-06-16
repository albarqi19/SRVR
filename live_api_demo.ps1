# 🔥 العرض التوضيحي المباشر لـ API نظام جلسات التلاوة
# ==============================================================

Write-Host "🔥 العرض التوضيحي المباشر للـ API" -ForegroundColor Red
Write-Host "تاريخ العرض: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
Write-Host "=============================================================" -ForegroundColor Blue
Write-Host ""

# إعداد URL الأساسي
$baseUrl = "http://localhost:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host "📋 الخطوات المطلوبة:" -ForegroundColor Yellow
Write-Host "   1. إنشاء جلسة تلاوة جديدة" -ForegroundColor White
Write-Host "   2. الحصول على معرف الجلسة" -ForegroundColor White
Write-Host "   3. إضافة أخطاء لهذه الجلسة" -ForegroundColor White
Write-Host ""

# =============================================================================
# الخطوة الأولى: إنشاء جلسة تلاوة جديدة
# =============================================================================
Write-Host "1️⃣ الخطوة الأولى: إنشاء جلسة تلاوة جديدة..." -ForegroundColor Green
Write-Host "   📡 إرسال طلب POST إلى: $baseUrl/recitation/sessions" -ForegroundColor Gray

$sessionData = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    session_date = (Get-Date -Format "yyyy-MM-dd")
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "جلسة تجريبية للعرض التوضيحي - $(Get-Date -Format 'HH:mm:ss')"
}

Write-Host "   📤 البيانات المرسلة:" -ForegroundColor Cyan
$sessionData | ConvertTo-Json -Depth 2 | Write-Host -ForegroundColor Gray

try {
    $sessionResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body ($sessionData | ConvertTo-Json -Depth 10) -Headers $headers
    
    Write-Host "   ✅ نجح إنشاء الجلسة!" -ForegroundColor Green
    Write-Host "   🆔 معرف الجلسة: $($sessionResponse.data.id)" -ForegroundColor Yellow
    Write-Host "   📋 رقم الجلسة: $($sessionResponse.data.session_code)" -ForegroundColor Yellow
    Write-Host "   👤 الطالب: $($sessionResponse.data.student.name)" -ForegroundColor Cyan
    Write-Host "   👨‍🏫 المعلم: $($sessionResponse.data.teacher.name)" -ForegroundColor Cyan
    Write-Host "   ⭐ التقييم: $($sessionResponse.data.evaluation)" -ForegroundColor Magenta
    
    $sessionId = $sessionResponse.data.id
    $sessionCode = $sessionResponse.data.session_code
} catch {
    Write-Host "   ❌ فشل في إنشاء الجلسة!" -ForegroundColor Red
    Write-Host "   خطأ: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Blue

# =============================================================================
# الخطوة الثانية: التحقق من الحصول على الجلسة
# =============================================================================
Write-Host "2️⃣ الخطوة الثانية: التحقق من إنشاء الجلسة..." -ForegroundColor Green
Write-Host "   📡 إرسال طلب GET إلى: $baseUrl/recitation/sessions" -ForegroundColor Gray

try {
    $sessionsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    
    Write-Host "   ✅ نجح جلب الجلسات!" -ForegroundColor Green
    Write-Host "   📊 إجمالي الجلسات: $($sessionsResponse.data.count)" -ForegroundColor Yellow
    
    # البحث عن الجلسة التي تم إنشاؤها
    $createdSession = $sessionsResponse.data.sessions | Where-Object { $_.id -eq $sessionId }
    
    if ($createdSession) {
        Write-Host "   🎯 تم العثور على الجلسة المُنشأة:" -ForegroundColor Green
        Write-Host "     🆔 ID: $($createdSession.id)" -ForegroundColor Yellow
        Write-Host "     📋 الكود: $($createdSession.session_code)" -ForegroundColor Yellow
        Write-Host "     📚 النوع: $($createdSession.recitation_type)" -ForegroundColor Cyan
        Write-Host "     📝 الملاحظات: $($createdSession.notes)" -ForegroundColor Gray
    } else {
        Write-Host "   ⚠️ لم يتم العثور على الجلسة في قائمة الجلسات!" -ForegroundColor Yellow
    }
    
} catch {
    Write-Host "   ❌ فشل في جلب الجلسات!" -ForegroundColor Red
    Write-Host "   خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Blue

# =============================================================================
# الخطوة الثالثة: إضافة أخطاء للجلسة
# =============================================================================
Write-Host "3️⃣ الخطوة الثالثة: إضافة أخطاء للجلسة $sessionCode..." -ForegroundColor Green
Write-Host "   📡 إرسال طلب POST إلى: $baseUrl/recitation/errors" -ForegroundColor Gray

$errorsData = @{
    session_id = $sessionId
    errors = @(
        @{
            surah_number = 1
            verse_number = 2
            word_position = "الرحمن"
            error_type = "تجويد"
            severity = "متوسط"
            is_recurring = $true
            correction_note = "عدم مد الألف في 'الرحمن' بشكل صحيح"
        },
        @{
            surah_number = 1
            verse_number = 3
            word_position = "الرحيم"
            error_type = "نطق"
            severity = "خفيف"
            is_recurring = $false
            correction_note = "نطق الحاء غير واضح"
        }
    )
}

Write-Host "   📤 أخطاء سيتم إضافتها: $($errorsData.errors.Count)" -ForegroundColor Cyan

try {
    $errorsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body ($errorsData | ConvertTo-Json -Depth 10) -Headers $headers
    
    Write-Host "   ✅ نجح إضافة الأخطاء!" -ForegroundColor Green
    Write-Host "   📊 عدد الأخطاء المضافة: $($errorsResponse.data.added_count)" -ForegroundColor Yellow
    
    if ($errorsResponse.data.errors) {
        Write-Host "   📋 تفاصيل الأخطاء المضافة:" -ForegroundColor Cyan
        foreach ($error in $errorsResponse.data.errors) {
            Write-Host "     🔸 سورة $($error.surah_number) آية $($error.verse_number): $($error.error_type) ($($error.severity))" -ForegroundColor White
            Write-Host "       📝 التصحيح: $($error.correction_note)" -ForegroundColor Gray
        }
    }
    
} catch {
    Write-Host "   ❌ فشل في إضافة الأخطاء!" -ForegroundColor Red
    Write-Host "   خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor Blue
Write-Host "🎉 انتهى العرض التوضيحي بنجاح!" -ForegroundColor Green
Write-Host "📋 ملخص العملية:" -ForegroundColor Yellow
Write-Host "   ✅ تم إنشاء جلسة جديدة برقم: $sessionCode" -ForegroundColor White
Write-Host "   ✅ تم الحصول على معرف الجلسة: $sessionId" -ForegroundColor White
Write-Host "   ✅ تم إضافة أخطاء للجلسة بنجاح" -ForegroundColor White
Write-Host ""
Write-Host "🔗 الروابط المستخدمة:" -ForegroundColor Magenta
Write-Host "   POST $baseUrl/recitation/sessions" -ForegroundColor Gray
Write-Host "   GET  $baseUrl/recitation/sessions" -ForegroundColor Gray
Write-Host "   POST $baseUrl/recitation/errors" -ForegroundColor Gray
Write-Host ""
