# وظيفة شاملة ومحسنة لإنشاء جلسات التسميع

# إعداد المتغيرات الأساسية
$BaseUrl = "http://localhost:8000/api/recitation"
$Token = "YOUR_AUTH_TOKEN_HERE"

# إعداد Headers للطلبات
$Headers = @{
    "Authorization" = "Bearer $Token"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

# وظيفة للتحقق من صحة بيانات الجلسة
function Test-RecitationSessionData {
    param(
        [Parameter(Mandatory=$true)][int]$StudentId,
        [Parameter(Mandatory=$true)][int]$TeacherId,
        [Parameter(Mandatory=$true)][int]$QuranCircleId,
        [Parameter(Mandatory=$true)][int]$StartSurahNumber,
        [Parameter(Mandatory=$true)][int]$StartVerse,
        [Parameter(Mandatory=$true)][int]$EndSurahNumber,
        [Parameter(Mandatory=$true)][int]$EndVerse,
        [Parameter(Mandatory=$true)][string]$RecitationType,
        [Parameter(Mandatory=$true)][decimal]$Grade,
        [Parameter(Mandatory=$true)][string]$Evaluation,
        [int]$DurationMinutes = 0,
        [string]$TeacherNotes = ""
    )
    
    # القيم المقبولة
    $validRecitationTypes = @("حفظ", "مراجعة صغرى", "مراجعة كبرى", "تثبيت")
    $validEvaluations = @("ممتاز", "جيد جداً", "جيد", "مقبول", "ضعيف")
    
    # التحقق من أرقام السور
    if ($StartSurahNumber -lt 1 -or $StartSurahNumber -gt 114) {
        throw "رقم السورة البداية يجب أن يكون بين 1 و 114"
    }
    
    if ($EndSurahNumber -lt 1 -or $EndSurahNumber -gt 114) {
        throw "رقم السورة النهاية يجب أن يكون بين 1 و 114"
    }
    
    # التحقق من أرقام الآيات
    if ($StartVerse -lt 1) {
        throw "رقم الآية البداية يجب أن يكون أكبر من 0"
    }
    
    if ($EndVerse -lt 1) {
        throw "رقم الآية النهاية يجب أن يكون أكبر من 0"
    }
    
    # التحقق من نوع التسميع
    if ($RecitationType -notin $validRecitationTypes) {
        throw "نوع التسميع غير صحيح. الأنواع المسموحة: $($validRecitationTypes -join ', ')"
    }
    
    # التحقق من التقييم
    if ($Evaluation -notin $validEvaluations) {
        throw "التقييم غير صحيح. التقييمات المسموحة: $($validEvaluations -join ', ')"
    }
    
    # التحقق من الدرجة
    if ($Grade -lt 0 -or $Grade -gt 10) {
        throw "الدرجة يجب أن تكون بين 0 و 10"
    }
    
    # التحقق من المدة الزمنية
    if ($DurationMinutes -lt 0) {
        throw "مدة الجلسة يجب أن تكون أكبر من أو تساوي 0"
    }
    
    return $true
}

# وظيفة معالجة الأخطاء
function Handle-SessionApiError {
    param([object]$Response)
    
    if ($Response.success -eq $false) {
        Write-Host "❌ خطأ: $($Response.message)" -ForegroundColor Red
        if ($Response.errors) {
            Write-Host "تفاصيل الأخطاء:" -ForegroundColor Yellow
            foreach ($field in $Response.errors.PSObject.Properties) {
                Write-Host "- $($field.Name): $($field.Value -join ', ')" -ForegroundColor Red
            }
        }
        return $false
    }
    return $true
}

# وظيفة إنشاء جلسة تسميع
function New-RecitationSession {
    param(
        [Parameter(Mandatory=$true)][int]$StudentId,
        [Parameter(Mandatory=$true)][int]$TeacherId,
        [Parameter(Mandatory=$true)][int]$QuranCircleId,
        [Parameter(Mandatory=$true)][int]$StartSurahNumber,
        [Parameter(Mandatory=$true)][int]$StartVerse,
        [Parameter(Mandatory=$true)][int]$EndSurahNumber,
        [Parameter(Mandatory=$true)][int]$EndVerse,
        [Parameter(Mandatory=$true)][string]$RecitationType,
        [Parameter(Mandatory=$true)][decimal]$Grade,
        [Parameter(Mandatory=$true)][string]$Evaluation,
        [int]$DurationMinutes = 30,
        [string]$TeacherNotes = ""
    )
    
    try {
        # التحقق من صحة البيانات
        Test-RecitationSessionData -StudentId $StudentId -TeacherId $TeacherId -QuranCircleId $QuranCircleId `
            -StartSurahNumber $StartSurahNumber -StartVerse $StartVerse -EndSurahNumber $EndSurahNumber `
            -EndVerse $EndVerse -RecitationType $RecitationType -Grade $Grade -Evaluation $Evaluation `
            -DurationMinutes $DurationMinutes -TeacherNotes $TeacherNotes
        
        # إعداد بيانات الجلسة
        $sessionData = @{
            student_id = $StudentId
            teacher_id = $TeacherId
            quran_circle_id = $QuranCircleId
            start_surah_number = $StartSurahNumber
            start_verse = $StartVerse
            end_surah_number = $EndSurahNumber
            end_verse = $EndVerse
            recitation_type = $RecitationType
            grade = $Grade
            evaluation = $Evaluation
        }
        
        # إضافة الحقول الاختيارية إذا كانت متوفرة
        if ($DurationMinutes -gt 0) { $sessionData.duration_minutes = $DurationMinutes }
        if ($TeacherNotes) { $sessionData.teacher_notes = $TeacherNotes }
        
        # تحويل البيانات إلى JSON
        $jsonData = $sessionData | ConvertTo-Json
        
        # عرض البيانات المرسلة
        Write-Host "📋 بيانات الجلسة المرسلة:" -ForegroundColor Cyan
        Write-Host $jsonData -ForegroundColor Gray
        
        # إرسال الطلب
        Write-Host "`n🔄 جاري إنشاء جلسة التسميع..." -ForegroundColor Yellow
        $response = Invoke-RestMethod -Uri "$BaseUrl/sessions" -Method Post -Headers $Headers -Body $jsonData
        
        if (Handle-SessionApiError -Response $response) {
            Write-Host "✅ تم إنشاء جلسة التسميع بنجاح!" -ForegroundColor Green
            
            # عرض معلومات الجلسة
            Write-Host "`n📊 معلومات الجلسة:" -ForegroundColor Blue
            Write-Host "معرف الجلسة: $($response.session_id)" -ForegroundColor Cyan
            Write-Host "معرف قاعدة البيانات: $($response.data.id)" -ForegroundColor Cyan
            
            if ($response.data.student) {
                Write-Host "الطالب: $($response.data.student.name)" -ForegroundColor White
            }
            if ($response.data.teacher) {
                Write-Host "المعلم: $($response.data.teacher.name)" -ForegroundColor White
            }
            if ($response.data.circle) {
                Write-Host "الحلقة: $($response.data.circle.name)" -ForegroundColor White
            }
            
            Write-Host "السورة: من $StartSurahNumber:$StartVerse إلى $EndSurahNumber:$EndVerse" -ForegroundColor White
            Write-Host "نوع التسميع: $RecitationType" -ForegroundColor White
            Write-Host "التقييم: $Evaluation" -ForegroundColor White
            Write-Host "الدرجة: $Grade" -ForegroundColor White
            
            if ($DurationMinutes -gt 0) {
                Write-Host "المدة: $DurationMinutes دقيقة" -ForegroundColor White
            }
            
            if ($TeacherNotes) {
                Write-Host "ملاحظات المعلم: $TeacherNotes" -ForegroundColor Gray
            }
            
            return $response
        }
    }
    catch {
        Write-Host "❌ خطأ في إنشاء جلسة التسميع: $($_.Exception.Message)" -ForegroundColor Red
        
        # محاولة الحصول على تفاصيل الخطأ من الاستجابة
        if ($_.Exception.Response) {
            try {
                $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
                $responseBody = $reader.ReadToEnd()
                
                try {
                    $errorData = $responseBody | ConvertFrom-Json
                    if ($errorData.errors) {
                        Write-Host "`n🔍 تفاصيل أخطاء التحقق:" -ForegroundColor Yellow
                        foreach ($field in $errorData.errors.PSObject.Properties) {
                            Write-Host "- $($field.Name): $($field.Value -join ', ')" -ForegroundColor Red
                        }
                    }
                } catch {
                    Write-Host "تفاصيل الخطأ: $responseBody" -ForegroundColor Red
                }
            } catch {
                Write-Host "لا يمكن قراءة تفاصيل الخطأ" -ForegroundColor Gray
            }
        }
        
        return $null
    }
}

# أمثلة للاستخدام الصحيح
Write-Host "🎯 أمثلة لإنشاء جلسات تسميع:" -ForegroundColor Magenta
Write-Host "=================================" -ForegroundColor Gray

# مثال 1: جلسة حفظ
Write-Host "`n1️⃣ مثال لجلسة حفظ:" -ForegroundColor Yellow
$session1 = New-RecitationSession -StudentId 1 -TeacherId 1 -QuranCircleId 1 `
    -StartSurahNumber 1 -StartVerse 1 -EndSurahNumber 1 -EndVerse 7 `
    -RecitationType "حفظ" -Grade 9.0 -Evaluation "ممتاز" `
    -DurationMinutes 30 -TeacherNotes "حفظ ممتاز مع تطبيق جيد لقواعد التجويد"

# مثال 2: جلسة مراجعة
Write-Host "`n2️⃣ مثال لجلسة مراجعة صغرى:" -ForegroundColor Yellow
$session2 = New-RecitationSession -StudentId 2 -TeacherId 1 -QuranCircleId 1 `
    -StartSurahNumber 2 -StartVerse 1 -EndSurahNumber 2 -EndVerse 20 `
    -RecitationType "مراجعة صغرى" -Grade 7.5 -Evaluation "جيد" `
    -DurationMinutes 25 -TeacherNotes "مراجعة جيدة مع بعض الأخطاء في النطق"

# مثال 3: جلسة تثبيت
Write-Host "`n3️⃣ مثال لجلسة تثبيت:" -ForegroundColor Yellow
$session3 = New-RecitationSession -StudentId 3 -TeacherId 1 -QuranCircleId 1 `
    -StartSurahNumber 3 -StartVerse 1 -EndSurahNumber 3 -EndVerse 10 `
    -RecitationType "تثبيت" -Grade 8.5 -Evaluation "جيد جداً" `
    -DurationMinutes 20

Write-Host "`n✅ انتهاء الأمثلة" -ForegroundColor Green
