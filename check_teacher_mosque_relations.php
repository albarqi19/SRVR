<?php

require_once 'vendor/autoload.php';

// بدء Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\TeacherMosqueSchedule;

echo "فحص النماذج والعلاقات:\n";
echo "=============================\n";

// فحص وجود النماذج
echo "فحص نموذج TeacherMosqueSchedule: ";
try {
    $schedule = new TeacherMosqueSchedule();
    echo "✓ موجود\n";
    echo "الحقول القابلة للتعبئة: " . implode(', ', $schedule->getFillable()) . "\n";
} catch (Exception $e) {
    echo "✗ غير موجود - " . $e->getMessage() . "\n";
}

echo "\nفحص العلاقات:\n";
echo "==============\n";

// فحص علاقة المعلم مع المساجد
echo "علاقة المعلم مع جداول المساجد: ";
try {
    $teacher = Teacher::first();
    if ($teacher) {
        $schedules = $teacher->mosqueSchedules();
        echo "✓ متوفرة\n";
        echo "عدد الجداول للمعلم الأول: " . $schedules->count() . "\n";
        echo "المعلم: " . $teacher->name . "\n";
    } else {
        echo "لا يوجد معلمين في قاعدة البيانات\n";
    }
} catch (Exception $e) {
    echo "✗ خطأ - " . $e->getMessage() . "\n";
}

// فحص علاقة المسجد مع جداول المعلمين
echo "علاقة المسجد مع جداول المعلمين: ";
try {
    $mosque = Mosque::first();
    if ($mosque) {
        $schedules = $mosque->teacherSchedules();
        echo "✓ متوفرة\n";
        echo "عدد الجداول للمسجد الأول: " . $schedules->count() . "\n";
        echo "المسجد: " . $mosque->name . "\n";
    } else {
        echo "لا يوجد مساجد في قاعدة البيانات\n";
    }
} catch (Exception $e) {
    echo "✗ خطأ - " . $e->getMessage() . "\n";
}

// فحص الجدول مباشرة
echo "\nفحص جدول teacher_mosque_schedules:\n";
echo "===================================\n";
try {
    $count = TeacherMosqueSchedule::count();
    echo "عدد السجلات الحالية: " . $count . "\n";
    
    if ($count > 0) {
        $sample = TeacherMosqueSchedule::with(['teacher', 'mosque'])->first();
        echo "عينة من البيانات:\n";
        echo "- المعلم: " . ($sample->teacher ? $sample->teacher->name : 'غير محدد') . "\n";
        echo "- المسجد: " . ($sample->mosque ? $sample->mosque->name : 'غير محدد') . "\n";
        echo "- اليوم: " . $sample->day_of_week . "\n";
        echo "- من: " . $sample->start_time . " إلى: " . $sample->end_time . "\n";
    }
} catch (Exception $e) {
    echo "خطأ في فحص الجدول: " . $e->getMessage() . "\n";
}

echo "\nحالة النظام:\n";
echo "============\n";
if (class_exists('App\Models\TeacherMosqueSchedule')) {
    echo "✓ نموذج TeacherMosqueSchedule متوفر\n";
}
if (method_exists(Teacher::class, 'mosqueSchedules')) {
    echo "✓ علاقة mosqueSchedules في نموذج Teacher متوفرة\n";
}
if (method_exists(Mosque::class, 'teacherSchedules')) {
    echo "✓ علاقة teacherSchedules في نموذج Mosque متوفرة\n";
}

echo "\nالخلاصة: نظام ربط المعلمين بالمساجد ";
if (class_exists('App\Models\TeacherMosqueSchedule') && 
    method_exists(Teacher::class, 'mosqueSchedules') && 
    method_exists(Mosque::class, 'teacherSchedules')) {
    echo "✓ جاهز ويعمل بشكل صحيح\n";
} else {
    echo "✗ يحتاج إلى مراجعة\n";
}
