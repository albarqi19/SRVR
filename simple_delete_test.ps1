# اختبار بسيط لحذف جلسة التسميع
Write-Host "🧪 اختبار endpoint حذف جلسة التسميع" -ForegroundColor Cyan

$base_url = "http://127.0.0.1:8000/api"

# أولاً: إنشاء جلسة للاختبار
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
}

try {
    Write-Host "`n📝 إنشاء جلسة للاختبار..." -ForegroundColor Yellow
    
    $createResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/" -Method POST -Body ($sessionData | ConvertTo-Json) -ContentType "application/json"
    
    if ($createResponse.success) {
        $sessionId = $createResponse.data.session_id
        Write-Host "✅ تم إنشاء جلسة: $sessionId" -ForegroundColor Green
        
        # اختبار حذف الجلسة
        Write-Host "`n🗑️ حذف الجلسة..." -ForegroundColor Yellow
        
        $deleteResponse = Invoke-RestMethod -Uri "$base_url/recitation/sessions/$sessionId" -Method DELETE
        
        Write-Host "✅ استجابة الحذف:" -ForegroundColor Green
        Write-Host "   النجاح: $($deleteResponse.success)" -ForegroundColor White
        Write-Host "   الرسالة: $($deleteResponse.message)" -ForegroundColor White
        
    } else {
        Write-Host "❌ فشل إنشاء الجلسة" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ خطأ: $($_.Exception.Message)" -ForegroundColor Red
}
