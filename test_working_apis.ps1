# اختبار سريع للـ APIs التي تعمل فقط
# تاريخ: 10 يونيو 2025

$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app/api"
$studentId = 14
$headers = @{
    "ngrok-skip-browser-warning" = "true"
    "Accept" = "application/json"
}

Write-Host "=== اختبار APIs الطالب الشخصية الناجحة ===" -ForegroundColor Green
Write-Host ""

# 1. تفاصيل الطالب
Write-Host "1. تفاصيل الطالب..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId" -Headers $headers -Method GET
    Write-Host "✅ نجح - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل - $($_.Exception.Message)" -ForegroundColor Red
}

# 2. سجل الحضور
Write-Host "2. سجل الحضور..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/attendance" -Headers $headers -Method GET
    Write-Host "✅ نجح - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل - $($_.Exception.Message)" -ForegroundColor Red
}

# 3. إحصائيات أخطاء التسميع
Write-Host "3. إحصائيات أخطاء التسميع..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/recitation/errors/stats/student/$studentId" -Headers $headers -Method GET
    Write-Host "✅ نجح - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل - $($_.Exception.Message)" -ForegroundColor Red
}

# 4. إحصائيات التسميع
Write-Host "4. إحصائيات التسميع..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/recitation/sessions/stats/student/$studentId" -Headers $headers -Method GET
    Write-Host "✅ نجح - Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل - $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== انتهى الاختبار ===" -ForegroundColor Green
