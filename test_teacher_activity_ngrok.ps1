# Teacher Daily Activity API Test with ngrok
Write-Host "Testing Teacher Daily Activity API (ngrok)" -ForegroundColor Cyan
Write-Host ("=" * 60)

$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app/api"
$supervisorId = 1
$date = Get-Date -Format "yyyy-MM-dd"

# Headers with ngrok warning bypass
$headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
    "ngrok-skip-browser-warning" = "true"
}

Write-Host ""
Write-Host "Test 1: Get Teachers Daily Activity for ($date)" -ForegroundColor Yellow
Write-Host ("-" * 50)

try {
    $url = "$baseUrl/test/teachers-daily-activity?supervisor_id=$supervisorId&date=$date"
    Write-Host "Request URL: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "SUCCESS!" -ForegroundColor Green
        Write-Host ""
        
        Write-Host "Activity Summary:" -ForegroundColor Blue
        $summary = $response.data.summary
        Write-Host "   Total Teachers: $($summary.total_teachers)"
        Write-Host "   Active Teachers: $($summary.active_teachers)"
        Write-Host "   Attendance Recorded: $($summary.attendance_recorded)"
        Write-Host "   Recitation Recorded: $($summary.recitation_recorded)"
        Write-Host "   Completion Rate: $($summary.completion_rate)%"
        Write-Host "   Attendance Percentage: $($summary.attendance_percentage)%"
        Write-Host "   Recitation Percentage: $($summary.recitation_percentage)%"
        
        Write-Host ""
        Write-Host "Teachers Details:" -ForegroundColor Blue
        
        if ($response.data.teachers_activity.Count -eq 0) {
            Write-Host "   No teacher data found" -ForegroundColor Yellow
        } else {
            foreach ($teacher in $response.data.teachers_activity[0..4]) {  # Show first 5 teachers only
                $activity = $teacher.daily_activity
                Write-Host "   Teacher: $($teacher.teacher_name)"
                Write-Host "      Circle: $($teacher.circle.name)"
                Write-Host "      Status: $($activity.activity_status)"
                Write-Host "      Attendance: $(if($activity.attendance_recorded) { 'Done (' + $activity.attendance_percentage + '%)' } else { 'Not Done' })"
                Write-Host "      Recitation: $(if($activity.recitation_recorded) { 'Done (' + $activity.recitation_percentage + '%)' } else { 'Not Done' })"
                Write-Host "      Summary: $($activity.details.completion_summary)"
                Write-Host ""
            }
            
            if ($response.data.teachers_activity.Count -gt 5) {
                Write-Host "   ... and $($response.data.teachers_activity.Count - 5) more teachers"
            }
        }
        
    } else {
        Write-Host "API Error: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Test 2: Teachers Activity Statistics (Last Week)" -ForegroundColor Yellow
Write-Host ("-" * 50)

try {
    $startDate = (Get-Date).AddDays(-7).ToString("yyyy-MM-dd")
    $endDate = (Get-Date).ToString("yyyy-MM-dd")
    
    $url = "$baseUrl/test/teachers-activity-statistics?supervisor_id=$supervisorId&start_date=$startDate&end_date=$endDate"
    Write-Host "Request URL: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "SUCCESS!" -ForegroundColor Green
        Write-Host ""
        
        $period = $response.data.period
        Write-Host "Period: From $($period.start) to $($period.end) ($($period.total_days) days)" -ForegroundColor Blue
        
        $summary = $response.data.overall_summary
        Write-Host ""
        Write-Host "Overall Summary:" -ForegroundColor Blue
        Write-Host "   Total Teachers: $($summary.total_teachers)"
        Write-Host "   Average Attendance Days: $($summary.average_attendance_days)"
        Write-Host "   Average Recitation Days: $($summary.average_recitation_days)"
        Write-Host "   Average Performance Score: $($summary.average_performance_score)"
        Write-Host "   Attendance Rate: $($summary.attendance_rate)%"
        Write-Host "   Recitation Rate: $($summary.recitation_rate)%"
        
        Write-Host ""
        Write-Host "Grade Distribution:" -ForegroundColor Blue
        if ($summary.grade_distribution) {
            foreach ($grade in $summary.grade_distribution.PSObject.Properties) {
                Write-Host "   $($grade.Name): $($grade.Value) teachers"
            }
        }
        
        Write-Host ""
        Write-Host "Sample Teacher Statistics:" -ForegroundColor Blue
        
        if ($response.data.teachers_statistics.Count -eq 0) {
            Write-Host "   No statistics available" -ForegroundColor Yellow
        } else {
            foreach ($teacher in $response.data.teachers_statistics[0..2]) {  # Show first 3 teachers only
                $stats = $teacher.statistics
                $grade = $teacher.performance_grade
                Write-Host "   Teacher: $($teacher.teacher_name)"
                Write-Host "      Attendance Days: $($stats.attendance_days)"
                Write-Host "      Recitation Days: $($stats.recitation_days)"
                Write-Host "      Active Days: $($stats.active_days)"
                Write-Host "      Total Recitation Sessions: $($stats.total_recitation_sessions)"
                Write-Host "      Performance Score: $($grade.score) ($($grade.grade))"
                Write-Host ""
            }
        }
        
    } else {
        Write-Host "API Error: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Test 3: Yesterday's Data" -ForegroundColor Yellow
Write-Host ("-" * 50)

try {
    $yesterdayDate = (Get-Date).AddDays(-1).ToString("yyyy-MM-dd")
    
    $url = "$baseUrl/test/teachers-daily-activity?supervisor_id=$supervisorId&date=$yesterdayDate"
    Write-Host "Request URL: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "SUCCESS!" -ForegroundColor Green
        
        $summary = $response.data.summary
        Write-Host "Yesterday's Summary ($yesterdayDate):"
        Write-Host "   Total Teachers: $($summary.total_teachers)"
        Write-Host "   Active Teachers: $($summary.active_teachers)"
        Write-Host "   Completion Rate: $($summary.completion_rate)%"
        
    } else {
        Write-Host "API Error: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host ("=" * 60)
Write-Host "Teacher Activity API Test Completed" -ForegroundColor Green
Write-Host ""
Write-Host "Now you can use this API in your dashboard to monitor:" -ForegroundColor Cyan
Write-Host "   • Did the teacher record student attendance?"
Write-Host "   • Did the teacher enter recitation sessions?"
Write-Host "   • What is their daily completion rate?"
Write-Host "   • Performance statistics for a specific period"
