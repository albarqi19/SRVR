# اختبار API حضور الطلاب باستخدام PowerShell

Write-Host "=== اختبار API حضور الطلاب ===" -ForegroundColor Green
Write-Host ""

# بيانات الاختبار
$testData = @{
    teacherId = 1
    date = "2025-06-08"
    time = "14:30:00"
    students = @(
        @{
            studentId = 1
            status = "حاضر"
            notes = "حضر في الوقت المحدد"
        }
    )
} | ConvertTo-Json -Depth 3

Write-Host "البيانات المُرسلة:" -ForegroundColor Yellow
Write-Host $testData
Write-Host ""

try {
    Write-Host "إرسال طلب إلى API..." -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/attendance/record-batch" `
                                  -Method POST `
                                  -Body $testData `
                                  -ContentType "application/json" `
                                  -Headers @{"Accept" = "application/json"}
    
    Write-Host "✅ نجح الطلب!" -ForegroundColor Green
    Write-Host "الاستجابة:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json -Depth 4)
    
} catch {
    Write-Host "❌ فشل الطلب!" -ForegroundColor Red
    Write-Host "خطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "كود الحالة: $statusCode" -ForegroundColor Red
        
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorBody = $reader.ReadToEnd()
            Write-Host "تفاصيل الخطأ: $errorBody" -ForegroundColor Red
        } catch {
            Write-Host "لا يمكن قراءة تفاصيل الخطأ" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "=== انتهى الاختبار ===" -ForegroundColor Green
