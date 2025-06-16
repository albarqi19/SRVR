try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Headers @{"Accept"="application/json"; "Content-Type"="application/json"} -Body '{"student_id":1,"teacher_id":1,"quran_circle_id":1,"start_surah_number":1,"start_verse":1,"end_surah_number":1,"end_verse":5,"recitation_type":"حفظ","duration_minutes":30,"grade":8.5,"evaluation":"ممتاز","teacher_notes":"Test session","status":"جارية"}' -Verbose
    Write-Host "Success: "
    $response | ConvertTo-Json
} catch {
    Write-Host "Error: $($_.Exception.Message)"
}
