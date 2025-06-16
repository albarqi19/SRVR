<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "فحص هيكل جدول المعلمين:\n";
    $columns = DB::select('DESCRIBE teachers');
    
    foreach ($columns as $column) {
        echo "العمود: {$column->Field}\n";
        echo "النوع: {$column->Type}\n";
        echo "الافتراضي: " . ($column->Default ?? 'NULL') . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
