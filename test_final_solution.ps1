# Test teacher_id to user_id mapping
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app"

$headers = @{
    "Accept" = "application/json"
    "ngrok-skip-browser-warning" = "true"
    "User-Agent" = "PowerShell-Test"
}

Write-Host "Testing teacher_id mapping..." -ForegroundColor Cyan

# Test 1: Get user_id from teacher_id 89
Write-Host "`nTest 1: Get user_id for teacher 89" -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/teachers/get-user-id/89" -Headers $headers
    
    if ($response.success) {
        Write-Host "SUCCESS!" -ForegroundColor Green
        Write-Host "Teacher ID (teachers table): $($response.data.teacher_id_in_teachers_table)" -ForegroundColor White
        Write-Host "User ID (for API): $($response.data.teacher_id_for_api)" -ForegroundColor Green
        Write-Host "Name: $($response.data.teacher_name)" -ForegroundColor White
        
        $correctId = $response.data.teacher_id_for_api
        
        # Test session creation with correct ID
        Write-Host "`nTest 2: Create session with user_id $correctId" -ForegroundColor Yellow
        
        $sessionData = @{
            student_id = 36
            teacher_id = $correctId
            quran_circle_id = 1
            start_surah_number = 1
            start_verse = 1
            end_surah_number = 1
            end_verse = 1
            recitation_type = "حفظ"
            duration_minutes = 30
            grade = 8.5
            evaluation = "جيد جداً"
            teacher_notes = "Test with correct user_id"
        }
        
        $sessionHeaders = $headers.Clone()
        $sessionHeaders["Content-Type"] = "application/json"
        
        try {
            $sessionResponse = Invoke-RestMethod -Uri "$baseUrl/api/recitation/sessions" -Method POST -Body ($sessionData | ConvertTo-Json) -Headers $sessionHeaders
            
            if ($sessionResponse.success) {
                Write-Host "SUCCESS! Session created!" -ForegroundColor Green
                Write-Host "Session ID: $($sessionResponse.data.session_id)" -ForegroundColor White
            }
        } catch {
            Write-Host "Session creation failed: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "Failed to get user_id: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`nSOLUTION: Use teacher_id = 34 instead of 89 in your frontend!" -ForegroundColor Green
