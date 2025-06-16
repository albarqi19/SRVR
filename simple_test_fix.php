<?php

echo "بدء الاختبار...\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "تم تحميل autoload بنجاح\n";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "تم تحميل Laravel بنجاح\n";
    
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "تم تهيئة Laravel بنجاح\n";
    
    // التحقق من الاتصال بقاعدة البيانات
    $connection = \Illuminate\Support\Facades\DB::connection();
    $pdo = $connection->getPdo();
    echo "تم الاتصال بقاعدة البيانات بنجاح\n";
    
    // التحقق من وجود جدول curriculum_plans
    $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'curriculum_plans'");
    if (count($tables) > 0) {
        echo "جدول curriculum_plans موجود\n";
    } else {
        echo "جدول curriculum_plans غير موجود\n";
    }
    
    // التحقق من بنية الجدول
    $columns = \Illuminate\Support\Facades\DB::select("DESCRIBE curriculum_plans");
    echo "أعمدة الجدول:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) " . ($column->Null === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
