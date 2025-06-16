<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== فحص البيانات المتاحة ===" . PHP_EOL;

// فحص المناهج المتاحة
$curriculums = App\Models\Curriculum::all();
echo "المناهج المتاحة:" . PHP_EOL;
foreach ($curriculums as $curriculum) {
    echo "  - ID: {$curriculum->id}, الاسم: {$curriculum->name}" . PHP_EOL;
}

// فحص الطالب 1
$student = App\Models\Student::find(1);
if ($student) {
    echo "\nبيانات الطالب ID 1:" . PHP_EOL;
    echo "  - الاسم: {$student->name}" . PHP_EOL;
    echo "  - نشط: " . ($student->is_active ? 'نعم' : 'لا') . PHP_EOL;
    
    // فحص StudentProgress للطالب
    $progress = App\Models\StudentProgress::where('student_id', 1)->where('is_active', true)->first();
    if ($progress) {
        echo "  - لديه StudentProgress نشط مع curriculum_id: {$progress->curriculum_id}" . PHP_EOL;
    } else {
        echo "  - لا يوجد StudentProgress نشط" . PHP_EOL;
    }
}
