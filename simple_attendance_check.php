<?php

// تحديد المسار الصحيح
chdir(__DIR__);
require_once 'vendor/autoload.php';

// إنشاء التطبيق
$app = require_once 'bootstrap/app.php';

// تشغيل bootstrap
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "فحص اتصال قاعدة البيانات...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "✓ تم الاتصال بقاعدة البيانات بنجاح\n";
} catch (Exception $e) {
    echo "✗ فشل الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
    exit;
}

echo "\nفحص جدول student_attendances...\n";
try {
    $count = DB::table('student_attendances')->count();
    echo "عدد السجلات: $count\n";
    
    if ($count > 0) {
        $first = DB::table('student_attendances')->first();
        echo "أول سجل:\n";
        print_r($first);
        
        $statuses = DB::table('student_attendances')->select('status')->distinct()->get();
        echo "\nالحالات الموجودة:\n";
        foreach ($statuses as $status) {
            echo "- " . ($status->status ?? 'NULL') . "\n";
        }
    }
} catch (Exception $e) {
    echo "خطأ في فحص الجدول: " . $e->getMessage() . "\n";
}
