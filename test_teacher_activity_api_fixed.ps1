# اختبار API تتبع نشاط المعلمين اليومي
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "🔍 اختبار API تتبع نشاط المعلمين اليومي" -ForegroundColor Cyan
Write-Host "=" * 60

$baseUrl = "http://127.0.0.1:8000/api"
$supervisorId = 1
$date = Get-Date -Format "yyyy-MM-dd"

# Headers
$headers = @{
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host ""
Write-Host "📊 اختبار 1: جلب نشاط المعلمين لليوم ($date)" -ForegroundColor Yellow
Write-Host "-" * 50

try {
    $url = "$baseUrl/supervisors/teachers-daily-activity?supervisor_id=$supervisorId&date=$date"
    Write-Host "🌐 الطلب: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "✅ نجح الطلب!" -ForegroundColor Green
        Write-Host ""
        
        Write-Host "📈 ملخص النشاط:" -ForegroundColor Blue
        $summary = $response.data.summary
        Write-Host "   📊 إجمالي المعلمين: $($summary.total_teachers)"
        Write-Host "   🟢 المعلمين النشطين: $($summary.active_teachers)"
        Write-Host "   📝 سجلوا الحضور: $($summary.attendance_recorded)"
        Write-Host "   🎤 سجلوا التسميع: $($summary.recitation_recorded)"
        Write-Host "   📈 معدل الإنجاز: $($summary.completion_rate)%"
        Write-Host "   📋 نسبة التحضير: $($summary.attendance_percentage)%"
        Write-Host "   🎯 نسبة التسميع: $($summary.recitation_percentage)%"
        
        Write-Host ""
        Write-Host "👥 تفاصيل المعلمين:" -ForegroundColor Blue
        
        if ($response.data.teachers_activity.Count -eq 0) {
            Write-Host "   ⚠️  لا توجد بيانات معلمين" -ForegroundColor Yellow
        } else {
            $teachersToShow = [Math]::Min(5, $response.data.teachers_activity.Count)
            for ($i = 0; $i -lt $teachersToShow; $i++) {
                $teacher = $response.data.teachers_activity[$i]
                $activity = $teacher.daily_activity
                Write-Host "   📋 $($teacher.teacher_name):"
                Write-Host "      🏢 الحلقة: $($teacher.circle.name)"
                Write-Host "      📊 الحالة: $($activity.activity_status)"
                
                $attendanceStatus = if($activity.attendance_recorded) { "تم ($($activity.attendance_percentage)%)" } else { "لم يتم" }
                Write-Host "      ✅ التحضير: $attendanceStatus"
                
                $recitationStatus = if($activity.recitation_recorded) { "تم ($($activity.recitation_percentage)%)" } else { "لم يتم" }
                Write-Host "      🎤 التسميع: $recitationStatus"
                
                Write-Host "      💡 الملخص: $($activity.details.completion_summary)"
                Write-Host ""
            }
            
            if ($response.data.teachers_activity.Count -gt 5) {
                Write-Host "   ... و $($response.data.teachers_activity.Count - 5) معلمين آخرين"
            }
        }
        
    } else {
        Write-Host "❌ فشل الطلب: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ في الطلب: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "تفاصيل الخطأ: $($_.Exception)" -ForegroundColor Red
}

Write-Host ""
Write-Host "📈 اختبار 2: إحصائيات المعلمين للأسبوع الماضي" -ForegroundColor Yellow
Write-Host "-" * 50

try {
    $startDate = (Get-Date).AddDays(-7).ToString("yyyy-MM-dd")
    $endDate = (Get-Date).ToString("yyyy-MM-dd")
    
    $url = "$baseUrl/supervisors/teachers-activity-statistics?supervisor_id=$supervisorId&start_date=$startDate&end_date=$endDate"
    Write-Host "🌐 الطلب: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "✅ نجح الطلب!" -ForegroundColor Green
        Write-Host ""
        
        $period = $response.data.period
        Write-Host "📅 الفترة: من $($period.start) إلى $($period.end) ($($period.total_days) أيام)" -ForegroundColor Blue
        
        $summary = $response.data.overall_summary
        Write-Host ""
        Write-Host "📊 الملخص الإجمالي:" -ForegroundColor Blue
        Write-Host "   👥 إجمالي المعلمين: $($summary.total_teachers)"
        Write-Host "   📅 متوسط أيام التحضير: $($summary.average_attendance_days)"
        Write-Host "   🎤 متوسط أيام التسميع: $($summary.average_recitation_days)"
        Write-Host "   🎯 متوسط درجة الأداء: $($summary.average_performance_score)"
        Write-Host "   📋 معدل التحضير: $($summary.attendance_rate)%"
        Write-Host "   🎯 معدل التسميع: $($summary.recitation_rate)%"
        
        Write-Host ""
        Write-Host "📈 توزيع الدرجات:" -ForegroundColor Blue
        if ($summary.grade_distribution) {
            foreach ($grade in $summary.grade_distribution.PSObject.Properties) {
                Write-Host "   $($grade.Name): $($grade.Value) معلم"
            }
        }
        
        Write-Host ""
        Write-Host "👤 عينة من إحصائيات المعلمين:" -ForegroundColor Blue
        
        if ($response.data.teachers_statistics.Count -eq 0) {
            Write-Host "   ⚠️  لا توجد إحصائيات" -ForegroundColor Yellow
        } else {
            $teachersToShow = [Math]::Min(3, $response.data.teachers_statistics.Count)
            for ($i = 0; $i -lt $teachersToShow; $i++) {
                $teacher = $response.data.teachers_statistics[$i]
                $stats = $teacher.statistics
                $grade = $teacher.performance_grade
                Write-Host "   📋 $($teacher.teacher_name):"
                Write-Host "      📅 أيام التحضير: $($stats.attendance_days)"
                Write-Host "      🎤 أيام التسميع: $($stats.recitation_days)"
                Write-Host "      🔥 الأيام النشطة: $($stats.active_days)"
                Write-Host "      📊 جلسات التسميع: $($stats.total_recitation_sessions)"
                Write-Host "      🎯 درجة الأداء: $($grade.score) ($($grade.grade))"
                Write-Host ""
            }
        }
        
    } else {
        Write-Host "❌ فشل الطلب: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ في الطلب: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "🧪 اختبار 3: تجربة البيانات بتاريخ أمس" -ForegroundColor Yellow
Write-Host "-" * 50

try {
    $yesterdayDate = (Get-Date).AddDays(-1).ToString("yyyy-MM-dd")
    
    $url = "$baseUrl/supervisors/teachers-daily-activity?supervisor_id=$supervisorId&date=$yesterdayDate"
    Write-Host "🌐 الطلب: $url" -ForegroundColor Gray
    
    $response = Invoke-RestMethod -Uri $url -Method GET -Headers $headers
    
    if ($response.success) {
        Write-Host "✅ نجح الطلب!" -ForegroundColor Green
        
        $summary = $response.data.summary
        Write-Host "📊 ملخص أمس ($yesterdayDate):"
        Write-Host "   📊 إجمالي المعلمين: $($summary.total_teachers)"
        Write-Host "   🟢 المعلمين النشطين: $($summary.active_teachers)"
        Write-Host "   📈 معدل الإنجاز: $($summary.completion_rate)%"
        
    } else {
        Write-Host "❌ فشل الطلب: $($response.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ في الطلب: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=" * 60
Write-Host "🎉 انتهى اختبار API تتبع نشاط المعلمين" -ForegroundColor Green
Write-Host ""
Write-Host "💡 الآن يمكنك استخدام هذا API في لوحة التحكم لمتابعة:" -ForegroundColor Cyan
Write-Host "   • هل قام المعلم بتسجيل حضور الطلاب؟" 
Write-Host "   • هل قام بإدخال جلسات التسميع؟"
Write-Host "   • ما هي نسبة إنجازه اليومي؟"
Write-Host "   • إحصائيات الأداء لفترة معينة"
