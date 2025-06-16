# Test Student Attendance API - Fixed Version
Write-Host "Testing Student Attendance API..." -ForegroundColor Cyan
Write-Host ""

# Test data
$testData = @{
    attendance_records = @(
        @{
            student_name = "Ahmed Ali"
            date = "2025-06-08"
            status = "present"
            period = "morning"
            notes = "Attended on time"
        }
    )
} | ConvertTo-Json -Depth 3

Write-Host "Data being sent:" -ForegroundColor Yellow
Write-Host $testData
Write-Host ""

try {
    Write-Host "Sending request to API..." -ForegroundColor Cyan
    
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/attendance/record-batch" `
                                  -Method POST `
                                  -Body $testData `
                                  -ContentType "application/json" `
                                  -Headers @{"Accept" = "application/json"}
    
    Write-Host "SUCCESS!" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Yellow
    Write-Host ($response | ConvertTo-Json -Depth 4)
    
} catch {
    Write-Host "FAILED!" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode" -ForegroundColor Red
        
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorBody = $reader.ReadToEnd()
            Write-Host "Error Details: $errorBody" -ForegroundColor Red
        } catch {
            Write-Host "Could not read error details" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "=== Test Complete ===" -ForegroundColor Green
