# سكريپت نقل البيانات من MySQL إلى Supabase

param(
    [string]$SupabaseConnectionString = ""
)

Write-Host "=== نقل البيانات من MySQL إلى Supabase ===" -ForegroundColor Green

if ($SupabaseConnectionString -eq "") {
    Write-Host "الرجاء إدخال connection string من Supabase:" -ForegroundColor Yellow
    $SupabaseConnectionString = Read-Host "Connection String"
}

# معلومات MySQL الحالية
$mysqlHost = "127.0.0.1"
$mysqlPort = "3306"
$mysqlDB = "garb_project"
$mysqlUser = "root"
$mysqlPassword = ""

# إنشاء نسخة احتياطية من MySQL
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupFile = "C:\Users\ALBAR\Desktop\mysql_to_supabase_$timestamp.sql"

Write-Host "1. إنشاء نسخة احتياطية من MySQL..." -ForegroundColor Yellow
try {
    if ($mysqlPassword -eq "") {
        & mysqldump -h $mysqlHost -P $mysqlPort -u $mysqlUser $mysqlDB > $backupFile
    } else {
        & mysqldump -h $mysqlHost -P $mysqlPort -u $mysqlUser -p$mysqlPassword $mysqlDB > $backupFile
    }
    Write-Host "تم إنشاء النسخة الاحتياطية: $backupFile" -ForegroundColor Green
} catch {
    Write-Host "خطأ في إنشاء النسخة الاحتياطية: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "2. تحويل SQL من MySQL إلى PostgreSQL..." -ForegroundColor Yellow

# قراءة محتوى الملف
$content = Get-Content $backupFile -Raw

# تحويلات أساسية من MySQL إلى PostgreSQL
$content = $content -replace '`([^`]+)`', '"$1"'  # تحويل backticks إلى double quotes
$content = $content -replace 'AUTO_INCREMENT', ''  # إزالة AUTO_INCREMENT
$content = $content -replace 'ENGINE=\w+', ''  # إزالة ENGINE
$content = $content -replace 'DEFAULT CHARSET=\w+', ''  # إزالة CHARSET
$content = $content -replace 'COLLATE=\w+', ''  # إزالة COLLATE
$content = $content -replace 'TINYINT\(1\)', 'BOOLEAN'  # تحويل TINYINT إلى BOOLEAN
$content = $content -replace 'DATETIME', 'TIMESTAMP'  # تحويل DATETIME إلى TIMESTAMP

# حفظ الملف المحول
$pgBackupFile = "C:\Users\ALBAR\Desktop\postgresql_converted_$timestamp.sql"
$content | Out-File -FilePath $pgBackupFile -Encoding UTF8

Write-Host "تم حفظ الملف المحول: $pgBackupFile" -ForegroundColor Green

Write-Host "3. رفع البيانات إلى Supabase..." -ForegroundColor Yellow
Write-Host "الرجاء تشغيل الأمر التالي يدوياً:" -ForegroundColor Cyan
Write-Host "psql `"$SupabaseConnectionString`" -f `"$pgBackupFile`"" -ForegroundColor White

Write-Host @"

=== خطوات يدوية مطلوبة ===

1. تثبيت PostgreSQL client إذا لم يكن مثبتاً:
   - تحميل من: https://www.postgresql.org/download/windows/
   - أو استخدام Supabase SQL Editor

2. رفع البيانات:
   psql "$SupabaseConnectionString" -f "$pgBackupFile"

3. أو استخدام Supabase Dashboard:
   - اذهب إلى SQL Editor
   - انسخ محتوى الملف والصقه
   - نفذ الاستعلام

=== النهاية ===

"@ -ForegroundColor Yellow

Write-Host "اضغط أي مفتاح للخروج..." -ForegroundColor Green
Read-Host
