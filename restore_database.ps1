# سكريپت لاستعادة النسخة الاحتياطية من سطح المكتب

param(
    [string]$BackupFile = ""
)

# معلومات قاعدة البيانات من ملف .env
$dbHost = "127.0.0.1"
$dbPort = "3306"
$dbName = "garb_project"
$dbUser = "root"
$dbPassword = ""

Write-Host "سكريبت استعادة قاعدة البيانات" -ForegroundColor Green

# إذا لم يتم تحديد ملف النسخة الاحتياطية، ابحث عن الملفات في سطح المكتب
if ($BackupFile -eq "") {
    $backupFiles = Get-ChildItem "C:\Users\ALBAR\Desktop" -Filter "garb_project_backup_*.sql" | Sort-Object LastWriteTime -Descending
    
    if ($backupFiles.Count -eq 0) {
        Write-Host "لا توجد ملفات نسخ احتياطية في سطح المكتب" -ForegroundColor Red
        Read-Host "اضغط أي مفتاح للخروج"
        exit
    }
    
    Write-Host "الملفات المتاحة:" -ForegroundColor Yellow
    for ($i = 0; $i -lt $backupFiles.Count; $i++) {
        $file = $backupFiles[$i]
        $fileSize = [math]::Round($file.Length / 1024, 2)
        Write-Host "[$($i+1)] $($file.Name) ($fileSize KB) - $($file.LastWriteTime)" -ForegroundColor Cyan
    }
    
    $selection = Read-Host "اختر رقم الملف (أو اضغط Enter للملف الأحدث)"
    
    if ($selection -eq "") {
        $BackupFile = $backupFiles[0].FullName
    } else {
        $index = [int]$selection - 1
        if ($index -ge 0 -and $index -lt $backupFiles.Count) {
            $BackupFile = $backupFiles[$index].FullName
        } else {
            Write-Host "اختيار غير صحيح" -ForegroundColor Red
            Read-Host "اضغط أي مفتاح للخروج"
            exit
        }
    }
}

if (!(Test-Path $BackupFile)) {
    Write-Host "الملف غير موجود: $BackupFile" -ForegroundColor Red
    Read-Host "اضغط أي مفتاح للخروج"
    exit
}

Write-Host "سيتم استعادة البيانات من: $BackupFile" -ForegroundColor Yellow
$confirm = Read-Host "هل تريد المتابعة؟ (y/n)"

if ($confirm -ne "y" -and $confirm -ne "Y") {
    Write-Host "تم إلغاء العملية" -ForegroundColor Yellow
    Read-Host "اضغط أي مفتاح للخروج"
    exit
}

Write-Host "بدء استعادة البيانات..." -ForegroundColor Green

try {
    # استعادة البيانات مباشرة
    Write-Host "جاري استعادة البيانات..." -ForegroundColor Cyan
    
    if ($dbPassword -eq "") {
        Get-Content $BackupFile | mysql -h $dbHost -P $dbPort -u $dbUser $dbName
    } else {
        Get-Content $BackupFile | mysql -h $dbHost -P $dbPort -u $dbUser -p$dbPassword $dbName
    }
    
    Write-Host "تم استعادة البيانات بنجاح!" -ForegroundColor Green
    
} catch {
    Write-Host "حدث خطأ أثناء استعادة البيانات:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host "اضغط أي مفتاح للخروج..."
Read-Host
