# اختبار مبسط لـ API مساجد المعلم باستخدام PowerShell
# GET /api/teachers/{id}/mosques

Write-Host "🚀 بدء اختبار API مساجد المعلم" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Yellow

# إعدادات الاختبار
$baseUrl = "http://localhost:8000/api"  # تعديل الرابط حسب إعدادك
$teacherId = 1  # معرف المعلم للاختبار

# دالة لإرسال طلب HTTP
function Send-APIRequest {
    param(
        [string]$Url,
        [string]$Method = "GET"
    )
    
    try {
        $response = Invoke-RestMethod -Uri $Url -Method $Method -ContentType "application/json"
        return $response
    }
    catch {
        Write-Host "❌ خطأ في الطلب: $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

# دالة لعرض النتائج
function Show-TestResult {
    param(
        [string]$TestName,
        [bool]$Passed,
        [string]$Message
    )
    
    $status = if ($Passed) { "✅" } else { "❌" }
    $color = if ($Passed) { "Green" } else { "Red" }
    
    Write-Host "$status $TestName : $Message" -ForegroundColor $color
}

# الاختبار 1: التحقق من وجود خادم Laravel
Write-Host "`n🧪 اختبار 1: التحقق من وجود الخادم..." -ForegroundColor Cyan
try {
    $testUrl = "http://localhost:8000"
    $response = Invoke-WebRequest -Uri $testUrl -TimeoutSec 5 -ErrorAction Stop
    Show-TestResult "اتصال الخادم" $true "الخادم يعمل بنجاح"
}
catch {
    Show-TestResult "اتصال الخادم" $false "فشل الاتصال بالخادم. تأكد من تشغيل 'php artisan serve'"
    Write-Host "تشغيل الخادم: php artisan serve" -ForegroundColor Yellow
    exit 1
}

# الاختبار 2: اختبار API مع معرف صحيح
Write-Host "`n🧪 اختبار 2: اختبار API مع معرف معلم صحيح..." -ForegroundColor Cyan
$apiUrl = "$baseUrl/teachers/$teacherId/mosques"
$response = Send-APIRequest -Url $apiUrl

if ($response) {
    if ($response.نجح -eq $true) {
        Show-TestResult "استجابة API" $true "API يستجيب بنجاح"
        
        # التحقق من هيكل البيانات
        if ($response.البيانات) {
            Show-TestResult "هيكل البيانات" $true "البيانات موجودة"
            
            # عرض معلومات المعلم
            if ($response.البيانات.معلومات_المعلم) {
                $teacherInfo = $response.البيانات.معلومات_المعلم
                Write-Host "   📋 معلومات المعلم:" -ForegroundColor Cyan
                Write-Host "      • الاسم: $($teacherInfo.الاسم)" -ForegroundColor White
                Write-Host "      • رقم الهوية: $($teacherInfo.رقم_الهوية)" -ForegroundColor White
                Write-Host "      • رقم الهاتف: $($teacherInfo.رقم_الهاتف)" -ForegroundColor White
            }
            
            # عرض الإحصائيات
            if ($response.البيانات.الإحصائيات) {
                $stats = $response.البيانات.الإحصائيات
                Write-Host "   📊 الإحصائيات:" -ForegroundColor Cyan
                Write-Host "      • عدد المساجد: $($stats.عدد_المساجد)" -ForegroundColor White
                Write-Host "      • عدد الحلقات: $($stats.عدد_الحلقات)" -ForegroundColor White
                Write-Host "      • إجمالي الطلاب: $($stats.إجمالي_الطلاب)" -ForegroundColor White
            }
            
            # عرض المساجد
            if ($response.البيانات.المساجد) {
                Write-Host "   🕌 المساجد:" -ForegroundColor Cyan
                foreach ($mosque in $response.البيانات.المساجد) {
                    Write-Host "      • $($mosque.اسم_المسجد) ($($mosque.النوع))" -ForegroundColor White
                    Write-Host "        العنوان: $($mosque.العنوان)" -ForegroundColor Gray
                    Write-Host "        عدد الحلقات: $($mosque.الحلقات.Count)" -ForegroundColor Gray
                    Write-Host "        عدد الجداول: $($mosque.الجداول.Count)" -ForegroundColor Gray
                }
            }
            
        } else {
            Show-TestResult "هيكل البيانات" $false "البيانات غير موجودة"
        }
    } else {
        Show-TestResult "استجابة API" $false "API يعيد خطأ: $($response.رسالة)"
    }
} else {
    Show-TestResult "استجابة API" $false "لا توجد استجابة من API"
}

# الاختبار 3: اختبار API مع معرف غير موجود
Write-Host "`n🧪 اختبار 3: اختبار API مع معرف غير موجود..." -ForegroundColor Cyan
$invalidUrl = "$baseUrl/teachers/99999/mosques"
$invalidResponse = Send-APIRequest -Url $invalidUrl

if ($invalidResponse) {
    if ($invalidResponse.نجح -eq $false) {
        Show-TestResult "معالجة خطأ المعرف" $true "API يعالج المعرف غير الموجود بشكل صحيح"
        Write-Host "   رسالة الخطأ: $($invalidResponse.رسالة)" -ForegroundColor Yellow
    } else {
        Show-TestResult "معالجة خطأ المعرف" $false "API لا يعالج المعرف غير الموجود بشكل صحيح"
    }
} else {
    Show-TestResult "معالجة خطأ المعرف" $true "API يرفض المعرف غير الموجود (متوقع)"
}

# الاختبار 4: اختبار API مع معرف غير صحيح
Write-Host "`n🧪 اختبار 4: اختبار API مع معرف غير صحيح..." -ForegroundColor Cyan
$wrongUrl = "$baseUrl/teachers/abc/mosques"
$wrongResponse = Send-APIRequest -Url $wrongUrl

if ($wrongResponse) {
    if ($wrongResponse.نجح -eq $false) {
        Show-TestResult "معالجة معرف خاطئ" $true "API يعالج المعرف الخاطئ بشكل صحيح"
    } else {
        Show-TestResult "معالجة معرف خاطئ" $false "API لا يعالج المعرف الخاطئ بشكل صحيح"
    }
} else {
    Show-TestResult "معالجة معرف خاطئ" $true "API يرفض المعرف الخاطئ (متوقع)"
}

# الاختبار 5: اختبار سرعة الاستجابة
Write-Host "`n🧪 اختبار 5: اختبار سرعة الاستجابة..." -ForegroundColor Cyan
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
$speedResponse = Send-APIRequest -Url $apiUrl
$stopwatch.Stop()

$responseTime = $stopwatch.ElapsedMilliseconds
if ($responseTime -lt 2000) {
    Show-TestResult "سرعة الاستجابة" $true "الاستجابة سريعة (${responseTime}ms)"
} elseif ($responseTime -lt 5000) {
    Show-TestResult "سرعة الاستجابة" $true "الاستجابة مقبولة (${responseTime}ms)"
} else {
    Show-TestResult "سرعة الاستجابة" $false "الاستجابة بطيئة (${responseTime}ms)"
}

# الاختبار 6: اختبار تنسيق JSON
Write-Host "`n🧪 اختبار 6: اختبار تنسيق JSON..." -ForegroundColor Cyan
try {
    $jsonResponse = Invoke-RestMethod -Uri $apiUrl -Method GET -ContentType "application/json"
    $jsonString = $jsonResponse | ConvertTo-Json -Depth 10
    if ($jsonString) {
        Show-TestResult "تنسيق JSON" $true "JSON صحيح ومنسق بشكل جيد"
    } else {
        Show-TestResult "تنسيق JSON" $false "مشكلة في تنسيق JSON"
    }
}
catch {
    Show-TestResult "تنسيق JSON" $false "خطأ في تنسيق JSON"
}

# النتائج النهائية
Write-Host "`n========================================" -ForegroundColor Yellow
Write-Host "📊 ملخص نتائج الاختبار" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Yellow

Write-Host "`n🎯 معلومات API:" -ForegroundColor Cyan
Write-Host "   • المسار: GET /api/teachers/{id}/mosques" -ForegroundColor White
Write-Host "   • الوصف: جلب جميع المساجد التي يعمل بها المعلم" -ForegroundColor White
Write-Host "   • مثال: curl -X GET '$baseUrl/teachers/1/mosques'" -ForegroundColor White

Write-Host "`n📋 خطوات الاستخدام:" -ForegroundColor Cyan
Write-Host "   1. تأكد من تشغيل خادم Laravel: php artisan serve" -ForegroundColor White
Write-Host "   2. استبدل {id} برقم المعلم المطلوب" -ForegroundColor White
Write-Host "   3. أرسل طلب GET إلى المسار المحدد" -ForegroundColor White
Write-Host "   4. ستحصل على جميع المساجد مع التفاصيل" -ForegroundColor White

Write-Host "`n✨ مميزات API:" -ForegroundColor Cyan
Write-Host "   • عرض المسجد الأساسي والمساجد الإضافية" -ForegroundColor White
Write-Host "   • تفاصيل الحلقات والطلاب في كل مسجد" -ForegroundColor White
Write-Host "   • جداول العمل في المساجد المختلفة" -ForegroundColor White
Write-Host "   • إحصائيات شاملة للمعلم" -ForegroundColor White

Write-Host "`n🏁 انتهى الاختبار بنجاح!" -ForegroundColor Green
