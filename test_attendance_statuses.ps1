# اختبار حالات الحضور المختلفة
# تاريخ: 10 يونيو 2025

# إعداد المتغيرات
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app/api/attendance/record"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "ngrok-skip-browser-warning" = "true"
}
$today = Get-Date -Format "yyyy-MM-dd"
$statusValues = @("حاضر", "غائب", "متأخر", "مأذون")

Write-Host "=== اختبار حالات الحضور المختلفة ===" -ForegroundColor Green
Write-Host "Base URL: $baseUrl" -ForegroundColor Cyan
Write-Host "Date: $today" -ForegroundColor Cyan
Write-Host "Status values to test: $($statusValues -join ', ')" -ForegroundColor Cyan
Write-Host ""

# اختبار كل حالة
foreach ($status in $statusValues) {
    $body = @{
        student_name = "اختبار طالب"
        date = $today
        status = $status
        period = "العصر"
        notes = "اختبار حالة: $status"
    } | ConvertTo-Json -Depth 3

    Write-Host "Testing status: '$status'" -ForegroundColor Yellow
    
    try {
        $response = Invoke-RestMethod -Uri $baseUrl -Method POST -Headers $headers -Body $body -ErrorAction Stop
        Write-Host "✅ SUCCESS: '$status' is accepted" -ForegroundColor Green
        Write-Host "Response: $($response | ConvertTo-Json -Compress)" -ForegroundColor Gray
    }
    catch {
        $errorDetails = $_.Exception.Response
        if ($errorDetails) {
            try {
                $stream = $errorDetails.GetResponseStream()
                $reader = New-Object System.IO.StreamReader($stream)
                $errorBody = $reader.ReadToEnd()
                Write-Host "❌ FAILED: '$status' - $($errorDetails.StatusCode)" -ForegroundColor Red
                Write-Host "Error: $errorBody" -ForegroundColor DarkRed
            }
            catch {
                Write-Host "❌ FAILED: '$status' - $($errorDetails.StatusCode)" -ForegroundColor Red
            }
        } else {
            Write-Host "❌ FAILED: '$status' - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    Start-Sleep -Seconds 1
    Write-Host ""
}

Write-Host "=== انتهى الاختبار ===" -ForegroundColor Green
