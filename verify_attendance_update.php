<?php

chdir(__DIR__);
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "فحص قيم حالة الحضور بعد التحديث...\n\n";

try {
    $statuses = DB::table('student_attendances')->select('status')->distinct()->get();
    echo "الحالات الموجودة الآن في قاعدة البيانات:\n";
    foreach ($statuses as $status) {
        $count = DB::table('student_attendances')->where('status', $status->status)->count();
        echo "- " . ($status->status ?? 'NULL') . " (عدد السجلات: $count)\n";
    }
    
    echo "\nعدد السجلات الإجمالي: " . DB::table('student_attendances')->count() . "\n";
    
    // اختبار نموذج StudentAttendance
    echo "\nاختبار النموذج:\n";
    $attendance = new \App\Models\StudentAttendance();
    $statusConstants = [];
    $reflection = new ReflectionClass($attendance);
    $constants = $reflection->getConstants();
    foreach ($constants as $name => $value) {
        if (strpos($name, 'STATUS') !== false) {
            $statusConstants[$name] = $value;
        }
    }
    
    if (!empty($statusConstants)) {
        echo "الثوابت المعرفة في النموذج:\n";
        foreach ($statusConstants as $name => $value) {
            echo "- $name: $value\n";
        }
    } else {
        echo "لم يتم العثور على ثوابت الحالة في النموذج\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
