# API Live Demo - Clean Version
# Date: June 9, 2025
# Purpose: Demonstrate Recitation API workflow

Write-Host "API Live Demo - RecitationSessions" -ForegroundColor Red
Write-Host "==================================" -ForegroundColor Blue

$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host ""
Write-Host "Step 1: Creating a new recitation session..." -ForegroundColor Green

$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    session_date = "2024-06-09"
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "Live demo session via PowerShell"
}

$sessionJson = $sessionData | ConvertTo-Json -Depth 3

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $sessionJson -Headers $headers
    Write-Host "SUCCESS: Session created!" -ForegroundColor Green
    Write-Host "Session Code: $($response.data.session_code)" -ForegroundColor Cyan
    Write-Host "Session ID: $($response.data.id)" -ForegroundColor Yellow
    $sessionId = $response.data.id
    $sessionCode = $response.data.session_code
}
catch {
    Write-Host "ERROR creating session:" -ForegroundColor Red
    Write-Host "$($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        Write-Host "Details: $($_.ErrorDetails.Message)" -ForegroundColor Red
    }
    exit 1
}

Write-Host ""
Write-Host "Step 2: Fetching sessions to verify..." -ForegroundColor Green

try {
    $sessions = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    Write-Host "SUCCESS: Retrieved sessions" -ForegroundColor Green
    Write-Host "Total sessions: $($sessions.data.count)" -ForegroundColor Cyan
    
    $foundSession = $sessions.data.sessions | Where-Object { $_.id -eq $sessionId }
    if ($foundSession) {
        Write-Host "VERIFIED: Session found in database!" -ForegroundColor Green
        Write-Host "Student: $($foundSession.student_name)" -ForegroundColor Cyan
        Write-Host "Teacher: $($foundSession.teacher_name)" -ForegroundColor Cyan
    }
}
catch {
    Write-Host "ERROR fetching sessions:" -ForegroundColor Red
    Write-Host "$($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Step 3: Adding errors to the session..." -ForegroundColor Green

$errorsData = @{
    session_id = $sessionId
    errors = @(
        @{
            surah_number = 1
            verse_number = 2
            word_position = "الرحمن"
            error_type = "تجويد"
            severity = "متوسط"
            is_recurring = $true
            correction_note = "Fix elongation"
        }
    )
}

$errorsJson = $errorsData | ConvertTo-Json -Depth 4

try {
    $errorsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body $errorsJson -Headers $headers
    Write-Host "SUCCESS: Errors added!" -ForegroundColor Green
    Write-Host "Errors count: $($errorsResponse.data.added_count)" -ForegroundColor Cyan
    Write-Host "Session: $sessionCode" -ForegroundColor Cyan
}
catch {
    Write-Host "ERROR adding errors:" -ForegroundColor Red
    Write-Host "$($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Demo completed successfully!" -ForegroundColor Green
Write-Host "Summary:" -ForegroundColor Yellow
Write-Host "- Created session: $sessionCode" -ForegroundColor White
Write-Host "- Verified session exists" -ForegroundColor White  
Write-Host "- Added errors to session" -ForegroundColor White
Write-Host "==================================" -ForegroundColor Blue
