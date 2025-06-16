# تست إنشاء جلسة تسميع مع تشفير صحيح للأحرف العربية

# تعيين الـ encoding الصحيح
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$PSDefaultParameterValues['*:Encoding'] = 'utf8'

# إنشاء البيانات بـ encoding صحيح
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

# تحويل إلى JSON مع encoding صحيح
$jsonBody = $sessionData | ConvertTo-Json -Depth 10
$utf8Bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonBody)

Write-Host "JSON Body being sent:"
Write-Host $jsonBody
Write-Host ""

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
    
    Write-Host "✅ Success!" -ForegroundColor Green
    Write-Host ($response | ConvertTo-Json -Depth 10)
    
} catch {
    Write-Host "❌ Error occurred:" -ForegroundColor Red
    Write-Host "HTTP Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
    Write-Host "Error Message: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $responseBody = $reader.ReadToEnd()
            Write-Host "Response Body: $responseBody" -ForegroundColor Yellow
            
            # محاولة فك تشفير Unicode escapes
            $decodedResponse = [System.Text.RegularExpressions.Regex]::Unescape($responseBody)
            Write-Host "Decoded Response: $decodedResponse" -ForegroundColor Cyan
            
            $reader.Close()
            $stream.Close()
        } catch {
            Write-Host "Could not read response body: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}
