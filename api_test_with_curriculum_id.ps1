# PowerShell script لإنشاء جلسة تسميع مع curriculum_id

$body = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    curriculum_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 7
    recitation_type = "حفظ"
    duration_minutes = 15
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "أداء جيد مع بعض الأخطاء البسيطة"
    status = "مكتملة"
} | ConvertTo-Json -Depth 10

Write-Host "=== إنشاء جلسة تسميع مع curriculum_id ===" -ForegroundColor Yellow
Write-Host "البيانات المرسلة:" -ForegroundColor Cyan
Write-Host $body -ForegroundColor Gray

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" `
        -Method POST `
        -Body $body `
        -ContentType "application/json" `
        -Headers @{
            "Accept" = "application/json"
        } `
        -TimeoutSec 30

    Write-Host "`n✅ نجح إنشاء جلسة التسميع!" -ForegroundColor Green
    Write-Host "تفاصيل الجلسة:" -ForegroundColor Cyan
    Write-Host ($response | ConvertTo-Json -Depth 10) -ForegroundColor Gray
    
    if ($response.data.session_id) {
        Write-Host "`n🆔 Session ID: $($response.data.session_id)" -ForegroundColor Magenta
    }
}
catch {
    Write-Host "`n❌ فشل في إنشاء جلسة التسميع!" -ForegroundColor Red
    Write-Host "تفاصيل الخطأ:" -ForegroundColor Yellow
    
    if ($_.Exception.Response) {
        $errorResponse = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($errorResponse)
        $errorBody = $reader.ReadToEnd()
        Write-Host $errorBody -ForegroundColor Red
    } else {
        Write-Host $_.Exception.Message -ForegroundColor Red
    }
}
