# اختبار API مع تشخيص تفصيلي

# تعيين encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$PSDefaultParameterValues['*:Encoding'] = 'utf8'

Write-Host "=== Testing API with Detailed Debug ===" -ForegroundColor Green

# البيانات الأساسية
$sessionData = @{
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
    teacher_notes = "Test session from API"
    status = "مكتملة"
}

# طباعة البيانات
Write-Host "Data being sent:" -ForegroundColor Yellow
$sessionData | Format-Table -AutoSize

# تحويل إلى JSON
$jsonBody = $sessionData | ConvertTo-Json -Depth 10 -Compress
Write-Host "JSON Body:" -ForegroundColor Cyan
Write-Host $jsonBody
Write-Host ""

# تحويل إلى UTF8 bytes
$utf8Bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonBody)
Write-Host "UTF8 Bytes length: $($utf8Bytes.Length)" -ForegroundColor Magenta

try {
    # إرسال الطلب
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" `
        -Method POST `
        -Headers @{
            "Accept" = "application/json"
            "Content-Type" = "application/json; charset=utf-8"
        } `
        -Body $utf8Bytes `
        -Verbose
    
    Write-Host "✅ SUCCESS!" -ForegroundColor Green
    Write-Host ($response | ConvertTo-Json -Depth 10)
    
} catch {
    Write-Host "❌ ERROR OCCURRED:" -ForegroundColor Red
    Write-Host "HTTP Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
    Write-Host "Error Message: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $responseBody = $reader.ReadToEnd()
            
            Write-Host "`nResponse Body:" -ForegroundColor Yellow
            Write-Host $responseBody
            
            # فك تشفير Unicode escapes  
            $decodedResponse = [System.Text.RegularExpressions.Regex]::Unescape($responseBody)
            Write-Host "`nDecoded Response:" -ForegroundColor Cyan
            Write-Host $decodedResponse
            
            $reader.Close()
            $stream.Close()
        } catch {
            Write-Host "Could not read response body: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# اقتراح فحص logs
Write-Host "`n=== Check Laravel logs ===" -ForegroundColor Yellow
Write-Host "Run: Get-Content storage\logs\laravel.log -Tail 20" -ForegroundColor White
