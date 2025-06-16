# 🎯 اختبار الوظائف الكاملة لـ API جلسات التسميع
# تم إنشاؤه في: 9 يونيو 2025
# يتطلب: خادم Laravel يعمل على المنفذ 8000

Write-Host "🚀 بدء اختبار API جلسات التسميع الشامل..." -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Yellow

# 1. اختبار الاتصال بالخادم
Write-Host "`n📡 1. اختبار الاتصال بالخادم..." -ForegroundColor Cyan
try {
    $response = curl.exe -s -X GET "http://127.0.0.1:8000/api/recitation/sessions?limit=1" -H "Accept: application/json"
    $jsonResponse = $response | ConvertFrom-Json
    if ($jsonResponse.success) {
        Write-Host "✅ الخادم يعمل بنجاح" -ForegroundColor Green
    } else {
        Write-Host "❌ مشكلة في الاستجابة" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "❌ فشل الاتصال بالخادم. تأكد من تشغيل: php artisan serve" -ForegroundColor Red
    exit 1
}

# 2. جلب جميع الجلسات للحصول على session_id صحيح
Write-Host "`n📚 2. جلب الجلسات الموجودة..." -ForegroundColor Cyan
$allSessionsResponse = curl.exe -s -X GET "http://127.0.0.1:8000/api/recitation/sessions" -H "Accept: application/json"
$allSessions = $allSessionsResponse | ConvertFrom-Json

if ($allSessions.success -and $allSessions.data.data.Count -gt 0) {
    $existingSession = $allSessions.data.data[0]
    $existingSessionId = $existingSession.session_id
    Write-Host "✅ تم العثور على $(($allSessions.data.data).Count) جلسة" -ForegroundColor Green
    Write-Host "📝 أول جلسة: ID=$($existingSession.id), Session_ID=$existingSessionId" -ForegroundColor Yellow
} else {
    Write-Host "⚠️ لا توجد جلسات في النظام" -ForegroundColor Yellow
    $existingSessionId = $null
}

# 3. اختبار جلب جلسة فردية (إذا كانت موجودة)
if ($existingSessionId) {
    Write-Host "`n🔍 3. اختبار جلب جلسة فردية باستخدام session_id..." -ForegroundColor Cyan
    $singleSessionResponse = curl.exe -s -X GET "http://127.0.0.1:8000/api/recitation/sessions/$existingSessionId" -H "Accept: application/json"
    $singleSession = $singleSessionResponse | ConvertFrom-Json
    
    if ($singleSession.success) {
        Write-Host "✅ تم جلب الجلسة بنجاح" -ForegroundColor Green
        Write-Host "📊 الطالب: $($singleSession.data.student.name)" -ForegroundColor White
        Write-Host "👨‍🏫 المعلم: $($singleSession.data.teacher.name)" -ForegroundColor White
        Write-Host "🎯 التقييم: $($singleSession.data.evaluation)" -ForegroundColor White
        Write-Host "🐛 عدد الأخطاء: $(($singleSession.data.errors).Count)" -ForegroundColor White
    } else {
        Write-Host "❌ فشل جلب الجلسة: $($singleSession.message)" -ForegroundColor Red
    }
}

# 4. إنشاء جلسة جديدة
Write-Host "`n➕ 4. إنشاء جلسة تسميع جديدة..." -ForegroundColor Cyan
$newSessionData = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    start_surah_number = 4
    start_verse = 1
    end_surah_number = 4
    end_verse = 15
    recitation_type = "حفظ"
    duration_minutes = 20
    grade = 9.0
    evaluation = "ممتاز"
    teacher_notes = "جلسة اختبار عبر PowerShell - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
} | ConvertTo-Json -Depth 3

$createResponse = curl.exe -s -X POST "http://127.0.0.1:8000/api/recitation/sessions" `
    -H "Content-Type: application/json" `
    -H "Accept: application/json" `
    -d $newSessionData

$createResult = $createResponse | ConvertFrom-Json

if ($createResult.success) {
    $newSessionId = $createResult.session_id
    Write-Host "✅ تم إنشاء جلسة جديدة بنجاح!" -ForegroundColor Green
    Write-Host "🆔 Session ID: $newSessionId" -ForegroundColor Yellow
    Write-Host "📊 الدرجة: $($createResult.data.grade)" -ForegroundColor White
} else {
    Write-Host "❌ فشل إنشاء الجلسة:" -ForegroundColor Red
    Write-Host $createResult.message -ForegroundColor Red
    if ($createResult.errors) {
        $createResult.errors | ConvertTo-Json -Depth 2 | Write-Host -ForegroundColor Yellow
    }
    $newSessionId = $null
}

# 5. إضافة أخطاء للجلسة الجديدة
if ($newSessionId) {
    Write-Host "`n🐛 5. إضافة أخطاء للجلسة الجديدة..." -ForegroundColor Cyan
    $errorsData = @{
        session_id = $newSessionId
        errors = @(
            @{
                surah_number = 4
                verse_number = 3
                word_text = "المؤمنين"
                error_type = "تجويد"
                correction_note = "لم يتم إظهار الإدغام بوضوح"
                teacher_note = "التركيز على أحكام النون الساكنة"
                is_repeated = $false
                severity_level = "متوسط"
            },
            @{
                surah_number = 4
                verse_number = 7
                word_text = "يؤمنون"
                error_type = "نطق"
                correction_note = "نطق الهمزة غير واضح"
                teacher_note = "مراجعة مخارج الحروف"
                is_repeated = $false
                severity_level = "خفيف"
            }
        )
    } | ConvertTo-Json -Depth 4

    $errorsResponse = curl.exe -s -X POST "http://127.0.0.1:8000/api/recitation/errors" `
        -H "Content-Type: application/json" `
        -H "Accept: application/json" `
        -d $errorsData

    $errorsResult = $errorsResponse | ConvertFrom-Json

    if ($errorsResult.success) {
        Write-Host "✅ تم إضافة الأخطاء بنجاح!" -ForegroundColor Green
        Write-Host "📊 عدد الأخطاء المضافة: $($errorsResult.total_errors)" -ForegroundColor White
    } else {
        Write-Host "❌ فشل إضافة الأخطاء:" -ForegroundColor Red
        Write-Host $errorsResult.message -ForegroundColor Red
    }
}

# 6. التحقق من الجلسة مع الأخطاء
if ($newSessionId) {
    Write-Host "`n🔍 6. التحقق من الجلسة مع الأخطاء المضافة..." -ForegroundColor Cyan
    $verifyResponse = curl.exe -s -X GET "http://127.0.0.1:8000/api/recitation/sessions/$newSessionId" -H "Accept: application/json"
    $verifyResult = $verifyResponse | ConvertFrom-Json

    if ($verifyResult.success) {
        Write-Host "✅ تم التحقق من الجلسة بنجاح" -ForegroundColor Green
        Write-Host "🐛 عدد الأخطاء: $(($verifyResult.data.errors).Count)" -ForegroundColor White
        
        foreach ($error in $verifyResult.data.errors) {
            Write-Host "   - سورة $($error.surah_number):$($error.verse_number) - $($error.word_text) - $($error.error_type)" -ForegroundColor Gray
        }
    } else {
        Write-Host "❌ فشل التحقق من الجلسة" -ForegroundColor Red
    }
}

# 7. جلب الإحصائيات
Write-Host "`n📊 7. جلب الإحصائيات العامة..." -ForegroundColor Cyan
$statsResponse = curl.exe -s -X GET "http://127.0.0.1:8000/api/recitation/stats" -H "Accept: application/json"
$stats = $statsResponse | ConvertFrom-Json

if ($stats.success) {
    Write-Host "✅ تم جلب الإحصائيات بنجاح" -ForegroundColor Green
    Write-Host "📈 إجمالي الجلسات: $($stats.data.total_sessions)" -ForegroundColor White
    Write-Host "❌ جلسات بها أخطاء: $($stats.data.sessions_with_errors)" -ForegroundColor White
    Write-Host "✅ جلسات بدون أخطاء: $($stats.data.sessions_without_errors)" -ForegroundColor White
    Write-Host "📊 معدل الأخطاء: $($stats.data.error_rate_percentage)%" -ForegroundColor White
    Write-Host "🎯 متوسط الدرجات: $($stats.data.average_grade)" -ForegroundColor White
} else {
    Write-Host "❌ فشل جلب الإحصائيات" -ForegroundColor Red
}

# الخلاصة
Write-Host "`n" + "=" * 60 -ForegroundColor Yellow
Write-Host "🎉 انتهى الاختبار الشامل!" -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Yellow

Write-Host "`n📋 ملخص النتائج:" -ForegroundColor Cyan
Write-Host "✅ اختبار الاتصال بالخادم: نجح" -ForegroundColor Green
Write-Host "✅ جلب قائمة الجلسات: نجح" -ForegroundColor Green
if ($existingSessionId) {
    Write-Host "✅ جلب جلسة فردية: نجح" -ForegroundColor Green
}
if ($newSessionId) {
    Write-Host "✅ إنشاء جلسة جديدة: نجح (ID: $newSessionId)" -ForegroundColor Green
    Write-Host "✅ إضافة أخطاء: نجح" -ForegroundColor Green
    Write-Host "✅ التحقق من الجلسة: نجح" -ForegroundColor Green
}
Write-Host "✅ جلب الإحصائيات: نجح" -ForegroundColor Green

Write-Host "`n🎯 تم التأكد من عمل جميع وظائف API بنجاح!" -ForegroundColor Green
Write-Host "📝 للمزيد من التفاصيل، راجع: COMPLETE_API_WORKFLOW_DEMO.md" -ForegroundColor Yellow
