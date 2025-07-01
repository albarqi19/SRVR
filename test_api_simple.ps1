# اختبار بسيط لAPI تتبع نشاط المعلمين
Write-Host "🔍 اختبار API تتبع النشاط" -ForegroundColor Green
Write-Host "=" * 50

$baseUrl = "http://127.0.0.1:8000/api"
$supervisorId = 1
$date = "2025-07-01"

# Headers
$headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host "📊 اختبار النشاط اليومي" -ForegroundColor Yellow

try {
    $url = "$baseUrl/supervisors/teachers-daily-activity?supervisor_id=$supervisorId&date=$date"
    Write-Host "URL: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "✅ نجح الطلب" -ForegroundColor Green
        Write-Host "عدد المعلمين: $($response.data.summary.total_teachers)" -ForegroundColor Blue
        Write-Host "النشطين: $($response.data.summary.active_teachers)" -ForegroundColor Blue
        Write-Host "معدل الإنجاز: $($response.data.summary.completion_rate)%" -ForegroundColor Blue
    } else {
        Write-Host "❌ فشل: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "تفاصيل: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

Write-Host ""
Write-Host "📈 اختبار الإحصائيات" -ForegroundColor Yellow

try {
    $startDate = "2025-06-24"
    $endDate = "2025-07-01"
    
    $url = "$baseUrl/supervisors/teachers-activity-statistics?supervisor_id=$supervisorId&start_date=$startDate&end_date=$endDate"
    Write-Host "URL: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "✅ نجح الطلب" -ForegroundColor Green
        Write-Host "عدد المعلمين: $($response.data.overall_summary.total_teachers)" -ForegroundColor Blue
        Write-Host "معدل التحضير: $($response.data.overall_summary.attendance_rate)%" -ForegroundColor Blue
        Write-Host "معدل التسميع: $($response.data.overall_summary.recitation_rate)%" -ForegroundColor Blue
    } else {
        Write-Host "❌ فشل: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "تفاصيل: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
}

Write-Host ""
Write-Host "🎉 انتهى الاختبار" -ForegroundColor Green
