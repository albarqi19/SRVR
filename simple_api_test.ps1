# Simple API Test
$headers = @{
    'Content-Type' = 'application/json'
    'Accept' = 'application/json'
}

$body = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 5
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "ممتاز"
    teacher_notes = "Test"
    status = "جارية"
} | ConvertTo-Json

Write-Host "Testing API..."
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Headers $headers -Body $body
    Write-Host "Success!" -ForegroundColor Green
    $response
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $responseBody = $reader.ReadToEnd()
        Write-Host "Response: $responseBody" -ForegroundColor Yellow
    }
}
