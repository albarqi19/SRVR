<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== فحص الجداول الموجودة ===\n";
echo "student_progress: " . (Schema::hasTable('student_progress') ? 'موجود' : 'غير موجود') . "\n";
echo "student_curricula: " . (Schema::hasTable('student_curricula') ? 'موجود' : 'غير موجود') . "\n";
echo "student_curriculum_progress: " . (Schema::hasTable('student_curriculum_progress') ? 'موجود' : 'غير موجود') . "\n";

echo "\n=== أعمدة جدول student_progress ===\n";
if (Schema::hasTable('student_progress')) {
    $columns = DB::select('SHOW COLUMNS FROM student_progress');
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
}

echo "\n=== أعمدة جدول student_curricula ===\n";
if (Schema::hasTable('student_curricula')) {
    $columns = DB::select('SHOW COLUMNS FROM student_curricula');
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
}
