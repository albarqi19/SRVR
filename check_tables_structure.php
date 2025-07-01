<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "فحص هيكل جداول الحضور والتسميع:\n";
echo str_repeat("=", 50) . "\n\n";

// فحص جدول student_attendances
echo "1. جدول student_attendances:\n";
try {
    $columns = Schema::getColumnListing('student_attendances');
    echo "الأعمدة: " . implode(', ', $columns) . "\n";
    
    $sampleData = DB::table('student_attendances')->take(1)->get();
    echo "عدد السجلات: " . DB::table('student_attendances')->count() . "\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// فحص جدول recitation_sessions
echo "2. جدول recitation_sessions:\n";
try {
    $columns = Schema::getColumnListing('recitation_sessions');
    echo "الأعمدة: " . implode(', ', $columns) . "\n";
    
    echo "عدد السجلات: " . DB::table('recitation_sessions')->count() . "\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// فحص النماذج
echo "3. فحص النماذج:\n";
try {
    echo "StudentAttendance Model: ";
    $model = new \App\Models\StudentAttendance();
    echo "موجود\n";
    echo "الحقول القابلة للتعبئة: " . implode(', ', $model->getFillable()) . "\n";
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}

echo "\n";

// البحث عن جداول الحضور
echo "4. البحث عن جداول الحضور:\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (stripos($tableName, 'attendance') !== false || stripos($tableName, 'attend') !== false) {
        echo "- $tableName\n";
    }
}

?>
