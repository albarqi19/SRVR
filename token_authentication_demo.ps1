# مثال كامل لاستخدام Token Authentication
# =====================================================

$baseUrl = "http://localhost:8000/api"

Write-Host "====================================================" -ForegroundColor Green
Write-Host "مثال عملي: استخدام Token للوصول للـ APIs المحمية" -ForegroundColor Green
Write-Host "====================================================" -ForegroundColor Green

# الخطوة 1: تسجيل الدخول والحصول على Token
Write-Host "`n1. تسجيل الدخول والحصول على Token..." -ForegroundColor Yellow

$loginData = @{
    identity_number = "1234567890"
    password = "password123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/teacher/login" `
        -Method POST `
        -Body $loginData `
        -ContentType "application/json"
    
    Write-Host "✅ تم تسجيل الدخول بنجاح!" -ForegroundColor Green
    Write-Host "Token: $($loginResponse.token.Substring(0, 50))..." -ForegroundColor Cyan
    
    $token = $loginResponse.token
} catch {
    Write-Host "❌ فشل في تسجيل الدخول: $($_.Exception.Message)" -ForegroundColor Red
    
    # إنشاء token وهمي للاختبار
    Write-Host "`n🔄 استخدام token وهمي للاختبار..." -ForegroundColor Yellow
    $token = "fake_token_for_demo"
}

# الخطوة 2: إعداد Headers مع Token
Write-Host "`n2. إعداد Headers مع Token..." -ForegroundColor Yellow

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host "Headers تم إعدادها:" -ForegroundColor Cyan
Write-Host "Authorization: Bearer $($token.Substring(0, 20))..." -ForegroundColor White

# الخطوة 3: محاولة الوصول للـ APIs بدون Token (سيفشل)
Write-Host "`n3. محاولة الوصول للـ APIs بدون Token..." -ForegroundColor Yellow

try {
    $studentsWithoutToken = Invoke-RestMethod -Uri "$baseUrl/students" -Method GET
    Write-Host "✅ تم الوصول بدون Token (APIs غير محمية حالياً)" -ForegroundColor Green
    Write-Host "عدد الطلاب: $($studentsWithoutToken.data.Count)" -ForegroundColor White
} catch {
    Write-Host "❌ فشل الوصول بدون Token (هذا متوقع إذا كانت APIs محمية)" -ForegroundColor Red
    Write-Host "خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

# الخطوة 4: الوصول للـ APIs مع Token
Write-Host "`n4. الوصول للـ APIs مع Token..." -ForegroundColor Yellow

try {
    # اختبار API المحمي الوحيد حالياً
    $userInfo = Invoke-RestMethod -Uri "$baseUrl/user" -Headers $headers -Method GET
    Write-Host "✅ تم الوصول للـ API المحمي بنجاح!" -ForegroundColor Green
    Write-Host "معلومات المستخدم: $($userInfo.name)" -ForegroundColor White
} catch {
    Write-Host "❌ فشل في الوصول للـ API المحمي" -ForegroundColor Red
    Write-Host "خطأ: $($_.Exception.Message)" -ForegroundColor Red
}

# الخطوة 5: عرض APIs أخرى يمكن اختبارها
Write-Host "`n5. APIs أخرى يمكن اختبارها مع Token:" -ForegroundColor Yellow

$apiEndpoints = @(
    "/students - قائمة الطلاب",
    "/teachers - قائمة المعلمين", 
    "/circles - قائمة الحلقات",
    "/mosques - قائمة المساجد",
    "/recitation/sessions - جلسات التسميع",
    "/reports/general-stats - الإحصائيات العامة"
)

foreach ($endpoint in $apiEndpoints) {
    Write-Host "  📍 $endpoint" -ForegroundColor Cyan
}

# الخطوة 6: مثال على كيفية حماية APIs
Write-Host "`n6. مثال على كيفية حماية APIs:" -ForegroundColor Yellow
Write-Host @"
// في ملف routes/api.php
// بدلاً من:
Route::prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index']);
});

// يصبح:
Route::middleware('auth:sanctum')->prefix('students')->group(function () {
    Route::get('/', [StudentController::class, 'index']); // محمي بـ Token
});
"@ -ForegroundColor White

Write-Host "`n====================================================" -ForegroundColor Green
Write-Host "انتهى المثال - Token Authentication Demo" -ForegroundColor Green
Write-Host "====================================================" -ForegroundColor Green

# معلومات إضافية
Write-Host "`n📋 ملاحظات مهمة:" -ForegroundColor Yellow
Write-Host "1. Token صالح لمدة معينة (حسب إعدادات Laravel Sanctum)" -ForegroundColor White
Write-Host "2. يجب إرسال Token مع كل طلب API في Header Authorization" -ForegroundColor White
Write-Host "3. حالياً معظم APIs غير محمية - يمكن الوصول إليها بدون Token" -ForegroundColor White
Write-Host "4. فقط API واحد محمي: /api/user" -ForegroundColor White
Write-Host "5. لحماية جميع APIs، يجب إضافة middleware('auth:sanctum')" -ForegroundColor White
