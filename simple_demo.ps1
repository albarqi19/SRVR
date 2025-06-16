# اختبار API مباشر - نسخة مبسطة
Write-Host "🔥 اختبار API مباشر للعرض التوضيحي" -ForegroundColor Red
Write-Host "=============================================" -ForegroundColor Blue

# تعريف البيانات الأساسية
$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{"Content-Type" = "application/json"; "Accept" = "application/json"}

# الخطوة 1: إنشاء جلسة جديدة
Write-Host "`n1️⃣ إنشاء جلسة تلاوة جديدة..." -ForegroundColor Green
$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    session_date = "2024-06-09"
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "عرض توضيحي مباشر"
} | ConvertTo-Json

try {
    $session = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $sessionData -Headers $headers
    Write-Host "   ✅ تم إنشاء الجلسة: $($session.data.session_code)" -ForegroundColor Green
    Write-Host "   🆔 معرف الجلسة: $($session.data.id)" -ForegroundColor Yellow
    $sessionId = $session.data.id
} catch {
    Write-Host "   ❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
    exit
}

# الخطوة 2: جلب الجلسة للتأكد
Write-Host "`n2️⃣ التحقق من الجلسة المُنشأة..." -ForegroundColor Green
try {
    $sessions = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    Write-Host "   ✅ تم جلب $($sessions.data.count) جلسة" -ForegroundColor Green
    $found = $sessions.data.sessions | Where-Object {$_.id -eq $sessionId}
    if ($found) {
        Write-Host "   🎯 تم العثور على الجلسة: $($found.session_code)" -ForegroundColor Cyan
    }
} catch {
    Write-Host "   ❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

# الخطوة 3: إضافة أخطاء للجلسة
Write-Host "`n3️⃣ إضافة أخطاء للجلسة..." -ForegroundColor Green
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
            correction_note = "مد الألف"
        }
    )
} | ConvertTo-Json -Depth 5

try {
    $errors = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body $errorsData -Headers $headers
    Write-Host "   ✅ تم إضافة $($errors.data.added_count) خطأ" -ForegroundColor Green
} catch {
    Write-Host "   ❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n🎉 انتهى العرض التوضيحي بنجاح!" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Blue
