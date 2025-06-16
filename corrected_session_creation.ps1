# أمر إنشاء جلسة تسميع صحيحة
$jsonData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 5
    recitation_type = "حفظ"  # تغيير من "memorization" إلى "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "ممتاز"  # تغيير من "excellent" إلى "ممتاز"
    teacher_notes = "Test session"  # تغيير من "teacher_notes" إلى "teacher_notes"
    status = "مكتملة"  # إضافة حالة الجلسة المطلوبة
} | ConvertTo-Json

try {
    $result = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Headers @{"Accept"="application/json"; "Content-Type"="application/json"} -Body $jsonData
    Write-Host "✅ Success: $($result | ConvertTo-Json)"
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorBody = $reader.ReadToEnd()
        Write-Host "📄 Response body: $errorBody"
    }
}
