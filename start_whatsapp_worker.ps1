# سكريبت لتشغيل queue worker الخاص بـ WhatsApp باستمرار
Write-Host "🚀 بدء تشغيل WhatsApp Queue Worker..." -ForegroundColor Green

# الانتقال لمجلد المشروع
Set-Location "c:\Users\ALBAR\OneDrive\سطح المكتب\GARB\garb-project"

Write-Host "📍 مجلد العمل: $(Get-Location)" -ForegroundColor Yellow

# حلقة لا نهائية لإعادة تشغيل worker في حالة توقفه
while ($true) {
    try {
        Write-Host "⚡ تشغيل Queue Worker..." -ForegroundColor Cyan
        
        # تشغيل queue worker
        php artisan queue:work --queue=whatsapp,default --timeout=60 --memory=128 --tries=3 --delay=3
        
        Write-Host "⚠️ Worker توقف. إعادة تشغيل في 5 ثوانٍ..." -ForegroundColor Yellow
        Start-Sleep -Seconds 5
        
    } catch {
        Write-Host "❌ خطأ في Worker: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "🔄 إعادة محاولة في 10 ثوانٍ..." -ForegroundColor Yellow
        Start-Sleep -Seconds 10
    }
}
