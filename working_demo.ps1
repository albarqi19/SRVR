# API Live Demo - Working Version
# تاريخ الإنشاء: 9 يونيو 2025

Write-Host "🔥 API Live Demo - RecitationSessions" -ForegroundColor Red
Write-Host "=====================================" -ForegroundColor Blue

$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host "`n🚀 Step 1: Creating a new recitation session..." -ForegroundColor Green

$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    session_date = "2024-06-09"
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "Live demo session created via PowerShell API"
} | ConvertTo-Json -Depth 3

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $sessionData -Headers $headers
    Write-Host "✅ SUCCESS: Session created!" -ForegroundColor Green
    Write-Host "   📋 Session Code: $($response.data.session_code)" -ForegroundColor Cyan
    Write-Host "   🆔 Session ID: $($response.data.id)" -ForegroundColor Yellow
    $sessionId = $response.data.id
    $sessionCode = $response.data.session_code
} catch {
    Write-Host "❌ ERROR creating session:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
        Write-Host "   Validation errors:" -ForegroundColor Red
        $errorDetails.errors.PSObject.Properties | ForEach-Object {
            Write-Host "     - $($_.Name): $($_.Value -join ', ')" -ForegroundColor Red
        }
    }
    exit 1
}

Write-Host "`n🔍 Step 2: Fetching session to verify creation..." -ForegroundColor Green

try {
    $sessions = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    Write-Host "✅ SUCCESS: Retrieved sessions data" -ForegroundColor Green
    Write-Host "   📊 Total sessions in database: $($sessions.data.count)" -ForegroundColor Cyan
    
    $foundSession = $sessions.data.sessions | Where-Object { $_.id -eq $sessionId }
    if ($foundSession) {
        Write-Host "✅ VERIFIED: Our session found in database!" -ForegroundColor Green
        Write-Host "   📋 Code: $($foundSession.session_code)" -ForegroundColor Cyan
        Write-Host "   👨‍🎓 Student: $($foundSession.student_name)" -ForegroundColor Cyan
        Write-Host "   👨‍🏫 Teacher: $($foundSession.teacher_name)" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️ WARNING: Session not found in list" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ ERROR fetching sessions:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n📝 Step 3: Adding errors to the session..." -ForegroundColor Green

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
            correction_note = "تحسين مد الألف في كلمة الرحمن"
        },
        @{
            surah_number = 1
            verse_number = 3
            word_position = "الرحيم"
            error_type = "مخارج"
            severity = "خفيف"
            is_recurring = $false
            correction_note = "تحسين نطق حرف الحاء"
        }
    )
} | ConvertTo-Json -Depth 4

try {
    $errorsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body $errorsData -Headers $headers
    Write-Host "✅ SUCCESS: Errors added to session!" -ForegroundColor Green
    Write-Host "   📊 Errors added: $($errorsResponse.data.added_count)" -ForegroundColor Cyan
    Write-Host "   📋 To session: $sessionCode" -ForegroundColor Cyan
} catch {
    Write-Host "❌ ERROR adding errors:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        Write-Host "   Details: $($_.ErrorDetails.Message)" -ForegroundColor Red
    }
}

Write-Host "`n🎉 Demo completed successfully!" -ForegroundColor Green
Write-Host "📋 Summary:" -ForegroundColor Yellow
Write-Host "   ✅ Created session: $sessionCode" -ForegroundColor White
Write-Host "   ✅ Verified session exists in database" -ForegroundColor White  
Write-Host "   ✅ Added errors to the session" -ForegroundColor White
Write-Host "=====================================" -ForegroundColor Blue
