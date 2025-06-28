# سكريپت تحضير Laravel لـ PostgreSQL

Write-Host "=== تحضير Laravel لـ PostgreSQL ===" -ForegroundColor Green

# 1. إضافة PostgreSQL driver
Write-Host "1. إضافة PostgreSQL driver..." -ForegroundColor Yellow
composer require doctrine/dbal

# 2. تحديث ملف .env (ستحتاج نسخ البيانات من Supabase)
Write-Host "2. تحديث ملف .env..." -ForegroundColor Yellow
Write-Host "يرجى تحديث الملف يدوياً بالمعلومات من Supabase" -ForegroundColor Cyan

# 3. اختبار الاتصال
Write-Host "3. اختبار الاتصال..." -ForegroundColor Yellow
php artisan tinker --execute="DB::connection()->getPdo(); echo 'اتصال ناجح!';"

# 4. تشغيل migrations
Write-Host "4. تشغيل migrations..." -ForegroundColor Yellow
php artisan migrate

# 5. اختبار Laravel
Write-Host "5. اختبار Laravel..." -ForegroundColor Yellow
php artisan serve --host=0.0.0.0 --port=8000

Write-Host "=== تم بنجاح! ===" -ForegroundColor Green
Write-Host "الآن يمكنك الوصول للتطبيق عبر:" -ForegroundColor Cyan
Write-Host "http://localhost:8000" -ForegroundColor White
