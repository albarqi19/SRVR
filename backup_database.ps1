# سكريبت لأخذ نسخة احتياطية من قاعدة البيانات وحفظها على سطح المكتب

# التاريخ والوقت الحالي
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"

# مسار ملف النسخة الاحتياطية على سطح المكتب
$backupPath = "C:\Users\ALBAR\Desktop\garb_project_backup_$timestamp.sql"

# معلومات قاعدة البيانات من ملف .env
$dbHost = "127.0.0.1"
$dbPort = "3306"
$dbName = "garb_project"
$dbUser = "root"
$dbPassword = ""

Write-Host "بدء أخذ النسخة الاحتياطية..." -ForegroundColor Green

try {
    # أمر mysqldump لأخذ النسخة الاحتياطية
    if ($dbPassword -eq "") {
        $command = "mysqldump -h $dbHost -P $dbPort -u $dbUser $dbName"
    } else {
        $command = "mysqldump -h $dbHost -P $dbPort -u $dbUser -p$dbPassword $dbName"
    }
    
    # تشغيل الأمر وحفظ الناتج في الملف
    Invoke-Expression "$command > `"$backupPath`""
    
    Write-Host "تم إنشاء النسخة الاحتياطية بنجاح!" -ForegroundColor Green
    Write-Host "مسار الملف: $backupPath" -ForegroundColor Yellow
    
    # عرض حجم الملف
    $fileSize = (Get-Item $backupPath).Length
    $fileSizeKB = [math]::Round($fileSize / 1024, 2)
    Write-Host "حجم الملف: $fileSizeKB KB" -ForegroundColor Cyan
    
} catch {
    Write-Host "حدث خطأ أثناء أخذ النسخة الاحتياطية:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host "اضغط أي مفتاح للخروج..."
Read-Host
