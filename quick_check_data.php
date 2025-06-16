<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

echo "=== فحص البيانات الحالية ===" . PHP_EOL;

// فحص الحلقات الفرعية
$circleGroups = App\Models\CircleGroup::with('teacher')->get();
echo "عدد الحلقات الفرعية: " . $circleGroups->count() . PHP_EOL;

foreach ($circleGroups as $group) {
    echo "المجموعة: " . $group->name . " - معلم: " . ($group->teacher ? $group->teacher->name : 'لا يوجد') . PHP_EOL;
}

echo PHP_EOL;

// فحص التكليفات الجديدة
$assignments = App\Models\TeacherCircleAssignment::with(['teacher', 'circle'])->get();
echo "عدد التكليفات: " . $assignments->count() . PHP_EOL;

foreach ($assignments as $assignment) {
    echo "المعلم: " . $assignment->teacher->name . " - الحلقة: " . $assignment->circle->name . " - نشط: " . ($assignment->is_active ? 'نعم' : 'لا') . PHP_EOL;
}

echo PHP_EOL;

// فحص المعلمين والحلقات المرتبطة بهم
$teachers = App\Models\Teacher::with('quranCircle')->get();
echo "عدد المعلمين: " . $teachers->count() . PHP_EOL;

foreach ($teachers as $teacher) {
    echo "المعلم: " . $teacher->name . " - الحلقة القديمة: " . ($teacher->quranCircle ? $teacher->quranCircle->name : 'لا يوجد') . PHP_EOL;
}
