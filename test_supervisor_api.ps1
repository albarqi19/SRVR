# اختبار API تسجيل دخول المشرف
# PowerShell Script

Write-Host "🚀 اختبار API تسجيل دخول المشرف..." -ForegroundColor Green
Write-Host ""

# بيانات تسجيل الدخول
$email = "demo_1749270301@quran-center.com"
$password = "demo123"
$url = "http://127.0.0.1:8000/api/supervisor/login"

# إعداد البيانات
$body = @{
    email = $email
    password = $password
} | ConvertTo-Json

Write-Host "📧 الإيميل: $email" -ForegroundColor Cyan
Write-Host "🔐 كلمة المرور: $password" -ForegroundColor Cyan
Write-Host "🔗 الرابط: $url" -ForegroundColor Cyan
Write-Host ""

# عرض البيانات المرسلة
Write-Host "📝 البيانات المرسلة:" -ForegroundColor Yellow
Write-Host $body -ForegroundColor White
Write-Host ""

try {
    Write-Host "🔄 جاري إرسال الطلب..." -ForegroundColor Yellow
    
    # إرسال الطلب
    $response = Invoke-RestMethod -Uri $url -Method POST -Body $body -ContentType "application/json"

    Write-Host "✅ نجح تسجيل الدخول!" -ForegroundColor Green
    Write-Host ""
    
    # عرض الاستجابة
    Write-Host "📄 الاستجابة:" -ForegroundColor Magenta
    $response | ConvertTo-Json -Depth 10 | Write-Host -ForegroundColor White
    
}
catch {
    Write-Host "❌ فشل في تسجيل الدخول!" -ForegroundColor Red
    Write-Host ""
    Write-Host "📄 تفاصيل الخطأ:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Yellow
    
    # محاولة قراءة تفاصيل الاستجابة
    if ($_.ErrorDetails) {
        Write-Host "📄 تفاصيل الاستجابة:" -ForegroundColor Red
        Write-Host $_.ErrorDetails.Message -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "🔗 للاختبار اليدوي، استخدم أداة مثل Postman أو curl:" -ForegroundColor Cyan
Write-Host "URL: $url" -ForegroundColor White
Write-Host "Method: POST" -ForegroundColor White
Write-Host "Content-Type: application/json" -ForegroundColor White
Write-Host "Body: $body" -ForegroundColor White
