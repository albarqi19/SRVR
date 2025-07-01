# Test teacher_id to user_id mapping solutions
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app"

# Headers required for ngrok
$headers = @{
    "Accept" = "application/json"
    "ngrok-skip-browser-warning" = "true"
    "User-Agent" = "PowerShell-API-Test"
}

Write-Host "Testing teacher_id mapping solutions" -ForegroundColor Cyan
Write-Host "=" * 50

# Test 1: Get user_id from teacher_id
Write-Host "`nTest 1: Get user_id from teacher_id 89:" -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/teachers/get-user-id/89" -Headers $headers -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "SUCCESS:" -ForegroundColor Green
    Write-Host ($data | ConvertTo-Json -Depth 3) -ForegroundColor White
    
    if ($data.success -and $data.data.teacher_id_for_api) {
        $correctTeacherId = $data.data.teacher_id_for_api
        Write-Host "`nCorrect teacher_id for API: $correctTeacherId" -ForegroundColor Green
    }
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: List all teachers with user_ids
Write-Host "`nTest 2: List teachers with user_ids:" -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/teachers/with-user-ids" -Headers $headers -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "SUCCESS:" -ForegroundColor Green
    
    if ($data.success -and $data.data) {
        Write-Host "`nTeachers list:" -ForegroundColor Cyan
        foreach ($teacher in $data.data) {
            $teacherId = $teacher.teacher_id
            $userId = $teacher.user_id
            $name = $teacher.teacher_name
            $email = $teacher.user_email
            
            if ($userId) {
                Write-Host "OK: $name (teacher_id: $teacherId -> user_id: $userId)" -ForegroundColor Green
            } else {
                Write-Host "MISSING: $name (teacher_id: $teacherId -> no user_id)" -ForegroundColor Red
            }
        }
    }
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Test recitation session creation with teacher_id = 89
Write-Host "`nTest 3: Create session with teacher_id = 89:" -ForegroundColor Yellow

$sessionData = @{
    student_id = 36
    teacher_id = 89  # Using teacher_id from teachers table
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 1
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "Testing new validation rule"
} | ConvertTo-Json

$sessionHeaders = $headers.Clone()
$sessionHeaders["Content-Type"] = "application/json"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/recitation/sessions" -Method POST -Body $sessionData -Headers $sessionHeaders -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "SUCCESS: Session created with new validation rule!" -ForegroundColor Green
    Write-Host ($data | ConvertTo-Json -Depth 3) -ForegroundColor White
    
} catch {
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $errorContent = $reader.ReadToEnd() | ConvertFrom-Json
            
            Write-Host "FAILED:" -ForegroundColor Red
            Write-Host "Status: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
            Write-Host "Message: $($errorContent.message)" -ForegroundColor Red
            
            if ($errorContent.errors) {
                Write-Host "Validation Errors:" -ForegroundColor Red
                Write-Host ($errorContent.errors | ConvertTo-Json -Depth 2) -ForegroundColor Red
            }
        } catch {
            Write-Host "ERROR reading response: $($_.Exception.Message)" -ForegroundColor Red
        }
    } else {
        Write-Host "CONNECTION ERROR: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n" + "=" * 50
Write-Host "SOLUTIONS SUMMARY:" -ForegroundColor Cyan
Write-Host "1. Use API endpoint to get correct user_id" -ForegroundColor White
Write-Host "2. Use user_id instead of teacher_id in API calls" -ForegroundColor White  
Write-Host "3. New validation rule should accept both teacher_id and user_id" -ForegroundColor White
