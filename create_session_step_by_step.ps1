# الأمر المصحح - نسخة منظمة على عدة أسطر

# 1. تعريف البيانات الصحيحة
$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 7
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "أداء جيد"
}

# 2. تحويل البيانات إلى JSON
$jsonData = $sessionData | ConvertTo-Json

# 3. إعداد المتغيرات
$baseUrl = "http://127.0.0.1:8000/api"
$headers = @{
    "Content-Type" = "application/json; charset=utf-8"
    "Accept" = "application/json"
}

# 4. عرض البيانات المرسلة (للتأكد)
Write-Host "البيانات المرسلة:" -ForegroundColor Cyan
Write-Host $jsonData -ForegroundColor Gray

# 5. إرسال الطلب
Write-Host "`nإرسال طلب إنشاء الجلسة..." -ForegroundColor Yellow

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/recitation/sessions" -Method POST -Body $jsonData -Headers $headers
    Write-Host "✅ تم إنشاء الجلسة بنجاح!" -ForegroundColor Green
    Write-Host "معرف الجلسة: $($response.session_id)" -ForegroundColor Cyan
    Write-Host "معرف قاعدة البيانات: $($response.data.id)" -ForegroundColor Cyan
    $global:CreatedSessionId = $response.session_id
    
    # عرض تفاصيل الاستجابة
    Write-Host "`n📊 تفاصيل الاستجابة:" -ForegroundColor Blue
    $response | ConvertTo-Json -Depth 3 | Write-Host -ForegroundColor Gray
    
} catch {
    Write-Host "❌ فشل إنشاء الجلسة:" -ForegroundColor Red
    Write-Host "الخطأ: $($_.Exception.Message)" -ForegroundColor Yellow
    
    if ($_.Exception.Response) {
        $reader = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Host "تفاصيل الخطأ: $responseBody" -ForegroundColor Magenta
        
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
            Write-Host "لا يمكن تفسير تفاصيل الخطأ" -ForegroundColor Gray
        }
    }
}
