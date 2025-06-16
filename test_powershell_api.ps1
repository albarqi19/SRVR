try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/recitation/sessions" -Method POST -Body '{"student_id":1,"teacher_id":1,"quran_circle_id":1,"curriculum_id":6,"start_surah_number":1,"start_verse":1,"end_surah_number":1,"end_verse":7,"recitation_type":"حفظ","duration_minutes":15,"grade":8.5,"evaluation":"جيد جداً","teacher_notes":"Good performance","status":"مكتملة"}' -ContentType "application/json" -Headers @{"Accept"="application/json"}
    
    Write-Host "✅ Success!" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "❌ Error occurred:" -ForegroundColor Red
    Write-Host "Status Code: $($_.Exception.Response.StatusCode)" -ForegroundColor Yellow
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $errorBody = $reader.ReadToEnd()
        Write-Host "Error Response:" -ForegroundColor Yellow
        Write-Host $errorBody
    }
}
