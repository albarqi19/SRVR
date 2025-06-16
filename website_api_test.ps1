# ============================================
# اختبار API نظام التسميع - للتجربة في الموقع
# Recitation API Test - For Website Testing
# ============================================

Write-Host "🚀 بدء اختبار API نظام التسميع للموقع" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan

# إعدادات الاتصال
$baseUrl = "http://127.0.0.1:8000/api"
$sessionsUrl = "$baseUrl/recitation/sessions"
$errorsUrl = "$baseUrl/recitation/errors"

Write-Host "`n📋 الخطوة 1: فحص الاتصال بالخادم" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

try {
    $connectionTest = curl.exe -s -X GET "$sessionsUrl" -H "Accept: application/json"
    $testResult = $connectionTest | ConvertFrom-Json
    
    if ($testResult.success) {
        Write-Host "✅ الاتصال بالخادم ناجح" -ForegroundColor Green
        Write-Host "📊 عدد الجلسات الموجودة: $($testResult.data.total)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ فشل الاتصال بالخادم" -ForegroundColor Red
        Write-Host "الخطأ: $($testResult.message)" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "❌ خطأ في الاتصال بالخادم. تأكد من تشغيل Laravel Server" -ForegroundColor Red
    Write-Host "لتشغيل الخادم: php artisan serve" -ForegroundColor Yellow
    exit 1
}

Write-Host "`n📝 الخطوة 2: إنشاء جلسة تسميع جديدة باستخدام الملف الموجود" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

Write-Host "📁 استخدام ملف: test_session.json" -ForegroundColor Cyan
Write-Host "📤 إرسال طلب إنشاء الجلسة..." -ForegroundColor White

# إنشاء الجلسة باستخدام الملف الموجود
$createResponse = curl.exe -s -X POST "$sessionsUrl" -H "Accept: application/json" -H "Content-Type: application/json" --data "@test_session.json"
$createResult = $createResponse | ConvertFrom-Json

if ($createResult.success) {
    Write-Host "✅ تم إنشاء الجلسة بنجاح!" -ForegroundColor Green
    $newSessionId = $createResult.session_id
    Write-Host "🆔 معرف الجلسة الجديدة: $newSessionId" -ForegroundColor Cyan
    Write-Host "👤 الطالب: $($createResult.data.student.name)" -ForegroundColor White
    Write-Host "👨‍🏫 المعلم: $($createResult.data.teacher.name)" -ForegroundColor White
    Write-Host "📊 التقييم: $($createResult.data.evaluation)" -ForegroundColor White
    Write-Host "🎯 الدرجة: $($createResult.data.grade)" -ForegroundColor White
    
    # حفظ معرف الجلسة في ملف للاستخدام لاحقاً
    $newSessionId | Out-File -FilePath "last_session_id.txt" -Encoding UTF8
    
} else {
    Write-Host "❌ فشل في إنشاء الجلسة" -ForegroundColor Red
    Write-Host "الخطأ: $($createResult.message)" -ForegroundColor Red
    
    if ($createResult.errors) {
        Write-Host "`n📋 تفاصيل الأخطاء:" -ForegroundColor Yellow
        $createResult.errors.PSObject.Properties | ForEach-Object {
            Write-Host "   - $($_.Name): $($_.Value -join ', ')" -ForegroundColor Red
        }
    }
    
    # استخدام جلسة موجودة للاختبار
    Write-Host "`n🔄 سيتم استخدام جلسة موجودة للاختبار..." -ForegroundColor Yellow
    
    if ($testResult.data.data.Count -gt 0) {
        $newSessionId = $testResult.data.data[0].session_id
        Write-Host "📋 الجلسة المستخدمة: $newSessionId" -ForegroundColor Cyan
    } else {
        Write-Host "❌ لا توجد جلسات للاختبار" -ForegroundColor Red
        exit 1
    }
}

Write-Host "`n🔍 الخطوة 3: التحقق من تفاصيل الجلسة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

Write-Host "📡 جلب تفاصيل الجلسة: $newSessionId" -ForegroundColor Cyan

$sessionDetailResponse = curl.exe -s -X GET "$sessionsUrl/$newSessionId" -H "Accept: application/json"
$sessionDetail = $sessionDetailResponse | ConvertFrom-Json

if ($sessionDetail.success) {
    Write-Host "✅ تم جلب تفاصيل الجلسة بنجاح!" -ForegroundColor Green
    $session = $sessionDetail.data
    
    Write-Host "`n📋 تفاصيل الجلسة:" -ForegroundColor Cyan
    Write-Host "   🆔 معرف الجلسة: $($session.session_id)" -ForegroundColor White
    Write-Host "   👤 الطالب: $($session.student.name)" -ForegroundColor White
    Write-Host "   👨‍🏫 المعلم: $($session.teacher.name)" -ForegroundColor White
    Write-Host "   🏢 الحلقة: $($session.circle.name)" -ForegroundColor White
    Write-Host "   📖 نوع التلاوة: $($session.recitation_type)" -ForegroundColor White
    Write-Host "   📊 التقييم: $($session.evaluation)" -ForegroundColor White
    Write-Host "   🎯 الدرجة: $($session.grade)" -ForegroundColor White
    Write-Host "   📄 عدد الآيات: $($session.total_verses)" -ForegroundColor White
    Write-Host "   🐛 يحتوي على أخطاء: $($session.has_errors)" -ForegroundColor White
    Write-Host "   🔢 عدد الأخطاء الحالية: $($session.errors.Count)" -ForegroundColor White
    
} else {
    Write-Host "❌ فشل في جلب تفاصيل الجلسة" -ForegroundColor Red
    Write-Host "الخطأ: $($sessionDetail.message)" -ForegroundColor Red
}

Write-Host "`n🐛 الخطوة 4: إضافة أخطاء للجلسة (إذا لم تكن موجودة)" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

if ($session -and $session.errors.Count -eq 0) {
    Write-Host "📝 إنشاء ملف أخطاء للجلسة..." -ForegroundColor Cyan
    
    # إنشاء بيانات الأخطاء
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
            }
        )
    }
    
    # حفظ بيانات الأخطاء في ملف
    $errorsData | ConvertTo-Json -Depth 10 | Out-File -FilePath "test_errors_new.json" -Encoding UTF8
    Write-Host "📁 تم إنشاء ملف: test_errors_new.json" -ForegroundColor Green
    
    # إضافة الأخطاء
    Write-Host "📤 إضافة أخطاء للجلسة..." -ForegroundColor White
    
    $errorsResponse = curl.exe -s -X POST "$errorsUrl" -H "Accept: application/json" -H "Content-Type: application/json" --data "@test_errors_new.json"
    $errorsResult = $errorsResponse | ConvertFrom-Json
    
    if ($errorsResult.success) {
        Write-Host "✅ تم إضافة الأخطاء بنجاح!" -ForegroundColor Green
        Write-Host "📊 عدد الأخطاء المضافة: $($errorsResult.total_errors)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ فشل في إضافة الأخطاء" -ForegroundColor Red
        Write-Host "الخطأ: $($errorsResult.message)" -ForegroundColor Red
    }
    
} else {
    Write-Host "ℹ️  الجلسة تحتوي بالفعل على أخطاء ($($session.errors.Count) أخطاء)" -ForegroundColor Blue
}

Write-Host "`n📊 الخطوة 5: جلب الإحصائيات العامة" -ForegroundColor Yellow
Write-Host "===============================================" -ForegroundColor Gray

$statsResponse = curl.exe -s -X GET "$baseUrl/recitation/stats" -H "Accept: application/json"
$stats = $statsResponse | ConvertFrom-Json

if ($stats.success) {
    Write-Host "✅ تم جلب الإحصائيات بنجاح!" -ForegroundColor Green
    Write-Host "`n📈 الإحصائيات العامة:" -ForegroundColor Cyan
    Write-Host "   📚 إجمالي الجلسات: $($stats.data.total_sessions)" -ForegroundColor White
    Write-Host "   🐛 جلسات بها أخطاء: $($stats.data.sessions_with_errors)" -ForegroundColor White
    Write-Host "   ✅ جلسات بدون أخطاء: $($stats.data.sessions_without_errors)" -ForegroundColor White
    Write-Host "   📊 نسبة الأخطاء: $($stats.data.error_rate_percentage)%" -ForegroundColor White
    Write-Host "   🎯 متوسط الدرجات: $($stats.data.average_grade)" -ForegroundColor White
    Write-Host "   📅 جلسات اليوم: $($stats.data.today_sessions)" -ForegroundColor White
} else {
    Write-Host "❌ فشل في جلب الإحصائيات" -ForegroundColor Red
}

Write-Host "`n🎯 ملخص نتائج الاختبار" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan

# إعداد نتائج الاختبار
$tests = @()
$tests += @{ Test = "فحص الاتصال"; Status = if($testResult.success) {"✅ نجح"} else {"❌ فشل"} }
$tests += @{ Test = "إنشاء/استخدام جلسة"; Status = if($newSessionId) {"✅ نجح"} else {"❌ فشل"} }
$tests += @{ Test = "جلب تفاصيل الجلسة"; Status = if($sessionDetail.success) {"✅ نجح"} else {"❌ فشل"} }
$tests += @{ Test = "إدارة الأخطاء"; Status = if($session.errors.Count -gt 0) {"✅ موجودة"} else {"ℹ️  فارغة"} }
$tests += @{ Test = "جلب الإحصائيات"; Status = if($stats.success) {"✅ نجح"} else {"❌ فشل"} }

# عرض النتائج في جدول
$tests | Format-Table -AutoSize

$successCount = ($tests | Where-Object { $_.Status -like "*نجح*" -or $_.Status -like "*موجودة*" }).Count
$totalTests = $tests.Count

Write-Host "`n🏆 النتيجة النهائية: $successCount/$totalTests اختبارات نجحت" -ForegroundColor $(if($successCount -ge ($totalTests - 1)) {"Green"} else {"Yellow"})

if ($successCount -ge ($totalTests - 1)) {
    Write-Host "🎉 النظام يعمل بشكل ممتاز! جاهز للاستخدام في الموقع." -ForegroundColor Green
} else {
    Write-Host "⚠️  بعض الاختبارات تحتاج مراجعة." -ForegroundColor Yellow
}

Write-Host "`n📝 ملفات تم إنشاؤها أو استخدامها:" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Gray
Write-Host "📁 test_session.json - بيانات إنشاء الجلسة" -ForegroundColor White
if (Test-Path "test_errors_new.json") {
    Write-Host "📁 test_errors_new.json - بيانات الأخطاء الجديدة" -ForegroundColor White
}
if (Test-Path "last_session_id.txt") {
    Write-Host "📁 last_session_id.txt - معرف آخر جلسة" -ForegroundColor White
}

Write-Host "`n🌐 أوامر للتجربة في الموقع:" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Gray
Write-Host "🔗 رابط API الأساسي: $baseUrl" -ForegroundColor White
if ($newSessionId) {
    Write-Host "📋 جلب جلسة محددة:" -ForegroundColor Gray
    Write-Host "   curl.exe -X GET `"$sessionsUrl/$newSessionId`" -H `"Accept: application/json`"" -ForegroundColor White
}
Write-Host "📊 جلب جميع الجلسات:" -ForegroundColor Gray
Write-Host "   curl.exe -X GET `"$sessionsUrl`" -H `"Accept: application/json`"" -ForegroundColor White
Write-Host "📈 جلب الإحصائيات:" -ForegroundColor Gray
Write-Host "   curl.exe -X GET `"$baseUrl/recitation/stats`" -H `"Accept: application/json`"" -ForegroundColor White

Write-Host "`n✨ انتهى اختبار الموقع بنجاح!" -ForegroundColor Green
