# دليل اختبار API نظام جلسات التسميع والأخطاء - أمثلة PowerShell

Write-Host "🚀 دليل اختبار API نظام جلسات التسميع والأخطاء" -ForegroundColor Yellow
Write-Host "=====================================================" -ForegroundColor Yellow
Write-Host ""

# إعداد المتغيرات الأساسية
$baseUrl = "http://localhost:8000/api"
$headers = @{'Content-Type' = 'application/json'}

Write-Host "📋 الإعدادات الأساسية:" -ForegroundColor Cyan
Write-Host "Base URL: $baseUrl" -ForegroundColor White
Write-Host "Content-Type: application/json" -ForegroundColor White
Write-Host ""

# ==========================================
# 1. اختبار إنشاء جلسة تسميع جديدة
# ==========================================

Write-Host "📝 1. اختبار إنشاء جلسة تسميع جديدة" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green

$sessionData = @{
    student_id = 1
    teacher_id = 2
    quran_circle_id = 1
    start_surah_number = 2
    start_verse = 1
    end_surah_number = 2
    end_verse = 5
    recitation_type = "مراجعة صغرى"
    grade = 7.5
    evaluation = "جيد جداً"
    teacher_notes = "جلسة تجريبية عبر PowerShell API"
} | ConvertTo-Json -Depth 3

Write-Host "📤 البيانات المرسلة:" -ForegroundColor Cyan
Write-Host $sessionData -ForegroundColor White

Write-Host "`n🔄 إرسال الطلب..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Headers $headers -Body $sessionData
    Write-Host "✅ نجح إنشاء الجلسة!" -ForegroundColor Green
    Write-Host "📥 الاستجابة:" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 3
    
    # حفظ معرف الجلسة للاستخدام لاحقاً
    $global:sessionId = $response.data.session_id
    Write-Host "`n📋 معرف الجلسة المحفوظ: $global:sessionId" -ForegroundColor Magenta
    
} catch {
    Write-Host "❌ فشل إنشاء الجلسة!" -ForegroundColor Red
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.ErrorDetails.Message) {
        Write-Host "`n📋 تفاصيل الخطأ:" -ForegroundColor Yellow
        $errorData = $_.ErrorDetails.Message | ConvertFrom-Json
        $errorData | ConvertTo-Json -Depth 3
    }
}

Write-Host "`n" + "="*50 + "`n"

# ==========================================
# 2. اختبار جلب الجلسات
# ==========================================

Write-Host "📚 2. اختبار جلب جلسات التسميع" -ForegroundColor Green
Write-Host "==============================" -ForegroundColor Green

$queryParams = "?limit=5&student_id=1"
Write-Host "📤 معاملات الاستعلام: $queryParams" -ForegroundColor Cyan

Write-Host "`n🔄 إرسال الطلب..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions$queryParams" -Method GET -Headers $headers
    Write-Host "✅ نجح جلب الجلسات!" -ForegroundColor Green
    Write-Host "📥 عدد الجلسات المجلبة: $($response.data.data.Count)" -ForegroundColor Green
    Write-Host "الاستجابة:" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 3
    
} catch {
    Write-Host "❌ فشل جلب الجلسات!" -ForegroundColor Red
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.ErrorDetails.Message) {
        Write-Host "`n📋 تفاصيل الخطأ:" -ForegroundColor Yellow
        $errorData = $_.ErrorDetails.Message | ConvertFrom-Json
        $errorData | ConvertTo-Json -Depth 3
    }
}

Write-Host "`n" + "="*50 + "`n"

# ==========================================
# 3. اختبار إضافة أخطاء التلاوة
# ==========================================

Write-Host "🐛 3. اختبار إضافة أخطاء التلاوة" -ForegroundColor Green
Write-Host "===============================" -ForegroundColor Green

# التأكد من وجود معرف جلسة
if ($global:sessionId) {
    $errorData = @{
        session_id = $global:sessionId
        errors = @(
            @{
                surah_number = 2
                verse_number = 10
                word_text = "يخادعون"
                error_type = "نطق"
                correction_note = "نطق الخاء غير صحيح"
                teacher_note = "تدريب على الحروف الحلقية"
                is_repeated = $false
                severity_level = "متوسط"
            }
        )
    } | ConvertTo-Json -Depth 3

    Write-Host "📤 البيانات المرسلة:" -ForegroundColor Cyan
    Write-Host $errorData -ForegroundColor White

    Write-Host "`n🔄 إرسال الطلب..." -ForegroundColor Yellow

    try {
        $response = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Headers $headers -Body $errorData
        Write-Host "✅ نجح إضافة الأخطاء!" -ForegroundColor Green
        Write-Host "📥 الاستجابة:" -ForegroundColor Green
        $response | ConvertTo-Json -Depth 3
        
    } catch {
        Write-Host "❌ فشل إضافة الأخطاء!" -ForegroundColor Red
        Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
        
        if ($_.ErrorDetails.Message) {
            Write-Host "`n📋 تفاصيل الخطأ:" -ForegroundColor Yellow
            $errorData = $_.ErrorDetails.Message | ConvertFrom-Json
            $errorData | ConvertTo-Json -Depth 3
        }
    }
} else {
    Write-Host "⚠️ لا يوجد معرف جلسة متاح. قم بإنشاء جلسة أولاً." -ForegroundColor Yellow
}

Write-Host "`n" + "="*50 + "`n"

# ==========================================
# 4. اختبار مع بيانات خاطئة (لرؤية الأخطاء)
# ==========================================

Write-Host "❌ 4. اختبار مع بيانات خاطئة" -ForegroundColor Red
Write-Host "============================" -ForegroundColor Red

$invalidSessionData = @{
    student_id = 1
    teacher_id = 2
    # quran_circle_id مفقود - مطلوب
    start_surah_number = 2
    start_verse = 1
    end_surah_number = 2
    end_verse = 5
    recitation_type = "نوع غير صحيح"  # قيمة غير صالحة
    grade = 7.5
    # evaluation مفقود - مطلوب
    teacher_notes = "اختبار مع بيانات خاطئة"
} | ConvertTo-Json -Depth 3

Write-Host "📤 البيانات الخاطئة المرسلة:" -ForegroundColor Cyan
Write-Host $invalidSessionData -ForegroundColor White

Write-Host "`n🔄 إرسال الطلب (متوقع أن يفشل)..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Headers $headers -Body $invalidSessionData
    Write-Host "⚠️ الطلب نجح بشكل غير متوقع!" -ForegroundColor Yellow
    $response | ConvertTo-Json -Depth 3
    
} catch {
    Write-Host "✅ فشل الطلب كما هو متوقع (HTTP 422)!" -ForegroundColor Green
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.ErrorDetails.Message) {
        Write-Host "`n📋 أخطاء التحقق من البيانات:" -ForegroundColor Yellow
        $errorData = $_.ErrorDetails.Message | ConvertFrom-Json
        
        if ($errorData.errors) {
            foreach ($field in $errorData.errors.PSObject.Properties) {
                Write-Host "  • $($field.Name): $($field.Value -join ', ')" -ForegroundColor Red
            }
        }
        
        Write-Host "`nالاستجابة الكاملة:" -ForegroundColor Yellow
        $errorData | ConvertTo-Json -Depth 3
    }
}

Write-Host "`n" + "="*70
Write-Host "🎉 انتهى اختبار API!" -ForegroundColor Green
Write-Host "="*70

# ==========================================
# معلومات إضافية
# ==========================================

Write-Host "`n📋 ملخص النتائج:" -ForegroundColor Cyan
Write-Host "• تم اختبار إنشاء جلسة تسميع" -ForegroundColor White
Write-Host "• تم اختبار جلب الجلسات" -ForegroundColor White
Write-Host "• تم اختبار إضافة أخطاء التلاوة" -ForegroundColor White
Write-Host "• تم اختبار معالجة الأخطاء والتحقق من البيانات" -ForegroundColor White

Write-Host "`n🔧 للمزيد من الاختبارات، استخدم:" -ForegroundColor Cyan
Write-Host "php artisan test:recitation-complete --api" -ForegroundColor White

Write-Host "`n📖 راجع الدليل الكامل في:" -ForegroundColor Cyan  
Write-Host "RECITATION_API_GUIDE.md" -ForegroundColor White
