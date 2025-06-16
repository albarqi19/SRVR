# اختبار شامل لـ APIs الطالب الشخصية
# تاريخ: 10 يونيو 2025

# إعداد المتغيرات
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app/api"
$studentId = 14
$headers = @{
    "ngrok-skip-browser-warning" = "true"
    "Accept" = "application/json"
}

Write-Host "=== بدء اختبار APIs الطالب الشخصية ===" -ForegroundColor Green
Write-Host ""

# =======================================
# 1. تفاصيل الطالب الأساسية
# =======================================
Write-Host "1. اختبار API تفاصيل الطالب..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId" -Headers $headers -Method GET
    Write-Host "✅ نجح: GET /api/students/$studentId" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Content Length: $($response.RawContentLength) bytes" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 2. سجل الحضور والغياب
# =======================================
Write-Host "2. اختبار API سجل الحضور..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/attendance" -Headers $headers -Method GET
    Write-Host "✅ نجح: GET /api/students/$studentId/attendance" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Content Length: $($response.RawContentLength) bytes" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId/attendance" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 3. إحصائيات الطالب العامة
# =======================================
Write-Host "3. اختبار API إحصائيات الطالب..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/stats" -Headers $headers -Method GET
    Write-Host "✅ نجح: GET /api/students/$studentId/stats" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId/stats" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 4. منهج الطالب
# =======================================
Write-Host "4. اختبار API منهج الطالب..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/curriculum" -Headers $headers -Method GET
    Write-Host "✅ نجح: GET /api/students/$studentId/curriculum" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId/curriculum" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 5. المنهج اليومي
# =======================================
Write-Host "5. اختبار API المنهج اليومي..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/daily-curriculum" -Headers $headers -Method GET
    Write-Host "✅ نجح: GET /api/students/$studentId/daily-curriculum" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId/daily-curriculum" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 6. جلسات التسميع
# =======================================
Write-Host "6. اختبار API جلسات التسميع..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/students/$studentId/recitation-sessions" -Headers $headers -Method GET -TimeoutSec 10
    Write-Host "✅ نجح: GET /api/students/$studentId/recitation-sessions" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/students/$studentId/recitation-sessions" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 7. إحصائيات التسميع
# =======================================
Write-Host "7. اختبار API إحصائيات التسميع..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/recitation/sessions/stats/student/$studentId" -Headers $headers -Method GET -TimeoutSec 10
    Write-Host "✅ نجح: GET /api/recitation/sessions/stats/student/$studentId" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/recitation/sessions/stats/student/$studentId" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 8. إحصائيات أخطاء التسميع
# =======================================
Write-Host "8. اختبار API إحصائيات أخطاء التسميع..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/recitation/errors/stats/student/$studentId" -Headers $headers -Method GET -TimeoutSec 10
    Write-Host "✅ نجح: GET /api/recitation/errors/stats/student/$studentId" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: GET /api/recitation/errors/stats/student/$studentId" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 9. تسجيل دخول الطالب
# =======================================
Write-Host "9. اختبار API تسجيل دخول الطالب..." -ForegroundColor Yellow
try {
    $loginBody = @{
        "identity_number" = "1234567890"
        "password" = "123456"
    } | ConvertTo-Json

    $loginHeaders = $headers.Clone()
    $loginHeaders["Content-Type"] = "application/json"

    $response = Invoke-WebRequest -Uri "$baseUrl/auth/student/login" -Headers $loginHeaders -Method POST -Body $loginBody
    Write-Host "✅ نجح: POST /api/auth/student/login" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: POST /api/auth/student/login" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# =======================================
# 10. معلومات المستخدم
# =======================================
Write-Host "10. اختبار API معلومات المستخدم..." -ForegroundColor Yellow
try {
    $userInfoBody = @{
        "user_type" = "student"
        "identity_number" = "1234567890"
    } | ConvertTo-Json

    $userInfoHeaders = $headers.Clone()
    $userInfoHeaders["Content-Type"] = "application/json"

    $response = Invoke-WebRequest -Uri "$baseUrl/auth/user-info" -Headers $userInfoHeaders -Method POST -Body $userInfoBody
    Write-Host "✅ نجح: POST /api/auth/user-info" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "❌ فشل: POST /api/auth/user-info" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

Write-Host "=== انتهى اختبار APIs الطالب الشخصية ===" -ForegroundColor Green
