# ============================================
# اختبار شامل لـ API باستخدام cURL
# Complete API Test using cURL
# ============================================

Write-Host "🚀 بدء الاختبار الشامل لـ API نظام التسميع" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan

# تنظيف الشاشة والإعداد
$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host "`n📋 الخطوة 1: جلب قائمة الجلسات الموجودة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

$sessionsResponse = curl.exe -s -X GET "$baseUrl/recitation/sessions" -H "Accept: application/json"
$sessions = $sessionsResponse | ConvertFrom-Json

if ($sessions.success) {
    Write-Host "✅ تم جلب الجلسات بنجاح" -ForegroundColor Green
    Write-Host "📊 العدد الإجمالي: $($sessions.data.total) جلسة" -ForegroundColor White
    
    if ($sessions.data.data.Count -gt 0) {
        $firstSession = $sessions.data.data[0]
        Write-Host "🔍 أحدث جلسة:" -ForegroundColor Cyan
        Write-Host "   - معرف الجلسة: $($firstSession.session_id)" -ForegroundColor White
        Write-Host "   - الطالب: $($firstSession.student.name)" -ForegroundColor White
        Write-Host "   - المعلم: $($firstSession.teacher.name)" -ForegroundColor White
        Write-Host "   - التقييم: $($firstSession.evaluation)" -ForegroundColor White
        Write-Host "   - الدرجة: $($firstSession.grade)" -ForegroundColor White
        
        $existingSessionId = $firstSession.session_id
    }
} else {
    Write-Host "❌ فشل في جلب الجلسات" -ForegroundColor Red
    Write-Host "الخطأ: $($sessions.message)" -ForegroundColor Red
    exit 1
}

Write-Host "`n📝 الخطوة 2: إنشاء جلسة تسميع جديدة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

# بيانات جلسة جديدة
$newSessionData = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    start_surah_number = 2
    start_verse = 1
    end_surah_number = 2
    end_verse = 10
    recitation_type = "مراجعة صغرى"
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "جلسة تجريبية شاملة عبر cURL API"
} | ConvertTo-Json -Depth 10

Write-Host "📤 إرسال بيانات الجلسة الجديدة..." -ForegroundColor Cyan

$createResponse = curl.exe -s -X POST "$baseUrl/recitation/sessions" `
    -H "Accept: application/json" `
    -H "Content-Type: application/json" `
    -d $newSessionData

$createResult = $createResponse | ConvertFrom-Json

if ($createResult.success) {
    Write-Host "✅ تم إنشاء الجلسة بنجاح!" -ForegroundColor Green
    $newSessionId = $createResult.session_id
    Write-Host "🆔 معرف الجلسة الجديدة: $newSessionId" -ForegroundColor Cyan
    Write-Host "👤 الطالب: $($createResult.data.student.name)" -ForegroundColor White
    Write-Host "👨‍🏫 المعلم: $($createResult.data.teacher.name)" -ForegroundColor White
    Write-Host "📊 التقييم: $($createResult.data.evaluation)" -ForegroundColor White
    Write-Host "🎯 الدرجة: $($createResult.data.grade)" -ForegroundColor White
} else {
    Write-Host "❌ فشل في إنشاء الجلسة" -ForegroundColor Red
    Write-Host "الخطأ: $($createResult.message)" -ForegroundColor Red
    if ($createResult.errors) {
        Write-Host "تفاصيل الأخطاء:" -ForegroundColor Yellow
        $createResult.errors | ConvertTo-Json -Depth 5 | Write-Host -ForegroundColor Red
    }
    $newSessionId = $existingSessionId  # استخدام جلسة موجودة للاختبار
    Write-Host "🔄 سيتم استخدام جلسة موجودة للاختبار: $newSessionId" -ForegroundColor Yellow
}

Write-Host "`n🔍 الخطوة 3: استرجاع تفاصيل الجلسة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

Write-Host "📡 جلب تفاصيل الجلسة: $newSessionId" -ForegroundColor Cyan

$sessionDetailResponse = curl.exe -s -X GET "$baseUrl/recitation/sessions/$newSessionId" -H "Accept: application/json"
$sessionDetail = $sessionDetailResponse | ConvertFrom-Json

if ($sessionDetail.success) {
    Write-Host "✅ تم جلب تفاصيل الجلسة بنجاح!" -ForegroundColor Green
    $session = $sessionDetail.data
    Write-Host "📋 تفاصيل الجلسة:" -ForegroundColor Cyan
    Write-Host "   - معرف الجلسة: $($session.session_id)" -ForegroundColor White
    Write-Host "   - الطالب: $($session.student.name)" -ForegroundColor White
    Write-Host "   - المعلم: $($session.teacher.name)" -ForegroundColor White
    Write-Host "   - الحلقة: $($session.circle.name)" -ForegroundColor White
    Write-Host "   - نوع التلاوة: $($session.recitation_type)" -ForegroundColor White
    Write-Host "   - التقييم: $($session.evaluation)" -ForegroundColor White
    Write-Host "   - الدرجة: $($session.grade)" -ForegroundColor White
    Write-Host "   - عدد الآيات: $($session.total_verses)" -ForegroundColor White
    Write-Host "   - يحتوي على أخطاء: $($session.has_errors)" -ForegroundColor White
    Write-Host "   - عدد الأخطاء الحالية: $($session.errors.Count)" -ForegroundColor White
} else {
    Write-Host "❌ فشل في جلب تفاصيل الجلسة" -ForegroundColor Red
    Write-Host "الخطأ: $($sessionDetail.message)" -ForegroundColor Red
}

Write-Host "`n🐛 الخطوة 4: إضافة أخطاء للجلسة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

# بيانات الأخطاء
$errorsData = @{
    session_id = $newSessionId
    errors = @(
        @{
            surah_number = 2
            verse_number = 5
            word_text = "الذين"
            error_type = "تجويد"
            correction_note = "عدم تطبيق القلقلة بشكل صحيح"
            teacher_note = "يحتاج تدريب على أحكام القلقلة"
            is_repeated = $false
            severity_level = "متوسط"
        },
        @{
            surah_number = 2
            verse_number = 7
            word_text = "ختم"
            error_type = "نطق"
            correction_note = "نطق الخاء غير واضح"
            teacher_note = "تدريب على مخارج الحروف"
            is_repeated = $true
            severity_level = "خفيف"
        },
        @{
            surah_number = 2
            verse_number = 10
            word_text = "يخادعون"
            error_type = "ترتيل"
            correction_note = "سرعة في القراءة"
            teacher_note = "الالتزام بقواعد الترتيل"
            is_repeated = $false
            severity_level = "شديد"
        }
    )
} | ConvertTo-Json -Depth 10

Write-Host "📤 إضافة 3 أخطاء للجلسة..." -ForegroundColor Cyan

$errorsResponse = curl.exe -s -X POST "$baseUrl/recitation/errors" `
    -H "Accept: application/json" `
    -H "Content-Type: application/json" `
    -d $errorsData

$errorsResult = $errorsResponse | ConvertFrom-Json

if ($errorsResult.success) {
    Write-Host "✅ تم إضافة الأخطاء بنجاح!" -ForegroundColor Green
    Write-Host "📊 عدد الأخطاء المضافة: $($errorsResult.total_errors)" -ForegroundColor Cyan
    Write-Host "🔄 تم تحديث الجلسة: $($errorsResult.session_updated)" -ForegroundColor White
} else {
    Write-Host "❌ فشل في إضافة الأخطاء" -ForegroundColor Red
    Write-Host "الخطأ: $($errorsResult.message)" -ForegroundColor Red
    if ($errorsResult.errors) {
        Write-Host "تفاصيل الأخطاء:" -ForegroundColor Yellow
        $errorsResult.errors | ConvertTo-Json -Depth 5 | Write-Host -ForegroundColor Red
    }
}

Write-Host "`n🔍 الخطوة 5: التحقق من الأخطاء المضافة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

$sessionAfterErrorsResponse = curl.exe -s -X GET "$baseUrl/recitation/sessions/$newSessionId" -H "Accept: application/json"
$sessionAfterErrors = $sessionAfterErrorsResponse | ConvertFrom-Json

if ($sessionAfterErrors.success) {
    $updatedSession = $sessionAfterErrors.data
    Write-Host "✅ تم جلب الجلسة المحدثة بنجاح!" -ForegroundColor Green
    Write-Host "📊 حالة الجلسة بعد إضافة الأخطاء:" -ForegroundColor Cyan
    Write-Host "   - يحتوي على أخطاء: $($updatedSession.has_errors)" -ForegroundColor White
    Write-Host "   - عدد الأخطاء: $($updatedSession.errors.Count)" -ForegroundColor White
    
    if ($updatedSession.errors.Count -gt 0) {
        Write-Host "`n🐛 تفاصيل الأخطاء:" -ForegroundColor Cyan
        for ($i = 0; $i -lt $updatedSession.errors.Count; $i++) {
            $error = $updatedSession.errors[$i]
            Write-Host "   خطأ $($i + 1):" -ForegroundColor Yellow
            Write-Host "      - السورة: $($error.surah_number), الآية: $($error.verse_number)" -ForegroundColor White
            Write-Host "      - الكلمة: $($error.word_text)" -ForegroundColor White
            Write-Host "      - نوع الخطأ: $($error.error_type)" -ForegroundColor White
            Write-Host "      - مستوى الشدة: $($error.severity_level)" -ForegroundColor White
            Write-Host "      - متكرر: $($error.is_repeated)" -ForegroundColor White
            Write-Host "      - ملاحظة التصحيح: $($error.correction_note)" -ForegroundColor Gray
        }
    }
} else {
    Write-Host "❌ فشل في جلب الجلسة المحدثة" -ForegroundColor Red
}

Write-Host "`n📊 الخطوة 6: جلب إحصائيات عامة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

$statsResponse = curl.exe -s -X GET "$baseUrl/recitation/stats" -H "Accept: application/json"
$stats = $statsResponse | ConvertFrom-Json

if ($stats.success) {
    Write-Host "✅ تم جلب الإحصائيات بنجاح!" -ForegroundColor Green
    Write-Host "📈 الإحصائيات العامة:" -ForegroundColor Cyan
    Write-Host "   - إجمالي الجلسات: $($stats.data.total_sessions)" -ForegroundColor White
    Write-Host "   - جلسات بها أخطاء: $($stats.data.sessions_with_errors)" -ForegroundColor White
    Write-Host "   - جلسات بدون أخطاء: $($stats.data.sessions_without_errors)" -ForegroundColor White
    Write-Host "   - نسبة الأخطاء: $($stats.data.error_rate_percentage)%" -ForegroundColor White
    Write-Host "   - متوسط الدرجات: $($stats.data.average_grade)" -ForegroundColor White
    Write-Host "   - جلسات اليوم: $($stats.data.today_sessions)" -ForegroundColor White
} else {
    Write-Host "❌ فشل في جلب الإحصائيات" -ForegroundColor Red
    Write-Host "الخطأ: $($stats.message)" -ForegroundColor Red
}

Write-Host "`n🎯 ملخص نتائج الاختبار" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan

$testResults = @(
    @{ Test = "جلب قائمة الجلسات"; Status = if($sessions.success) {"✅ نجح"} else {"❌ فشل"} },
    @{ Test = "إنشاء جلسة جديدة"; Status = if($createResult.success) {"✅ نجح"} else {"❌ فشل"} },
    @{ Test = "جلب تفاصيل جلسة"; Status = if($sessionDetail.success) {"✅ نجح"} else {"❌ فشل"} },
    @{ Test = "إضافة أخطاء"; Status = if($errorsResult.success) {"✅ نجح"} else {"❌ فشل"} },
    @{ Test = "التحقق من الأخطاء"; Status = if($sessionAfterErrors.success) {"✅ نجح"} else {"❌ فشل"} },
    @{ Test = "جلب الإحصائيات"; Status = if($stats.success) {"✅ نجح"} else {"❌ فشل"} }
)

$testResults | Format-Table -AutoSize

$successCount = ($testResults | Where-Object { $_.Status -like "*نجح*" }).Count
$totalTests = $testResults.Count

Write-Host "`n🏆 النتيجة النهائية: $successCount/$totalTests اختبارات نجحت" -ForegroundColor $(if($successCount -eq $totalTests) {"Green"} else {"Yellow"})

if ($successCount -eq $totalTests) {
    Write-Host "🎉 جميع الاختبارات نجحت! النظام يعمل بشكل مثالي." -ForegroundColor Green
} else {
    Write-Host "⚠️  بعض الاختبارات فشلت. يرجى مراجعة الأخطاء أعلاه." -ForegroundColor Yellow
}

Write-Host "`n📝 معلومات مفيدة للاستخدام:" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Gray
Write-Host "🔗 رابط API الأساسي: $baseUrl" -ForegroundColor White
if ($newSessionId) {
    Write-Host "🆔 معرف الجلسة الجديدة: $newSessionId" -ForegroundColor White
    Write-Host "📋 لجلب تفاصيل هذه الجلسة: curl.exe -X GET `"$baseUrl/recitation/sessions/$newSessionId`"" -ForegroundColor Gray
}
Write-Host "📊 لجلب جميع الجلسات: curl.exe -X GET `"$baseUrl/recitation/sessions`"" -ForegroundColor Gray
Write-Host "📈 لجلب الإحصائيات: curl.exe -X GET `"$baseUrl/recitation/stats`"" -ForegroundColor Gray

Write-Host "`n✨ انتهى الاختبار الشامل!" -ForegroundColor Green
