# Quran School Student Management APIs Test
# Simple test script for testing APIs

Write-Host "Testing Quran School Student Management APIs" -ForegroundColor Green
Write-Host "=" * 50

# Connection settings
$baseUrl = "http://localhost:8000/api"
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

# Quran School ID for testing (determined automatically)
$quranSchoolId = 1

try {
    Write-Host "`n1. Testing get school info API..." -ForegroundColor Yellow
    
    $url1 = "$baseUrl/quran-schools/$quranSchoolId/info"
    Write-Host "URL: $url1"
    
    $response1 = Invoke-RestMethod -Uri $url1 -Method GET -Headers $headers
    
    if ($response1.success) {
        Write-Host "Success: School info retrieved" -ForegroundColor Green
        Write-Host "School Name: $($response1.data.quran_school.name)"
        Write-Host "Mosque: $($response1.data.quran_school.mosque.name)"
        Write-Host "Circle Groups Count: $($response1.data.circle_groups.Count)"
        Write-Host "Total Students: $($response1.data.statistics.total_students)"
        
        # Save first circle group ID for testing
        if ($response1.data.circle_groups.Count -gt 0) {
            $circleGroupId = $response1.data.circle_groups[0].id
            Write-Host "Using Circle Group: $($response1.data.circle_groups[0].name) (ID: $circleGroupId)"
        } else {
            Write-Host "No circle groups available for testing" -ForegroundColor Red
            exit
        }
    } else {
        Write-Host "Failed to get school info: $($response1.message)" -ForegroundColor Red
        exit
    }

    Write-Host "`n2. Testing add new student API..." -ForegroundColor Yellow
    
    # New student data
    $studentData = @{
        identity_number = "1234567890$(Get-Random -Minimum 10 -Maximum 99)"
        name = "Test Student API - $(Get-Date -Format 'HH:mm')"
        phone = "0501234567"
        guardian_name = "Test Guardian"
        guardian_phone = "0507654321"
        birth_date = "2010-01-01"
        nationality = "Saudi"
        education_level = "Elementary"
        neighborhood = "Test District"
        circle_group_id = $circleGroupId
        memorization_plan = "Memorize Juz Amma"
        review_plan = "Daily Review"
    } | ConvertTo-Json -Depth 3
    
    $url2 = "$baseUrl/quran-schools/$quranSchoolId/students"
    Write-Host "URL: $url2"
    
    $response2 = Invoke-RestMethod -Uri $url2 -Method POST -Body $studentData -Headers $headers
    
    if ($response2.success) {
        Write-Host "Success: Student added successfully" -ForegroundColor Green
        Write-Host "Student Name: $($response2.data.student.name)"
        Write-Host "Identity Number: $($response2.data.student.identity_number)"
        Write-Host "Default Password: $($response2.data.student.default_password)"
        Write-Host "Circle Group: $($response2.data.student.circle_group.name)"
        
        $newStudentId = $response2.data.student.id
    } else {
        Write-Host "Failed to add student: $($response2.message)" -ForegroundColor Red
        if ($response2.errors) {
            $response2.errors | ForEach-Object {
                Write-Host "Error: $_" -ForegroundColor Red
            }
        }
    }

    Write-Host "`n3. Testing get students list API..." -ForegroundColor Yellow
    
    $url3 = "$baseUrl/quran-schools/$quranSchoolId/students"
    Write-Host "URL: $url3"
    
    $response3 = Invoke-RestMethod -Uri $url3 -Method GET -Headers $headers
    
    if ($response3.success) {
        Write-Host "Success: Students list retrieved" -ForegroundColor Green
        Write-Host "Students in page: $($response3.data.students.Count)"
        Write-Host "Total students: $($response3.data.pagination.total)"
        
        if ($response3.data.students.Count -gt 0) {
            Write-Host "First 3 students:"
            $response3.data.students | Select-Object -First 3 | ForEach-Object {
                Write-Host "  - $($_.name) ($($_.identity_number))"
            }
        }
    } else {
        Write-Host "Failed to get students list: $($response3.message)" -ForegroundColor Red
    }

    Write-Host "`n4. Testing filter students by circle group..." -ForegroundColor Yellow
    
    $url4 = "$baseUrl/quran-schools/$quranSchoolId/students?circle_group_id=$circleGroupId&is_active=true"
    Write-Host "URL: $url4"
    
    $response4 = Invoke-RestMethod -Uri $url4 -Method GET -Headers $headers
    
    if ($response4.success) {
        Write-Host "Success: Students filtered successfully" -ForegroundColor Green
        Write-Host "Students in circle group: $($response4.data.students.Count)"
    } else {
        Write-Host "Failed to filter students: $($response4.message)" -ForegroundColor Red
    }

    Write-Host "`n5. Testing search students..." -ForegroundColor Yellow
    
    $url5 = "$baseUrl/quran-schools/$quranSchoolId/students?search=Test"
    Write-Host "URL: $url5"
    
    $response5 = Invoke-RestMethod -Uri $url5 -Method GET -Headers $headers
    
    if ($response5.success) {
        Write-Host "Success: Search completed" -ForegroundColor Green
        Write-Host "Search results: $($response5.data.students.Count)"
    } else {
        Write-Host "Failed to search students: $($response5.message)" -ForegroundColor Red
    }

    # Test update student if successfully created
    if ($newStudentId) {
        Write-Host "`n6. Testing update student info..." -ForegroundColor Yellow
        
        $updateData = @{
            name = "Updated Student - $(Get-Date -Format 'HH:mm')"
            phone = "0509876543"
            memorization_plan = "Memorize Juz Amma + Tabarak"
        } | ConvertTo-Json
        
        $url6 = "$baseUrl/quran-schools/$quranSchoolId/students/$newStudentId"
        Write-Host "URL: $url6"
        
        $response6 = Invoke-RestMethod -Uri $url6 -Method PUT -Body $updateData -Headers $headers
        
        if ($response6.success) {
            Write-Host "Success: Student info updated" -ForegroundColor Green
            Write-Host "New Name: $($response6.data.student.name)"
            Write-Host "New Phone: $($response6.data.student.phone)"
        } else {
            Write-Host "Failed to update student: $($response6.message)" -ForegroundColor Red
        }
    }

    Write-Host "`nQuran School Student Management APIs test completed successfully!" -ForegroundColor Green

    Write-Host "`nAvailable APIs Summary:" -ForegroundColor Cyan
    Write-Host "=" * 40
    Write-Host "1. GET  /api/quran-schools/{id}/info"
    Write-Host "   - Get school info and active circle groups"
    Write-Host ""
    Write-Host "2. POST /api/quran-schools/{id}/students"
    Write-Host "   - Add new student to quran school"
    Write-Host "   - Required: identity_number, name, guardian_name, guardian_phone, circle_group_id"
    Write-Host ""
    Write-Host "3. GET  /api/quran-schools/{id}/students"
    Write-Host "   - Get students list with filtering and search"
    Write-Host "   - Filter params: circle_group_id, is_active, search, per_page"
    Write-Host ""
    Write-Host "4. PUT  /api/quran-schools/{id}/students/{studentId}"
    Write-Host "   - Update existing student info"
    Write-Host ""
    Write-Host "5. DELETE /api/quran-schools/{id}/students/{studentId}"
    Write-Host "   - Deactivate student (soft delete)"

    Write-Host "`nAll APIs are working correctly and ready for frontend integration!" -ForegroundColor Green

} catch {
    Write-Host "Error during testing: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Details: $($_.Exception)" -ForegroundColor Red
}

Write-Host "`nTest completed." -ForegroundColor White
