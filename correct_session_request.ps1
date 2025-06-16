# تصحيح طلب إنشاء جلسة التسميع - بدون استخدام &&

# إعداد المتغيرات الأساسية
Write-Host "🔧 إعداد المتغيرات الأساسية..." -ForegroundColor Cyan
$baseUrl = "http://localhost:8000/api"
$token = "YOUR_AUTH_TOKEN_HERE"  # استبدل بالـ token الصحيح

# إعداد Headers
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host "✅ تم إعداد المتغيرات بنجاح" -ForegroundColor Green

# إنشاء بيانات الجلسة بالقيم الصحيحة المضمونة
Write-Host "`n📋 إنشاء بيانات الجلسة..." -ForegroundColor Cyan

$sessionData = @{
    student_id = 1
    teacher_id = 1
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 7
    recitation_type = "حفظ"                    # ✅ قيمة صحيحة مضمونة
    duration_minutes = 30
    grade = 8.5
    evaluation = "جيد جداً"                    # ✅ قيمة صحيحة مضمونة
    teacher_notes = "أداء جيد مع بعض الأخطاء البسيطة"
}

# تحويل البيانات إلى JSON
$jsonData = $sessionData | ConvertTo-Json

Write-Host "✅ تم إنشاء البيانات بنجاح" -ForegroundColor Green
Write-Host "`n📄 البيانات المرسلة:" -ForegroundColor Yellow
Write-Host $jsonData -ForegroundColor Gray

# إرسال الطلب
Write-Host "`n🚀 إرسال طلب إنشاء الجلسة..." -ForegroundColor Magenta

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $jsonData -Headers $headers
    
    Write-Host "✅ نجح إنشاء الجلسة!" -ForegroundColor Green
    Write-Host "معرف الجلسة: $($response.session_id)" -ForegroundColor Yellow
    Write-Host "معرف قاعدة البيانات: $($response.data.id)" -ForegroundColor Cyan
    
    # عرض تفاصيل إضافية إذا كانت متوفرة
    if ($response.data.student) {
        Write-Host "الطالب: $($response.data.student.name)" -ForegroundColor White
    }
    if ($response.data.teacher) {
        Write-Host "المعلم: $($response.data.teacher.name)" -ForegroundColor White
    }
    if ($response.data.circle) {
        Write-Host "الحلقة: $($response.data.circle.name)" -ForegroundColor White
    }
    
    Write-Host "`n📊 تفاصيل الجلسة:" -ForegroundColor Blue
    Write-Host "نوع التسميع: $($response.data.recitation_type)" -ForegroundColor White
    Write-Host "التقييم: $($response.data.evaluation)" -ForegroundColor White
    Write-Host "الدرجة: $($response.data.grade)" -ForegroundColor White
    
    $response
    
} catch {
    Write-Host "❌ فشل إنشاء الجلسة: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = [System.IO.StreamReader]::new($stream)
        $errorResponse = $reader.ReadToEnd()
        
        Write-Host "`n🔍 تفاصيل الخطأ:" -ForegroundColor Yellow
        Write-Host $errorResponse -ForegroundColor Red
        
        # محاولة تفسير الخطأ
        try {
            $errorData = $errorResponse | ConvertFrom-Json
            
            if ($errorData.errors) {
                Write-Host "`n🚨 أخطاء التحقق المحددة:" -ForegroundColor Cyan
                foreach ($field in $errorData.errors.PSObject.Properties) {
                    Write-Host "- الحقل '$($field.Name)': $($field.Value -join ', ')" -ForegroundColor Red
                }
                
                # اقتراحات للحلول
                Write-Host "`n💡 اقتراحات للحل:" -ForegroundColor Green
                if ($errorData.errors.recitation_type) {
                    Write-Host "- تأكد من استخدام إحدى هذه القيم لنوع التسميع:" -ForegroundColor Yellow
                    Write-Host "  'حفظ', 'مراجعة صغرى', 'مراجعة كبرى', 'تثبيت'" -ForegroundColor White
                }
                if ($errorData.errors.evaluation) {
                    Write-Host "- تأكد من استخدام إحدى هذه القيم للتقييم:" -ForegroundColor Yellow
                    Write-Host "  'ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'" -ForegroundColor White
                }
                if ($errorData.errors.student_id) {
                    Write-Host "- تأكد من وجود الطالب رقم $($sessionData.student_id) في قاعدة البيانات" -ForegroundColor Yellow
                }
                if ($errorData.errors.teacher_id) {
                    Write-Host "- تأكد من وجود المعلم رقم $($sessionData.teacher_id) في قاعدة البيانات" -ForegroundColor Yellow
                }
                if ($errorData.errors.quran_circle_id) {
                    Write-Host "- تأكد من وجود الحلقة رقم $($sessionData.quran_circle_id) في قاعدة البيانات" -ForegroundColor Yellow
                }
            }
        } catch {
            Write-Host "لا يمكن تفسير تفاصيل الخطأ كـ JSON" -ForegroundColor Gray
        }
    }
}

Write-Host "`n📝 ملاحظة: تأكد من استبدال YOUR_AUTH_TOKEN_HERE بالـ token الصحيح" -ForegroundColor Cyan
