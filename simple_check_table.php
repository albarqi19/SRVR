<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $hasTable = Schema::hasTable('student_curriculum_progress');
    echo $hasTable ? "الجدول student_curriculum_progress موجود\n" : "الجدول student_curriculum_progress غير موجود\n";
    
    if ($hasTable) {
        $columns = DB::select('SHOW COLUMNS FROM student_curriculum_progress');
        echo "عدد الأعمدة: " . count($columns) . "\n";
        foreach ($columns as $column) {
            echo "- " . $column->Field . " (" . $column->Type . ")\n";
        }
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
