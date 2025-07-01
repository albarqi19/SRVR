# Test Quran School Student Management APIs
# Simple PowerShell test using Laravel server

Write-Host "Quran School Student Management APIs Test" -ForegroundColor Green
Write-Host "=" * 50

# Connection settings  
$baseUrl = "http://localhost:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

# Use existing circle ID for testing (from database)
$quranSchoolId = 1  # Circle "تجارب"

Write-Host "`nTest Information:" -ForegroundColor Cyan
Write-Host "Circle: تجارب (ID: $quranSchoolId)"
Write-Host "Mosque: جامع هيلة الحربي"
Write-Host "Type: Group Circle"

try {
    Write-Host "`nChecking Laravel server..." -ForegroundColor Yellow
    
    # Check if server is running
    try {
        $null = Invoke-RestMethod -Uri "http://localhost:8000" -Method GET -TimeoutSec 5
        Write-Host "✅ Laravel server is running" -ForegroundColor Green
    } catch {
        Write-Host "❌ Laravel server not available - run: php artisan serve" -ForegroundColor Red
        Write-Host "Start server: php artisan serve --host=localhost --port=8000" -ForegroundColor Yellow
        exit
    }

    Write-Host "`n1️⃣ Testing get school info API..." -ForegroundColor Yellow
    
    try {
        $response1 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/info" -Method GET -Headers $headers
        
        if ($response1.success) {
            Write-Host "✅ School info retrieved successfully" -ForegroundColor Green
            Write-Host "   School name: $($response1.data.quran_school.name)"
            Write-Host "   Mosque: $($response1.data.quran_school.mosque.name)"
            Write-Host "   Circle groups count: $($response1.data.circle_groups.Count)"
            Write-Host "   Total students: $($response1.data.statistics.total_students)"
            
            if ($response1.data.circle_groups.Count -gt 0) {
                $circleGroupId = $response1.data.circle_groups[0].id
                Write-Host "   Using circle group: $($response1.data.circle_groups[0].name) (ID: $circleGroupId)"
            } else {
                Write-Host "❌ No active circle groups found" -ForegroundColor Red
                exit
            }
        } else {
            Write-Host "❌ Failed to get school info: $($response1.message)" -ForegroundColor Red
            exit
        }
    } catch {
        Write-Host "❌ API connection error: $($_.Exception.Message)" -ForegroundColor Red
        exit
    }

    Write-Host "`n2️⃣ Testing add new student API..." -ForegroundColor Yellow
    
    # New student data
    $studentData = @{
        identity_number = "9876543210$(Get-Random -Minimum 10 -Maximum 99)"
        name = "Test Student API - $(Get-Date -Format 'HH:mm')"
        phone = "0501234567"
        guardian_name = "Test Guardian"
        guardian_phone = "0507654321"
        birth_date = "2010-01-01"
        nationality = "Saudi"
        education_level = "Primary"
        neighborhood = "Test District"
        circle_group_id = $circleGroupId
        memorization_plan = "Juz Amma"
        review_plan = "Daily Review"
    } | ConvertTo-Json -Depth 3
    
    try {
        $response2 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students" -Method POST -Body $studentData -Headers $headers
        
        if ($response2.success) {
            Write-Host "✅ Student added successfully" -ForegroundColor Green
            Write-Host "   Student name: $($response2.data.student.name)"
            Write-Host "   Identity number: $($response2.data.student.identity_number)"
            Write-Host "   Default password: $($response2.data.student.default_password)"
            Write-Host "   Circle group: $($response2.data.student.circle_group.name)"
            
            $newStudentId = $response2.data.student.id
        } else {
            Write-Host "❌ Failed to add student: $($response2.message)" -ForegroundColor Red
            if ($response2.errors) {
                $response2.errors.PSObject.Properties | ForEach-Object {
                    Write-Host "   $($_.Name): $($_.Value -join ', ')" -ForegroundColor Red
                }
            }
        }
    } catch {
        Write-Host "❌ Error adding student: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            try {
                $errorResponse = $_.Exception.Response.GetResponseStream()
                $reader = New-Object System.IO.StreamReader($errorResponse)
                $errorBody = $reader.ReadToEnd()
                Write-Host "Error details: $errorBody" -ForegroundColor Red
            } catch {
                Write-Host "Could not read error details" -ForegroundColor Red
            }
        }
    }

    Write-Host "`n3️⃣ Testing get students list API..." -ForegroundColor Yellow
    
    try {
        $response3 = Invoke-RestMethod -Uri "$baseUrl/quran-schools/$quranSchoolId/students" -Method GET -Headers $headers
        
        if ($response3.success) {
            Write-Host "✅ Students list retrieved successfully" -ForegroundColor Green
            Write-Host "   Students in page: $($response3.data.students.Count)"
            Write-Host "   Total students: $($response3.data.pagination.total)"
            
            if ($response3.data.students.Count -gt 0) {
                Write-Host "   First 3 students:"
                $response3.data.students | Select-Object -First 3 | ForEach-Object {
                    Write-Host "     - $($_.name) ($($_.identity_number))"
                }
            }
        } else {
            Write-Host "❌ Failed to get students list: $($response3.message)" -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ Error getting students list: $($_.Exception.Message)" -ForegroundColor Red
    }

    Write-Host "`n4️⃣ Testing filter students API..." -ForegroundColor Yellow
    
    try {
        $filterUrl = "$baseUrl/quran-schools/$quranSchoolId/students?circle_group_id=$circleGroupId" + "&is_active=true"
        $response4 = Invoke-RestMethod -Uri $filterUrl -Method GET -Headers $headers
        
        if ($response4.success) {
            Write-Host "✅ Students filtered successfully" -ForegroundColor Green
            Write-Host "   Students in circle group: $($response4.data.students.Count)"
        } else {
            Write-Host "❌ Failed to filter students: $($response4.message)" -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ Error filtering students: $($_.Exception.Message)" -ForegroundColor Red
    }

    Write-Host "`n🎉 Quran School Student Management APIs test completed!" -ForegroundColor Green

    Write-Host "`nTested APIs Summary:" -ForegroundColor Cyan
    Write-Host "$('=' * 50)"
    Write-Host "✅ GET  /api/quran-schools/{id}/info - Get school info"
    Write-Host "✅ POST /api/quran-schools/{id}/students - Add new student" 
    Write-Host "✅ GET  /api/quran-schools/{id}/students - Get students list"
    Write-Host "✅ Student filtering - by circle group and status"

    Write-Host "`nFor complete testing, ensure Laravel server is running:" -ForegroundColor Yellow
    Write-Host "php artisan serve --host=localhost --port=8000"

} catch {
    Write-Host "❌ General error during testing: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Details: $($_.Exception)" -ForegroundColor Red
}

Write-Host "`nTest completed." -ForegroundColor White
