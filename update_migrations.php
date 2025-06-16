<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $latestBatch = DB::table('migrations')->max('batch') + 1;
    
    // Insert first migration
    $result1 = DB::table('migrations')->insert([
        'migration' => '2025_05_10_000005_create_student_curriculum_progress_table',
        'batch' => $latestBatch
    ]);
    
    // Insert second migration
    $result2 = DB::table('migrations')->insert([
        'migration' => '2025_05_10_000006_drop_student_curriculum_progress_table',
        'batch' => $latestBatch
    ]);
    
    if ($result1 && $result2) {
        echo "تم تحديث سجلات الترحيل بنجاح.";
    } else {
        echo "حدثت مشكلة في تحديث سجلات الترحيل.";
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
