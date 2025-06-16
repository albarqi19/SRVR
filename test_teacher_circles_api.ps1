# ══════════════════════════════════════════════════════════════════════════════
# اختبار API حلقات المعلم - /api/teachers/{id}/circles
# ══════════════════════════════════════════════════════════════════════════════

# تنظيف الشاشة
Clear-Host

Write-Host "══════════════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                       اختبار API حلقات المعلم" -ForegroundColor Yellow
Write-Host "══════════════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan

# إعدادات الاختبار
$baseUrl = "http://127.0.0.1:8000"
$endpoint = "/api/teachers"
$headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

# ═══════════════════════════════════════════════════════════════════════════════
# الاختبار 1: جلب حلقات معلم برقم 1
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n📌 الاختبار 1: جلب حلقات المعلم رقم 1" -ForegroundColor Green
Write-Host "-------------------------------------------"

try {
    $url = "$baseUrl$endpoint/1/circles"
    Write-Host "🔗 الرابط: $url" -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    Write-Host "✅ تم الاستعلام بنجاح!" -ForegroundColor Green
    Write-Host "📋 النتيجة:" -ForegroundColor Yellow
    $response | ConvertTo-Json -Depth 10
    
} catch {
    Write-Host "❌ فشل الاختبار:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "🔢 رمز الخطأ: $statusCode" -ForegroundColor Red
    }
}

# ═══════════════════════════════════════════════════════════════════════════════
# الاختبار 2: جلب حلقات معلم برقم 2
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n📌 الاختبار 2: جلب حلقات المعلم رقم 2" -ForegroundColor Green
Write-Host "-------------------------------------------"

try {
    $url = "$baseUrl$endpoint/2/circles"
    Write-Host "🔗 الرابط: $url" -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    Write-Host "✅ تم الاستعلام بنجاح!" -ForegroundColor Green
    Write-Host "📋 النتيجة:" -ForegroundColor Yellow
    $response | ConvertTo-Json -Depth 10
    
} catch {
    Write-Host "❌ فشل الاختبار:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "🔢 رمز الخطأ: $statusCode" -ForegroundColor Red
    }
}

# ═══════════════════════════════════════════════════════════════════════════════
# الاختبار 3: جلب حلقات معلم غير موجود (رقم 9999)
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n📌 الاختبار 3: اختبار معلم غير موجود (رقم 9999)" -ForegroundColor Green
Write-Host "---------------------------------------------------"

try {
    $url = "$baseUrl$endpoint/9999/circles"
    Write-Host "🔗 الرابط: $url" -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    Write-Host "✅ تم الاستعلام بنجاح!" -ForegroundColor Green
    Write-Host "📋 النتيجة:" -ForegroundColor Yellow
    $response | ConvertTo-Json -Depth 10
    
} catch {
    Write-Host "❌ متوقع - معلم غير موجود:" -ForegroundColor Yellow
    Write-Host $_.Exception.Message -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "🔢 رمز الخطأ: $statusCode" -ForegroundColor Red
    }
}

# ═══════════════════════════════════════════════════════════════════════════════
# الاختبار 4: اختبار جلب قائمة المعلمين أولاً لمعرفة المعلمين الموجودين
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n📌 الاختبار 4: جلب قائمة المعلمين لمعرفة الأرقام الصحيحة" -ForegroundColor Green
Write-Host "--------------------------------------------------------------"

try {
    $url = "$baseUrl$endpoint"
    Write-Host "🔗 الرابط: $url" -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    Write-Host "✅ تم جلب قائمة المعلمين بنجاح!" -ForegroundColor Green
    Write-Host "📊 عدد المعلمين: " -NoNewline -ForegroundColor Yellow
    if ($response.data -and $response.data.data) {
        Write-Host $response.data.data.Count -ForegroundColor White
        
        Write-Host "`n📋 قائمة المعلمين الموجودين:" -ForegroundColor Yellow
        foreach ($teacher in $response.data.data) {
            $mosqueName = if ($teacher.mosque) { $teacher.mosque.name } else { "غير محدد" }
            $circleName = if ($teacher.quran_circle) { $teacher.quran_circle.name } else { "غير محدد" }
            Write-Host "  🆔 $($teacher.id) - $($teacher.name) - مسجد: $mosqueName - حلقة: $circleName" -ForegroundColor White
        }
    } else {
        Write-Host "0" -ForegroundColor White
    }
    
} catch {
    Write-Host "❌ فشل في جلب قائمة المعلمين:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

# ═══════════════════════════════════════════════════════════════════════════════
# الاختبار 5: اختبار مع معلمين موجودين فعلياً
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n📌 الاختبار 5: اختبار حلقات المعلمين الموجودين" -ForegroundColor Green
Write-Host "--------------------------------------------------"

# اختبار أول 3 معلمين من القائمة
for ($i = 1; $i -le 5; $i++) {
    Write-Host "`n🔍 اختبار المعلم رقم $i :" -ForegroundColor Cyan
    try {
        $url = "$baseUrl$endpoint/$i/circles"
        $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
        
        Write-Host "  ✅ نجح الاختبار!" -ForegroundColor Green
        Write-Host "  👨‍🏫 اسم المعلم: $($response.teacher_name)" -ForegroundColor White
        Write-Host "  🔢 عدد الحلقات: $($response.total_circles)" -ForegroundColor White
        
        if ($response.circles -and $response.circles.Count -gt 0) {
            Write-Host "  📚 الحلقات:" -ForegroundColor Yellow
            foreach ($circle in $response.circles) {
                $mosqueName = if ($circle.المسجد) { $circle.المسجد.الاسم } else { "غير محدد" }
                Write-Host "    • $($circle.اسم_الحلقة) - النوع: $($circle.النوع) - التكليف: $($circle.نوع_التكليف) - المسجد: $mosqueName" -ForegroundColor White
            }
        } else {
            Write-Host "  📝 لا توجد حلقات مرتبطة بهذا المعلم" -ForegroundColor Yellow
        }
        
    } catch {
        if ($_.Exception.Response.StatusCode.value__ -eq 404) {
            Write-Host "  ⚠️ المعلم رقم $i غير موجود" -ForegroundColor Yellow
        } else {
            Write-Host "  ❌ خطأ في الاختبار: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# ═══════════════════════════════════════════════════════════════════════════════
# نهاية الاختبارات
# ═══════════════════════════════════════════════════════════════════════════════
Write-Host "`n══════════════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "                            انتهى الاختبار" -ForegroundColor Yellow
Write-Host "══════════════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan

Write-Host "`n📋 ملخص الاختبار:" -ForegroundColor Green
Write-Host "• تم اختبار API endpoint: /api/teachers/{id}/circles" -ForegroundColor White
Write-Host "• تم اختبار حالات مختلفة: معلمين موجودين وغير موجودين" -ForegroundColor White
Write-Host "• تم عرض النتائج مع التفاصيل الكاملة" -ForegroundColor White
Write-Host "`n🚀 لتشغيل الاختبار مرة أخرى، استخدم الأمر:" -ForegroundColor Cyan
Write-Host ".\test_teacher_circles_api.ps1" -ForegroundColor Yellow
