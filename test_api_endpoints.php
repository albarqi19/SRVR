<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\Mosque;

echo "=== اختبار البيانات الموجودة ===\n";

// التحقق من وجود المساجد
$mosques = Mosque::count();
echo "عدد المساجد: $mosques\n";

// التحقق من وجود الحلقات
$circles = QuranCircle::count();
echo "عدد الحلقات: $circles\n";

// التحقق من وجود المعلمين
$teachers = Teacher::count();
echo "عدد المعلمين: $teachers\n";

// التحقق من وجود الطلاب
$students = Student::count();
echo "عدد الطلاب: $students\n";

echo "\n=== تفاصيل أول معلم ===\n";
$teacher = Teacher::with(['user', 'mosque'])->first();
if ($teacher) {
    echo "ID: " . $teacher->id . "\n";
    echo "الاسم: " . ($teacher->user->name ?? 'غير محدد') . "\n";
    echo "المسجد: " . ($teacher->mosque->name ?? 'غير محدد') . "\n";
    echo "رقم الحلقة: " . ($teacher->quran_circle_id ?? 'غير محدد') . "\n";
} else {
    echo "لا يوجد معلمين\n";
}

echo "\n=== تفاصيل أول حلقة ===\n";
$circle = QuranCircle::with(['teacher.user', 'mosque'])->first();
if ($circle) {
    echo "ID: " . $circle->id . "\n";
    echo "الاسم: " . $circle->name . "\n";
    echo "المعلم: " . ($circle->teacher->user->name ?? 'غير محدد') . "\n";
    echo "المسجد: " . ($circle->mosque->name ?? 'غير محدد') . "\n";
} else {
    echo "لا توجد حلقات\n";
}

echo "\n=== تفاصيل أول طالب ===\n";
$student = Student::with(['quranCircle', 'mosque'])->first();
if ($student) {
    echo "ID: " . $student->id . "\n";
    echo "الاسم: " . $student->name . "\n";
    echo "الحلقة: " . ($student->quranCircle->name ?? 'غير محدد') . "\n";
    echo "المسجد: " . ($student->mosque->name ?? 'غير محدد') . "\n";
} else {
    echo "لا يوجد طلاب\n";
}

echo "\n=== اختبار العلاقات ===\n";

if ($teacher) {
    echo "الحلقات التابعة للمعلم " . $teacher->id . ":\n";
    $teacherCircles = QuranCircle::where('teacher_id', $teacher->id)->get();
    foreach ($teacherCircles as $tc) {
        echo "- " . $tc->name . " (ID: " . $tc->id . ")\n";
    }
    
    echo "\nطلاب المعلم " . $teacher->id . ":\n";
    $circleIds = $teacherCircles->pluck('id');
    $teacherStudents = Student::whereIn('quran_circle_id', $circleIds)->get();
    foreach ($teacherStudents as $ts) {
        echo "- " . $ts->name . " (Surah: " . ($ts->curriculum->current_surah ?? 'غير محدد') . ")\n";
    }
}
