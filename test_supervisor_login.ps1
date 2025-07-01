# اختبار API تسجيل دخول المشرف
# PowerShell Script

Write-Host "🚀 اختبار API تسجيل دخول المشرف..." -ForegroundColor Green
Write-Host ""

# بيانات تسجيل الدخول
$email = "demo_1749270301@quran-center.com"
$password = "demo123"

# إعداد البيانات
$body = @{
    email = $email
    password = $password
} | ConvertTo-Json

Write-Host "📧 الإيميل: $email" -ForegroundColor Cyan
Write-Host "🔐 كلمة المرور: $password" -ForegroundColor Cyan
Write-Host ""

# عرض البيانات المرسلة
Write-Host "📝 البيانات المرسلة:" -ForegroundColor Yellow
Write-Host $body -ForegroundColor White
Write-Host ""

try {
    Write-Host "🔄 جاري إرسال الطلب..." -ForegroundColor Yellow
    
    # إرسال الطلب
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/supervisor/login" `
                                  -Method POST `
                                  -Body $body `
                                  -ContentType "application/json" `
                                  -ErrorAction Stop

    Write-Host "✅ نجح تسجيل الدخول!" -ForegroundColor Green
    Write-Host ""
    
    # عرض الاستجابة
    Write-Host "📄 الاستجابة:" -ForegroundColor Magenta
    $response | ConvertTo-Json -Depth 10 | Write-Host -ForegroundColor White
    
} catch {
    Write-Host "❌ فشل في تسجيل الدخول!" -ForegroundColor Red
    Write-Host ""
    Write-Host "📄 تفاصيل الخطأ:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Yellow
    
    # إذا كان هناك استجابة من الخادم
    if ($_.Exception.Response) {
        try {
            $errorStream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorStream)
            $errorBody = $reader.ReadToEnd()
            Write-Host "📄 تفاصيل الاستجابة:" -ForegroundColor Red
            Write-Host $errorBody -ForegroundColor Yellow
        } catch {
            Write-Host "لا توجد تفاصيل إضافية" -ForegroundColor Gray
        }
    }
}

Write-Host ""
Write-Host "🔗 للاختبار اليدوي، استخدم:" -ForegroundColor Cyan
Write-Host "URL: http://127.0.0.1:8000/api/supervisor/login" -ForegroundColor White
Write-Host "Method: POST" -ForegroundColor White
Write-Host "Content-Type: application/json" -ForegroundColor White
