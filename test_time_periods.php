<?php

require_once 'vendor/autoload.php';

use App\Models\TeacherCircleAssignment;
use App\Models\QuranCircle;
use App\Models\Teacher;

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار منطق الفترات الزمنية ===\n\n";

// جلب جميع الحلقات مع الفترات الزمنية
$circles = QuranCircle::select('id', 'name', 'time_period')->get();

echo "الحلقات الموجودة:\n";
foreach ($circles as $circle) {
    echo "- حلقة {$circle->id}: {$circle->name} - الفترة: {$circle->time_period}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// جلب المعلمين
$teachers = Teacher::select('id', 'name')->get();

echo "المعلمون الموجودون:\n";
foreach ($teachers as $teacher) {
    echo "- معلم {$teacher->id}: {$teacher->name}\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// جلب التكليفات الحالية
$assignments = TeacherCircleAssignment::with(['teacher', 'circle'])->get();

echo "التكليفات الحالية:\n";
foreach ($assignments as $assignment) {
    echo "- المعلم: {$assignment->teacher->name}\n";
    echo "  الحلقة: {$assignment->circle->name}\n";
    echo "  الفترة: {$assignment->circle->time_period}\n";
    echo "  نشط: " . ($assignment->is_active ? 'نعم' : 'لا') . "\n";
    echo "  من تاريخ: {$assignment->start_date}\n";
    echo "  إلى تاريخ: " . ($assignment->end_date ?: 'مفتوح') . "\n\n";
}

echo str_repeat("=", 50) . "\n";

// اختبار التعارض
if ($teachers->count() > 0 && $circles->count() > 1) {
    $teacher = $teachers->first();
    $firstCircle = $circles->first();
    $secondCircle = $circles->skip(1)->first();
    
    echo "اختبار التعارض:\n";
    echo "المعلم: {$teacher->name}\n";
    echo "الحلقة الأولى: {$firstCircle->name} - الفترة: {$firstCircle->time_period}\n";
    echo "الحلقة الثانية: {$secondCircle->name} - الفترة: {$secondCircle->time_period}\n\n";
    
    $hasConflict = TeacherCircleAssignment::hasTimeConflict(
        $teacher->id,
        $secondCircle->id,
        now()->format('Y-m-d')
    );
    
    if ($firstCircle->time_period === $secondCircle->time_period) {
        echo "نفس الفترة الزمنية - يجب أن يكون هناك تعارض: " . ($hasConflict ? 'نعم ✅' : 'لا ❌') . "\n";
    } else {
        echo "فترات زمنية مختلفة - يجب ألا يكون هناك تعارض: " . ($hasConflict ? 'نعم ❌' : 'لا ✅') . "\n";
    }
}

echo "\n=== انتهى الاختبار ===\n";
