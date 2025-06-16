<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\CircleGroup;

echo "🔍 فحص بيانات النظام\n";
echo "=" . str_repeat("=", 30) . "\n\n";

// فحص عدد المعلمين
$teachersCount = Teacher::count();
echo "👨‍🏫 إجمالي المعلمين: {$teachersCount}\n";

// فحص عدد الطلاب
$studentsCount = Student::count();
echo "👨‍🎓 إجمالي الطلاب: {$studentsCount}\n";

// فحص عدد الحلقات
$circlesCount = QuranCircle::count();
echo "🎯 إجمالي الحلقات: {$circlesCount}\n";

// فحص عدد الحلقات الفرعية
$circleGroupsCount = CircleGroup::count();
echo "🎯 إجمالي الحلقات الفرعية: {$circleGroupsCount}\n\n";

if ($teachersCount == 0) {
    echo "❌ لا توجد معلمين في النظام\n";
    exit;
}

if ($studentsCount == 0) {
    echo "❌ لا توجد طلاب في النظام\n";
    exit;
}

// عرض أول 3 معلمين
echo "👨‍🏫 أول 3 معلمين:\n";
$teachers = Teacher::take(3)->get();
foreach ($teachers as $teacher) {
    echo "   - ID: {$teacher->id}, الاسم: {$teacher->name}, المسجد: {$teacher->mosque_id}, الحلقة: {$teacher->quran_circle_id}\n";
}
echo "\n";

// عرض أول 3 طلاب
echo "👨‍🎓 أول 3 طلاب:\n";
$students = Student::take(3)->get();
foreach ($students as $student) {
    echo "   - ID: {$student->id}, الاسم: {$student->name}, الحلقة: {$student->quran_circle_id}, الحلقة الفرعية: {$student->circle_group_id}\n";
}
echo "\n";

// عرض الحلقات الفرعية إن وجدت
if ($circleGroupsCount > 0) {
    echo "🎯 الحلقات الفرعية:\n";
    $circleGroups = CircleGroup::take(3)->get();
    foreach ($circleGroups as $group) {
        echo "   - ID: {$group->id}, الاسم: {$group->name}, الحلقة الرئيسية: {$group->quran_circle_id}, المعلم: {$group->teacher_id}\n";
    }
    echo "\n";
}

echo "✅ انتهى الفحص\n";
