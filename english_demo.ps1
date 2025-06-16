# Simple API Test - English Only
Write-Host "API Test Demo" -ForegroundColor Red
Write-Host "=============" -ForegroundColor Blue

# Basic configuration
$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{"Content-Type" = "application/json"; "Accept" = "application/json"}

# Step 1: Create session
Write-Host "`n1. Creating session..." -ForegroundColor Green
$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    session_date = "2024-06-09"
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "Demo session"
} | ConvertTo-Json

try {
    $session = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $sessionData -Headers $headers
    Write-Host "   SUCCESS: Session created with ID: $($session.data.id)" -ForegroundColor Green
    Write-Host "   Session Code: $($session.data.session_code)" -ForegroundColor Yellow
    $sessionId = $session.data.id
} catch {
    Write-Host "   ERROR: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        Write-Host "   Details: $($_.ErrorDetails.Message)" -ForegroundColor Red
    }
    exit
}

# Step 2: Get sessions
Write-Host "`n2. Fetching sessions..." -ForegroundColor Green
try {
    $sessions = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    Write-Host "   SUCCESS: Found $($sessions.data.count) sessions" -ForegroundColor Green
} catch {
    Write-Host "   ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

# Step 3: Add errors
Write-Host "`n3. Adding errors..." -ForegroundColor Green
$errorsData = @{
    session_id = $sessionId
    errors = @(
        @{
            surah_number = 1
            verse_number = 2
            word_position = "word1"
            error_type = "تجويد"
            severity = "متوسط" 
            is_recurring = $true
            correction_note = "Test error"
        }
    )
} | ConvertTo-Json -Depth 5

try {
    $errors = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body $errorsData -Headers $headers
    Write-Host "   SUCCESS: Added $($errors.data.added_count) errors" -ForegroundColor Green
} catch {
    Write-Host "   ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`nDemo completed successfully!" -ForegroundColor Green
Write-Host "=========================" -ForegroundColor Blue
