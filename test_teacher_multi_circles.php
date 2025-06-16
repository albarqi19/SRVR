<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\TeacherCircleAssignment;
use App\Models\Mosque;

// إعداد Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار نظام تعدد الحلقات للمعلمين ===\n\n";

try {
    // 1. اختبار إنشاء تكليفات متعددة للمعلم
    echo "1. اختبار إنشاء تكليفات متعددة للمعلم:\n";
    
    $teacher = Teacher::first();
    if (!$teacher) {
        echo "❌ لا يوجد معلمون في النظام\n";
        exit;
    }
    
    $circles = QuranCircle::limit(2)->get();
    if ($circles->count() < 2) {
        echo "❌ يجب وجود حلقتين على الأقل للاختبار\n";
        exit;
    }
    
    echo "المعلم: {$teacher->name}\n";
    echo "الحلقات المتاحة: " . $circles->count() . "\n";
    
    // إنشاء تكليفات
    foreach ($circles as $index => $circle) {
        $assignment = TeacherCircleAssignment::create([
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'is_active' => true,
            'start_date' => now()->addDays($index),
            'notes' => 'اختبار تعدد الحلقات - حلقة ' . ($index + 1)
        ]);
        
        echo "✅ تم إنشاء تكليف للحلقة: {$circle->name}\n";
    }
    
    // 2. اختبار العلاقات الجديدة
    echo "\n2. اختبار العلاقات الجديدة:\n";
    
    $teacher = Teacher::with(['circleAssignments', 'activeCircles'])->find($teacher->id);
    echo "عدد تكليفات المعلم: " . $teacher->circleAssignments->count() . "\n";
    echo "عدد الحلقات النشطة: " . $teacher->activeCircles->count() . "\n";
    
    foreach ($teacher->activeCircles as $circle) {
        echo "- حلقة نشطة: {$circle->name}\n";
    }
    
    // 3. اختبار العلاقة المعكوسة من الحلقات
    echo "\n3. اختبار العلاقة المعكوسة من الحلقات:\n";
    
    $circle = QuranCircle::with(['teacherAssignments', 'activeTeachers'])->first();
    echo "الحلقة: {$circle->name}\n";
    echo "عدد التكليفات: " . $circle->teacherAssignments->count() . "\n";
    echo "عدد المعلمين النشطين: " . $circle->activeTeachers->count() . "\n";
    
    foreach ($circle->activeTeachers as $teacher) {
        $pivot = $teacher->pivot;
        echo "- معلم نشط: {$teacher->name} (من {$pivot->start_date})\n";
    }
    
    // 4. اختبار وظائف النموذج
    echo "\n4. اختبار وظائف النموذج:\n";
    
    $assignment = TeacherCircleAssignment::first();
    if ($assignment) {
        echo "✅ تكليف موجود: ID {$assignment->id}\n";
        echo "✅ دالة teacher(): " . ($assignment->teacher ? $assignment->teacher->name : 'فارغ') . "\n";
        echo "✅ دالة circle(): " . ($assignment->circle ? $assignment->circle->name : 'فارغ') . "\n";
        echo "✅ هل نشط؟ " . ($assignment->is_active ? 'نعم' : 'لا') . "\n";
    }
    
    // 5. اختبار Scopes
    echo "\n5. اختبار Scopes:\n";
    
    $activeCount = TeacherCircleAssignment::active()->count();
    $inactiveCount = TeacherCircleAssignment::where('is_active', false)->count();
    
    echo "التكليفات النشطة: {$activeCount}\n";
    echo "التكليفات غير النشطة: {$inactiveCount}\n";
    
    // 6. اختبار تعارض الأوقات (إذا كانت البيانات متوفرة)
    echo "\n6. اختبار تعارض الأوقات:\n";
    
    if ($assignment) {
        // محاولة إنشاء تكليف جديد قد يتعارض
        $testAssignment = new TeacherCircleAssignment([
            'teacher_id' => $assignment->teacher_id,
            'quran_circle_id' => $assignment->quran_circle_id,
            'start_date' => $assignment->start_date,
            'end_date' => null,
        ]);
        
        $hasConflict = TeacherCircleAssignment::hasTimeConflict(
            $testAssignment->teacher_id,
            $testAssignment->quran_circle_id,
            $testAssignment->start_date,
            $testAssignment->end_date
        );
        echo "هل يوجد تعارض؟ " . ($hasConflict ? 'نعم ✅' : 'لا ❌') . "\n";
    }
    
    // 7. إحصائيات عامة
    echo "\n7. إحصائيات عامة:\n";
    
    $totalTeachers = Teacher::count();
    $totalCircles = QuranCircle::count();
    $totalAssignments = TeacherCircleAssignment::count();
    $activeAssignments = TeacherCircleAssignment::active()->count();
    
    $teachersWithMultipleCircles = Teacher::whereHas('circleAssignments', function($q) {
        $q->active();
    }, '>=', 2)->count();
    
    $circlesWithMultipleTeachers = QuranCircle::whereHas('teacherAssignments', function($q) {
        $q->active();
    }, '>=', 2)->count();
    
    echo "إجمالي المعلمين: {$totalTeachers}\n";
    echo "إجمالي الحلقات: {$totalCircles}\n";
    echo "إجمالي التكليفات: {$totalAssignments}\n";
    echo "التكليفات النشطة: {$activeAssignments}\n";
    echo "المعلمون متعددو الحلقات: {$teachersWithMultipleCircles}\n";
    echo "الحلقات متعددة المعلمين: {$circlesWithMultipleTeachers}\n";
    
    echo "\n✅ تم اختبار النظام بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}
