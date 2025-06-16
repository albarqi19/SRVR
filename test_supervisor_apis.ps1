# ملف اختبار لواجهات برمجة المشرف

# عنوان الخادم الرئيسي
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app/api"

# استعداد لحفظ معرف المستخدم والرمز المميز للاستخدام في الطلبات اللاحقة
$authToken = ""
$userId = 0
$teacherId = 0
$circleId = 0
$studentId = 0
$evaluationId = 0

# دالة لعرض النتائج بشكل منسق
function Format-Response {
    param (
        [Parameter(Mandatory=$true)]
        [PSObject]$Response,
        
        [Parameter(Mandatory=$true)]
        [string]$Title
    )
    
    Write-Host "`n============== $Title ==============" -ForegroundColor Cyan
    Write-Host "Status Code: $($Response.StatusCode)" -ForegroundColor Yellow
    
    try {
        $content = $Response.Content | ConvertFrom-Json
        Write-Host "Response:" -ForegroundColor Green
        $content | ConvertTo-Json -Depth 10 | Write-Host
        return $content
    }
    catch {
        Write-Host "Error parsing response:" -ForegroundColor Red
        Write-Host $Response.Content
        return $null
    }
}

# 1. تسجيل الدخول وإنشاء جلسة المصادقة
# --------------------------------------
function Test-Login {
    $loginData = @{
        email = "test_supervisor@example.com"
        password = "password"
    } | ConvertTo-Json
    
    $headers = @{
        "Content-Type" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method Post -Body $loginData -Headers $headers -ResponseHeadersVariable responseHeaders -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    Write-Host "`n============== تسجيل الدخول ==============" -ForegroundColor Cyan
    if ($response.success -eq $true) {
        Write-Host "✅ تم تسجيل الدخول بنجاح" -ForegroundColor Green
        Write-Host "توكن المصادقة: $($response.token)" -ForegroundColor Yellow
        $script:authToken = $response.token
        $script:userId = $response.user.id
        return $true
    } else {
        Write-Host "❌ فشل تسجيل الدخول" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json)
        return $false
    }
}

# 2. اختبار الحصول على الحلقات المشرف عليها
# --------------------------------------
function Test-GetCircles {
    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/circles" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "الحلقات المشرف عليها"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true -and $formattedResponse.data.Count -gt 0) {
        Write-Host "✅ تم جلب الحلقات بنجاح، عدد الحلقات: $($formattedResponse.data.Count)" -ForegroundColor Green
        $script:circleId = $formattedResponse.data[0].id
        Write-Host "تم حفظ معرف الحلقة الأولى: $circleId" -ForegroundColor Yellow
        return $true
    } else {
        Write-Host "❌ فشل في جلب الحلقات" -ForegroundColor Red
        return $false
    }
}

# 3. اختبار الحصول على طلاب حلقة محددة
# --------------------------------------
function Test-GetCircleStudents {
    param (
        [int]$CircleId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/circles/$CircleId/students" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "طلاب الحلقة رقم $CircleId"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم جلب طلاب الحلقة بنجاح" -ForegroundColor Green
        if ($formattedResponse.data.Count -gt 0) {
            $script:studentId = $formattedResponse.data[0].id
            Write-Host "تم حفظ معرف الطالب الأول: $studentId" -ForegroundColor Yellow
        }
        return $true
    } else {
        Write-Host "❌ فشل في جلب طلاب الحلقة" -ForegroundColor Red
        return $false
    }
}

# 4. اختبار الحصول على معلمي حلقة محددة
# --------------------------------------
function Test-GetCircleTeachers {
    param (
        [int]$CircleId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/circles/$CircleId/teachers" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "معلمي الحلقة رقم $CircleId"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم جلب معلمي الحلقة بنجاح" -ForegroundColor Green
        if ($formattedResponse.data.Count -gt 0) {
            $script:teacherId = $formattedResponse.data[0].id
            Write-Host "تم حفظ معرف المعلم الأول: $teacherId" -ForegroundColor Yellow
        }
        return $true
    } else {
        Write-Host "❌ فشل في جلب معلمي الحلقة" -ForegroundColor Red
        return $false
    }
}

# 5. اختبار إنشاء تقييم جديد لمعلم
# --------------------------------------
function Test-CreateTeacherEvaluation {
    param (
        [int]$TeacherId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $evaluationData = @{
        teacher_id = $TeacherId
        performance_score = 18
        attendance_score = 19
        student_interaction_score = 17
        behavior_cooperation_score = 18
        memorization_recitation_score = 16
        general_evaluation_score = 17
        notes = "تقييم ممتاز مع ملاحظة التحسن في أداء المعلم"
        evaluation_date = (Get-Date).ToString("yyyy-MM-dd")
        evaluation_period = "شهري"
        evaluator_role = "مشرف"
        status = "مسودة"
    } | ConvertTo-Json
    
    try {
        $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-evaluations" -Method Post -Headers $headers -Body $evaluationData -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
        $formattedResponse = Format-Response -Response $response -Title "إنشاء تقييم معلم"
    
        if ($formattedResponse -and $formattedResponse.success -eq $true) {
            Write-Host "✅ تم إنشاء تقييم المعلم بنجاح" -ForegroundColor Green
            $script:evaluationId = $formattedResponse.data.evaluation_id
            Write-Host "تم حفظ معرف التقييم: $evaluationId" -ForegroundColor Yellow
            return $true
        } else {
            Write-Host "❌ فشل في إنشاء تقييم المعلم" -ForegroundColor Red
            return $false
        }
    }
    catch {
        Write-Host "❌ خطأ في إنشاء تقييم المعلم: $_" -ForegroundColor Red
        return $false
    }
}

# 6. اختبار الحصول على تقييمات معلم محدد
# --------------------------------------
function Test-GetTeacherEvaluations {
    param (
        [int]$TeacherId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-evaluations/$TeacherId" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "تقييمات المعلم رقم $TeacherId"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم جلب تقييمات المعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في جلب تقييمات المعلم" -ForegroundColor Red
        return $false
    }
}

# 7. اختبار تحديث تقييم معلم
# --------------------------------------
function Test-UpdateTeacherEvaluation {
    param (
        [int]$EvaluationId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $evaluationData = @{
        performance_score = 19
        notes = "تم تحديث التقييم بعد ملاحظة تحسن الأداء"
        status = "مكتمل"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-evaluations/$EvaluationId" -Method Put -Headers $headers -Body $evaluationData -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "تحديث تقييم المعلم"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم تحديث تقييم المعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في تحديث تقييم المعلم" -ForegroundColor Red
        return $false
    }
}

# 8. اختبار اعتماد تقييم معلم
# --------------------------------------
function Test-ApproveTeacherEvaluation {
    param (
        [int]$EvaluationId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-evaluations/$EvaluationId/approve" -Method Post -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "اعتماد تقييم المعلم"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم اعتماد تقييم المعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في اعتماد تقييم المعلم" -ForegroundColor Red
        return $false
    }
}

# 9. اختبار تسجيل حضور معلم
# --------------------------------------
function Test-RecordTeacherAttendance {
    param (
        [int]$TeacherId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $attendanceData = @{
        teacher_id = $TeacherId
        status = "حاضر"
        attendance_date = (Get-Date).ToString("yyyy-MM-dd")
        notes = "حضر في الوقت المحدد"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-attendance" -Method Post -Headers $headers -Body $attendanceData -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "تسجيل حضور المعلم"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم تسجيل حضور المعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في تسجيل حضور المعلم" -ForegroundColor Red
        return $false
    }
}

# 10. اختبار طلب نقل طالب
# --------------------------------------
function Test-RequestStudentTransfer {
    param (
        [int]$StudentId,
        [int]$CurrentCircleId,
        [int]$TargetCircleId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $transferData = @{
        student_id = $StudentId
        current_circle_id = $CurrentCircleId
        requested_circle_id = $TargetCircleId
        transfer_reason = "رغبة الطالب في تغيير وقت الدراسة"
        notes = "طالب مجتهد ويستحق الموافقة على طلبه"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/student-transfer" -Method Post -Headers $headers -Body $transferData -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "طلب نقل الطالب"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم تقديم طلب نقل الطالب بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في تقديم طلب نقل الطالب" -ForegroundColor Red
        return $false
    }
}

# 11. اختبار الحصول على إحصائيات لوحة المعلومات
# --------------------------------------
function Test-GetDashboardStats {
    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/dashboard-stats" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "إحصائيات لوحة المعلومات"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم جلب إحصائيات لوحة المعلومات بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في جلب إحصائيات لوحة المعلومات" -ForegroundColor Red
        return $false
    }
}

# 12. اختبار إنشاء تقرير لمعلم
# --------------------------------------
function Test-CreateTeacherReport {
    param (
        [int]$TeacherId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Content-Type" = "application/json"
        "Accept" = "application/json"
    }
    
    $reportData = @{
        teacher_id = $TeacherId
        evaluation_score = 8.5
        performance_notes = "أداء المعلم جيد جداً مع الطلاب"
        attendance_notes = "ملتزم بالحضور في المواعيد المحددة"
        recommendations = "يمكن إعطاؤه مزيدًا من المسؤوليات في الحلقة"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-report" -Method Post -Headers $headers -Body $reportData -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "إنشاء تقرير للمعلم"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم إنشاء تقرير للمعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في إنشاء تقرير للمعلم" -ForegroundColor Red
        return $false
    }
}

# 13. اختبار الحصول على تقرير شامل لمعلم
# --------------------------------------
function Test-GetTeacherFullReport {
    param (
        [int]$TeacherId
    )

    $headers = @{
        "Authorization" = "Bearer $authToken"
        "Accept" = "application/json"
    }
    
    $response = Invoke-RestMethod -Uri "$baseUrl/supervisors/teacher-report/$TeacherId" -Method Get -Headers $headers -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    
    $formattedResponse = Format-Response -Response $response -Title "التقرير الشامل للمعلم"
    
    if ($formattedResponse -and $formattedResponse.success -eq $true) {
        Write-Host "✅ تم جلب التقرير الشامل للمعلم بنجاح" -ForegroundColor Green
        return $true
    } else {
        Write-Host "❌ فشل في جلب التقرير الشامل للمعلم" -ForegroundColor Red
        return $false
    }
}

# تنفيذ الاختبارات
# --------------------------------------
Write-Host "🚀 بدء اختبار واجهات برمجة التطبيقات الخاصة بالمشرف" -ForegroundColor Magenta
Write-Host "=================================================" -ForegroundColor Magenta

# 1. تسجيل الدخول
$loginSuccess = Test-Login
if (-not $loginSuccess) {
    Write-Host "❌ فشل تسجيل الدخول، لا يمكن الاستمرار في الاختبار" -ForegroundColor Red
    exit
}

# 2. الحصول على الحلقات المشرف عليها
$circlesSuccess = Test-GetCircles
if (-not $circlesSuccess -or $circleId -eq 0) {
    Write-Host "❌ فشل في الحصول على الحلقات، لا يمكن الاستمرار في بعض الاختبارات" -ForegroundColor Red
} else {
    # 3. الحصول على طلاب الحلقة
    Test-GetCircleStudents -CircleId $circleId
    
    # 4. الحصول على معلمي الحلقة
    Test-GetCircleTeachers -CircleId $circleId
}

# اختبارات تقييم المعلم (إذا كان لدينا معلم)
if ($teacherId -ne 0) {
    # 5. إنشاء تقييم جديد للمعلم
    $evaluationSuccess = Test-CreateTeacherEvaluation -TeacherId $teacherId
    
    # 6. الحصول على تقييمات المعلم
    Test-GetTeacherEvaluations -TeacherId $teacherId
    
    if ($evaluationSuccess -and $evaluationId -ne 0) {
        # 7. تحديث تقييم المعلم
        Test-UpdateTeacherEvaluation -EvaluationId $evaluationId
        
        # 8. اعتماد تقييم المعلم
        Test-ApproveTeacherEvaluation -EvaluationId $evaluationId
    }
    
    # 9. تسجيل حضور المعلم
    Test-RecordTeacherAttendance -TeacherId $teacherId
    
    # 12. إنشاء تقرير للمعلم
    Test-CreateTeacherReport -TeacherId $teacherId
    
    # 13. الحصول على التقرير الشامل للمعلم
    Test-GetTeacherFullReport -TeacherId $teacherId
}

# اختبار طلب نقل طالب (إذا كان لدينا طالب وحلقتين على الأقل)
if ($studentId -ne 0 -and $circleId -ne 0) {
    $targetCircleId = $circleId + 1 # نفترض وجود حلقة بمعرف أكبر بواحد، يمكن تغييره حسب احتياجك
    Test-RequestStudentTransfer -StudentId $studentId -CurrentCircleId $circleId -TargetCircleId $targetCircleId
}

# 11. الحصول على إحصائيات لوحة المعلومات
Test-GetDashboardStats

Write-Host "`n✅ اكتمل اختبار واجهات برمجة التطبيقات الخاصة بالمشرف" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Magenta
