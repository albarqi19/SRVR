# مثال صحيح لإنشاء جلسة تسميع

# إعداد المتغيرات الأساسية
$baseUrl = "http://localhost:8000/api"
$token = "YOUR_AUTH_TOKEN_HERE"

# إعداد Headers
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

# بيانات الجلسة الصحيحة
$sessionData = @{
    student_id = 1                           # معرف الطالب (يجب أن يكون موجود في جدول students)
    teacher_id = 1                           # معرف المعلم (يجب أن يكون موجود في جدول users)
    quran_circle_id = 1                      # معرف الحلقة (يجب أن يكون موجود في جدول quran_circles)
    start_surah_number = 1                   # رقم السورة البداية (1-114)
    start_verse = 1                          # رقم الآية البداية
    end_surah_number = 1                     # رقم السورة النهاية (1-114)
    end_verse = 7                           # رقم الآية النهاية
    recitation_type = "حفظ"                  # نوع التسميع: حفظ، مراجعة صغرى، مراجعة كبرى، تثبيت
    duration_minutes = 30                    # مدة الجلسة بالدقائق (اختياري)
    grade = 8.5                             # الدرجة (0-10)
    evaluation = "جيد جداً"                   # التقييم: ممتاز، جيد جداً، جيد، مقبول، ضعيف
    teacher_notes = "أداء جيد مع بعض الأخطاء البسيطة في التجويد" # ملاحظات المعلم (اختياري)
}

# تحويل البيانات إلى JSON
$jsonData = $sessionData | ConvertTo-Json

# عرض البيانات المرسلة للتأكد
Write-Host "البيانات المرسلة:" -ForegroundColor Cyan
Write-Host $jsonData -ForegroundColor Yellow

# إرسال الطلب
try {
    Write-Host "`n🔄 جاري إنشاء جلسة التسميع..." -ForegroundColor Yellow
    
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $jsonData -Headers $headers
    
    Write-Host "✅ نجح إنشاء الجلسة!" -ForegroundColor Green
    Write-Host "معرف الجلسة: $($response.session_id)" -ForegroundColor Cyan
    Write-Host "معرف قاعدة البيانات: $($response.data.id)" -ForegroundColor Cyan
    
    # عرض تفاصيل الجلسة
    Write-Host "`n📊 تفاصيل الجلسة:" -ForegroundColor Blue
    Write-Host "الطالب: $($response.data.student.name)" -ForegroundColor White
    Write-Host "المعلم: $($response.data.teacher.name)" -ForegroundColor White
    Write-Host "الحلقة: $($response.data.circle.name)" -ForegroundColor White
    Write-Host "نوع التسميع: $($response.data.recitation_type)" -ForegroundColor White
    Write-Host "التقييم: $($response.data.evaluation)" -ForegroundColor White
    Write-Host "الدرجة: $($response.data.grade)" -ForegroundColor White
    
    return $response
    
} catch {
    Write-Host "❌ فشل إنشاء الجلسة:" -ForegroundColor Red
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "تفاصيل الخطأ: $responseBody" -ForegroundColor Yellow
        
        # محاولة تفسير الخطأ
        try {
            $errorData = $responseBody | ConvertFrom-Json
            if ($errorData.errors) {
                Write-Host "`n🔍 تفاصيل أخطاء التحقق:" -ForegroundColor Cyan
                foreach ($field in $errorData.errors.PSObject.Properties) {
                    Write-Host "- $($field.Name): $($field.Value -join ', ')" -ForegroundColor Red
                }
            }
        } catch {
            # إذا فشل تفسير JSON
            Write-Host "لا يمكن تفسير تفاصيل الخطأ" -ForegroundColor Gray
        }
    }
}
