<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\CircleGroup;

echo "🔍 اختبار الفرق بين الحلقات العادية والحلقات الفرعية في عرض الطلاب\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// البحث عن معلم لديه حلقة
$teacher = Teacher::whereNotNull('quran_circle_id')->first();

if (!$teacher) {
    echo "❌ لا يوجد معلمين مرتبطين بحلقات\n";
    exit;
}

echo "👨‍🏫 المعلم المختار:\n";
echo "   - الاسم: {$teacher->name}\n";
echo "   - ID: {$teacher->id}\n";
echo "   - الحلقة: {$teacher->quran_circle_id}\n";
echo "   - المسجد: {$teacher->mosque_id}\n\n";

// جلب الحلقة
$circle = QuranCircle::find($teacher->quran_circle_id);
if (!$circle) {
    echo "❌ لا توجد حلقة مرتبطة\n";
    exit;
}

echo "🎯 الحلقة:\n";
echo "   - الاسم: {$circle->name}\n";
echo "   - النوع: {$circle->circle_type}\n";
echo "   - المسجد: {$circle->mosque_id}\n\n";

// الطريقة القديمة: جلب الطلاب فقط من quran_circle_id
echo "📊 الطريقة القديمة (فقط من quran_circle_id):\n";
$oldWayStudents = Student::where('quran_circle_id', $circle->id)->get();
echo "   - عدد الطلاب: " . $oldWayStudents->count() . "\n";
if ($oldWayStudents->count() > 0) {
    echo "   - أسماء الطلاب:\n";
    foreach ($oldWayStudents as $student) {
        $groupName = $student->circle_group_id ? 
            "، الحلقة الفرعية: " . ($student->circleGroup->name ?? $student->circle_group_id) : 
            "، بدون حلقة فرعية";
        echo "     * {$student->name} (ID: {$student->id}{$groupName})\n";
    }
}
echo "\n";

// الطريقة الجديدة: جلب الطلاب من الحلقة الرئيسية + الحلقات الفرعية
echo "📊 الطريقة الجديدة (الحلقة الرئيسية + الحلقات الفرعية):\n";

// 1. الطلاب من الحلقة الرئيسية مباشرة
$mainCircleStudents = Student::where('quran_circle_id', $circle->id)
    ->whereNull('circle_group_id')
    ->get();

// 2. الطلاب من الحلقات الفرعية
$circleGroupStudents = Student::whereHas('circleGroup', function($query) use ($circle) {
    $query->where('quran_circle_id', $circle->id);
})->get();

$newWayStudents = $mainCircleStudents->merge($circleGroupStudents);

echo "   - طلاب الحلقة الرئيسية: " . $mainCircleStudents->count() . "\n";
echo "   - طلاب الحلقات الفرعية: " . $circleGroupStudents->count() . "\n";
echo "   - إجمالي الطلاب: " . $newWayStudents->count() . "\n";

if ($newWayStudents->count() > 0) {
    echo "   - تفاصيل الطلاب:\n";
    foreach ($newWayStudents as $student) {
        $location = $student->circle_group_id ? 
            "الحلقة الفرعية: " . ($student->circleGroup->name ?? $student->circle_group_id) : 
            "الحلقة الرئيسية";
        echo "     * {$student->name} (ID: {$student->id}) - {$location}\n";
    }
}
echo "\n";

// مقارنة النتائج
$difference = $newWayStudents->count() - $oldWayStudents->count();
echo "🔄 المقارنة:\n";
echo "   - الطريقة القديمة: {$oldWayStudents->count()} طالب\n";
echo "   - الطريقة الجديدة: {$newWayStudents->count()} طالب\n";
echo "   - الفرق: {$difference} طالب إضافي\n";

if ($difference > 0) {
    echo "   ✅ الطريقة الجديدة تعرض طلاب أكثر (تشمل الحلقات الفرعية)\n";
} elseif ($difference < 0) {
    echo "   ⚠️ هناك مشكلة - الطريقة الجديدة تعرض طلاب أقل\n";
} else {
    echo "   ℹ️ لا يوجد فرق - ربما لا توجد حلقات فرعية لهذا المعلم\n";
}

// فحص الحلقات الفرعية الموجودة
echo "\n🎯 فحص الحلقات الفرعية:\n";
$circleGroups = CircleGroup::where('quran_circle_id', $circle->id)->get();
echo "   - عدد الحلقات الفرعية: " . $circleGroups->count() . "\n";

if ($circleGroups->count() > 0) {
    echo "   - قائمة الحلقات الفرعية:\n";
    foreach ($circleGroups as $group) {
        $groupStudents = Student::where('circle_group_id', $group->id)->count();
        $teacher_name = $group->teacher ? $group->teacher->name : 'لا يوجد معلم';
        echo "     * {$group->name} (ID: {$group->id}) - المعلم: {$teacher_name} - الطلاب: {$groupStudents}\n";
    }
}

echo "\n✅ انتهى الاختبار\n";
