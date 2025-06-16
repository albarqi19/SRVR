# اختبار endpoint حذف جلسة التسميع

Write-Host "🧪 اختبار حذف جلسة التسميع" -ForegroundColor Cyan
Write-Host "=" * 50

$base_url = "http://127.0.0.1:8000/api"

# أولاً: إنشاء جلسة تسميع للاختبار
Write-Host "`n📝 خطوة 1: إنشاء جلسة تسميع للاختبار..." -ForegroundColor Yellow

$sessionData = @{
    student_id = 1
    teacher_id = 1  
    quran_circle_id = 1
    start_surah_number = 1
    start_verse = 1
    end_surah_number = 1
    end_verse = 5
    recitation_type = "حفظ"
    duration_minutes = 30
    grade = 8.5
    evaluation = "جيد جداً"
    teacher_notes = "جلسة اختبار للحذف"
} | ConvertTo-Json -Depth 10

try {
    $createResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/" -Method POST -Body $sessionData -ContentType "application/json"
    
    if ($createResponse.success) {
        $sessionId = $createResponse.data.session_id
        Write-Host "✅ تم إنشاء جلسة تسميع بنجاح" -ForegroundColor Green
        Write-Host "   Session ID: $sessionId" -ForegroundColor White
        
        # خطوة 2: حذف الجلسة
        Write-Host "`n🗑️ خطوة 2: حذف الجلسة..." -ForegroundColor Yellow
        
        $deleteResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/$sessionId" -Method DELETE
        
        if ($deleteResponse.success) {
            Write-Host "✅ تم حذف جلسة التسميع بنجاح!" -ForegroundColor Green
            Write-Host "   الرسالة: $($deleteResponse.message)" -ForegroundColor White
        } else {
            Write-Host "❌ فشل في حذف الجلسة: $($deleteResponse.message)" -ForegroundColor Red
        }
        
        # خطوة 3: التحقق من الحذف
        Write-Host "`n🔍 خطوة 3: التحقق من الحذف..." -ForegroundColor Yellow
        
        try {
            $checkResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/$sessionId" -Method GET
            Write-Host "❌ الجلسة ما زالت موجودة! الحذف لم يعمل" -ForegroundColor Red
        } catch {
            if ($_.Exception.Response.StatusCode -eq 404) {
                Write-Host "✅ تأكيد: الجلسة لم تعد موجودة" -ForegroundColor Green
            } else {
                Write-Host "⚠️ خطأ غير متوقع: $($_.Exception.Message)" -ForegroundColor Yellow
            }
        }
        
    } else {
        Write-Host "❌ فشل في إنشاء جلسة الاختبار: $($createResponse.message)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ في الاتصال: $($_.Exception.Message)" -ForegroundColor Red
}

# اختبار حذف جلسة غير موجودة
Write-Host "`n🚫 خطوة 4: اختبار حذف جلسة غير موجودة..." -ForegroundColor Yellow

try {
    $invalidResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/invalid-session-id" -Method DELETE
    Write-Host "❌ يجب أن يرجع خطأ 404" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode -eq 404) {
        Write-Host "✅ تأكيد: رجع 404 للجلسة غير الموجودة" -ForegroundColor Green
    } else {
        Write-Host "⚠️ خطأ غير متوقع: $($_.Exception.Message)" -ForegroundColor Yellow
    }
}

Write-Host "`n🎉 انتهى الاختبار!" -ForegroundColor Cyan
