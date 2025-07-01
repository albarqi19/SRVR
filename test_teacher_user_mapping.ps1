# اختبار حلول مشكلة teacher_id mapping
$baseUrl = "https://inviting-pleasantly-barnacle.ngrok-free.app"

# Headers مطلوبة لـ ngrok
$headers = @{
    "Accept" = "application/json"
    "ngrok-skip-browser-warning" = "true"
    "User-Agent" = "PowerShell-API-Test"
}

Write-Host "🔧 اختبار حلول مشكلة teacher_id mapping" -ForegroundColor Cyan
Write-Host "=" * 60

# الحل الأول: اختبار API للحصول على user_id من teacher_id
Write-Host "`n1️⃣ اختبار API للحصول على user_id من teacher_id 89:" -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/teachers/get-user-id/89" -Headers $headers -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "✅ نجح الاستعلام:" -ForegroundColor Green
    Write-Host ($data | ConvertTo-Json -Depth 3) -ForegroundColor White
    
    if ($data.success -and $data.data.teacher_id_for_api) {
        $correctTeacherId = $data.data.teacher_id_for_api
        Write-Host "`n🎯 الـ teacher_id الصحيح للاستخدام في API: $correctTeacherId" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ خطأ في الاستعلام: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Response: $($_.Exception.Response)" -ForegroundColor Red
}

# الحل الثاني: اختبار قائمة جميع المعلمين مع user_ids
Write-Host "`n2️⃣ اختبار API لقائمة المعلمين مع user_ids:" -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/teachers/with-user-ids" -Headers $headers -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "✅ نجح الاستعلام:" -ForegroundColor Green
    
    if ($data.success -and $data.data) {
        Write-Host "`n📋 قائمة المعلمين:" -ForegroundColor Cyan
        foreach ($teacher in $data.data) {
            $teacherId = $teacher.teacher_id
            $userId = $teacher.user_id
            $name = $teacher.teacher_name
            $email = $teacher.user_email
            
            if ($userId) {
                Write-Host "✅ $name (teacher_id: $teacherId → user_id: $userId, email: $email)" -ForegroundColor Green
            } else {
                Write-Host "❌ $name (teacher_id: $teacherId → لا يوجد user_id)" -ForegroundColor Red
            }
        }
    }
} catch {
    Write-Host "❌ خطأ في الاستعلام: $($_.Exception.Message)" -ForegroundColor Red
}

# الحل الثالث: اختبار إنشاء جلسة تسميع بـ teacher_id = 89 (التحقق من validation rule الجديد)
Write-Host "`n3️⃣ اختبار إنشاء جلسة تسميع مع teacher_id = 89:" -ForegroundColor Yellow

$sessionData = @{
    student_id = 36
    teacher_id = 89  # استخدام teacher_id من جدول teachers
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 1
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "اختبار validation rule الجديد"
} | ConvertTo-Json

$sessionHeaders = $headers.Clone()
$sessionHeaders["Content-Type"] = "application/json"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/recitation/sessions" -Method POST -Body $sessionData -Headers $sessionHeaders -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "✅ نجح إنشاء جلسة التسميع مع validation rule الجديد!" -ForegroundColor Green
    Write-Host ($data | ConvertTo-Json -Depth 3) -ForegroundColor White
    
} catch {
    $errorContent = ""
    if ($_.Exception.Response) {
        try {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $errorContent = $reader.ReadToEnd()
            $errorData = $errorContent | ConvertFrom-Json
            
            Write-Host "❌ فشل إنشاء الجلسة:" -ForegroundColor Red
            Write-Host "Status Code: $($_.Exception.Response.StatusCode)" -ForegroundColor Red
            Write-Host "Error: $($errorData.message)" -ForegroundColor Red
            
            if ($errorData.errors) {
                Write-Host "Validation Errors:" -ForegroundColor Red
                Write-Host ($errorData.errors | ConvertTo-Json -Depth 2) -ForegroundColor Red
            }
        } catch {
            Write-Host "❌ خطأ في قراءة الاستجابة: $($_.Exception.Message)" -ForegroundColor Red
        }
    } else {
        Write-Host "❌ خطأ في الاتصال: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n" + "=" * 60
Write-Host "🎯 خلاصة الحلول:" -ForegroundColor Cyan
Write-Host "1. استخدم API endpoint للحصول على user_id الصحيح" -ForegroundColor White
Write-Host "2. استخدم user_id بدلاً من teacher_id في API calls" -ForegroundColor White  
Write-Host "3. validation rule الجديد يجب أن يقبل كلا من teacher_id و user_id" -ForegroundColor White
