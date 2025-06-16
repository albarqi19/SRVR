<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

echo "الطلاب في الحلقة رقم 1:\n";
$students = Student::where('quran_circle_id', 1)->get(['id', 'name', 'circle_group_id']);
foreach ($students as $student) {
    echo "- الطالب: {$student->name} (ID: {$student->id}, circle_group_id: " . ($student->circle_group_id ?? 'null') . ")\n";
}

echo "\nجميع الطلاب مع حلقاتهم:\n";
$allStudents = Student::with('quranCircle:id,name')->get(['id', 'name', 'quran_circle_id', 'circle_group_id']);
foreach ($allStudents as $student) {
    $circleName = $student->quranCircle ? $student->quranCircle->name : 'غير مرتبط';
    echo "- {$student->name} -> الحلقة: {$circleName} (circle_group_id: " . ($student->circle_group_id ?? 'null') . ")\n";
}
