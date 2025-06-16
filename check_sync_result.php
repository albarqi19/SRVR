<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

echo "=== فحص الربط بعد Migration ===" . PHP_EOL;

// فحص الحلقات الفرعية بعد الربط
$circleGroup = App\Models\CircleGroup::with('teacher')->first();
if ($circleGroup) {
    echo "الحلقة الفرعية: " . $circleGroup->name . PHP_EOL;
    echo "المعلم: " . ($circleGroup->teacher ? $circleGroup->teacher->name : 'لا يوجد') . PHP_EOL;
    echo "teacher_id: " . $circleGroup->teacher_id . PHP_EOL;
} else {
    echo "لا توجد حلقات فرعية" . PHP_EOL;
}

echo PHP_EOL;

// فحص التكليفات
$assignments = App\Models\TeacherCircleAssignment::with(['teacher', 'circle'])->where('is_active', true)->get();
echo "التكليفات النشطة:" . PHP_EOL;
foreach ($assignments as $assignment) {
    echo "- المعلم: " . $assignment->teacher->name . " في حلقة: " . $assignment->circle->name . PHP_EOL;
}
