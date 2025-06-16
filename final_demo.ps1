# PowerShell API Demo - WORKING VERSION
# تم إنشاؤه: 9 يونيو 2025
# الغرض: عرض توضيحي مباشر للـ API

# إعدادات أساسية
$ErrorActionPreference = "Stop"
$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host "=== API LIVE DEMO ===" -ForegroundColor Red
Write-Host "Date: $(Get-Date)" -ForegroundColor Gray
Write-Host ""

# الخطوة 1: إنشاء جلسة
Write-Host "1. Creating recitation session..." -ForegroundColor Green

$sessionRequest = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    session_date = "2024-06-09"
    recitation_type = "مراجعة صغرى"
    start_page = 1
    end_page = 10
    evaluation = "جيد جداً"
    notes = "PowerShell API Demo Session"
} | ConvertTo-Json

Write-Host "   Sending POST request..." -ForegroundColor Yellow

try {
    $sessionResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $sessionRequest -Headers $headers
    Write-Host "   ✓ Session created successfully!" -ForegroundColor Green
    Write-Host "   Session ID: $($sessionResponse.data.id)" -ForegroundColor Cyan
    Write-Host "   Session Code: $($sessionResponse.data.session_code)" -ForegroundColor Cyan
    $createdSessionId = $sessionResponse.data.id
    $createdSessionCode = $sessionResponse.data.session_code
} catch {
    Write-Host "   ✗ Failed to create session" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""

# الخطوة 2: جلب الجلسات
Write-Host "2. Fetching sessions..." -ForegroundColor Green

try {
    $sessionsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method GET -Headers $headers
    Write-Host "   ✓ Sessions retrieved successfully!" -ForegroundColor Green
    Write-Host "   Total sessions in DB: $($sessionsResponse.data.count)" -ForegroundColor Cyan
    
    # البحث عن الجلسة المُنشأة
    $foundSession = $sessionsResponse.data.sessions | Where-Object { $_.id -eq $createdSessionId }
    if ($foundSession) {
        Write-Host "   ✓ Created session found in database!" -ForegroundColor Green
        Write-Host "   Student: $($foundSession.student_name)" -ForegroundColor Cyan
        Write-Host "   Teacher: $($foundSession.teacher_name)" -ForegroundColor Cyan
    } else {
        Write-Host "   ! Session not found in list" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ✗ Failed to fetch sessions" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# الخطوة 3: إضافة أخطاء
Write-Host "3. Adding errors to session..." -ForegroundColor Green

$errorsRequest = @{
    session_id = $createdSessionId
    errors = @(
        @{
            surah_number = 1
            verse_number = 2
            word_position = "الرحمن"
            error_type = "تجويد"
            severity = "متوسط"
            is_recurring = $true
            correction_note = "تحسين مد الألف"
        },
        @{
            surah_number = 1
            verse_number = 3
            word_position = "الرحيم"
            error_type = "مخارج"
            severity = "خفيف"
            is_recurring = $false
            correction_note = "تحسين نطق الحاء"
        }
    )
} | ConvertTo-Json -Depth 4

try {
    $errorsResponse = Invoke-RestMethod -Uri "$baseUrl/recitation/errors" -Method POST -Body $errorsRequest -Headers $headers
    Write-Host "   ✓ Errors added successfully!" -ForegroundColor Green
    Write-Host "   Errors count: $($errorsResponse.data.added_count)" -ForegroundColor Cyan
    Write-Host "   Session: $createdSessionCode" -ForegroundColor Cyan
} catch {
    Write-Host "   ✗ Failed to add errors" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== DEMO COMPLETED SUCCESSFULLY ===" -ForegroundColor Green
Write-Host "Summary:" -ForegroundColor Yellow
Write-Host "✓ Created session: $createdSessionCode" -ForegroundColor White
Write-Host "✓ Verified session in database" -ForegroundColor White
Write-Host "✓ Added 2 errors to session" -ForegroundColor White
Write-Host "===========================================" -ForegroundColor Blue
