<?php
require_once 'vendor/autoload.php';

// بدء Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "فحص التواريخ الموجودة في البيانات:\n";
echo "=====================================\n\n";

// فحص تواريخ الحضور
echo "📅 تواريخ الحضور في جدول attendances:\n";
$attendanceDates = DB::table('attendances')
    ->select('date', DB::raw('COUNT(*) as count'))
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->get();

foreach ($attendanceDates as $date) {
    echo "   $date->date: $date->count سجل\n";
}

echo "\n📅 تواريخ التسميع في جدول recitation_sessions:\n";
$recitationDates = DB::table('recitation_sessions')
    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
    ->groupBy(DB::raw('DATE(created_at)'))
    ->orderBy('date', 'desc')
    ->get();

foreach ($recitationDates as $date) {
    echo "   $date->date: $date->count سجل\n";
}

echo "\n🔍 فحص المعلمين النشطين:\n";
$teachers = DB::table('teachers')
    ->select('id', 'name', 'quran_circle_id', 'is_active_user')
    ->where('is_active_user', true)
    ->get();

echo "عدد المعلمين النشطين: " . $teachers->count() . "\n";

// فحص المعلمين في الحلقة المشرف عليها
echo "\n🔍 فحص المعلمين في الحلقة المشرف عليها (supervisor_id = 1):\n";
$supervisedTeachers = DB::table('teachers')
    ->join('circle_supervisors', 'teachers.quran_circle_id', '=', 'circle_supervisors.quran_circle_id')
    ->where('circle_supervisors.supervisor_id', 1)
    ->where('circle_supervisors.is_active', true)
    ->where('teachers.is_active_user', true)
    ->select('teachers.id', 'teachers.name', 'teachers.quran_circle_id')
    ->get();

echo "عدد المعلمين المشرف عليهم: " . $supervisedTeachers->count() . "\n";
foreach ($supervisedTeachers->take(5) as $teacher) {
    echo "   - $teacher->name (ID: $teacher->id, Circle: $teacher->quran_circle_id)\n";
}

// فحص بيانات محددة للمعلم الأول
if ($supervisedTeachers->count() > 0) {
    $firstTeacher = $supervisedTeachers->first();
    echo "\n📊 فحص بيانات المعلم الأول ($firstTeacher->name):\n";
    
    // فحص الحضور
    $attendanceData = DB::table('attendances')
        ->join('students', 'attendances.attendable_id', '=', 'students.id')
        ->where('attendances.attendable_type', 'App\\Models\\Student')
        ->where('students.quran_circle_id', $firstTeacher->quran_circle_id)
        ->select('attendances.date', DB::raw('COUNT(*) as count'))
        ->groupBy('attendances.date')
        ->orderBy('attendances.date', 'desc')
        ->get();
    
    echo "   حضور الطلاب:\n";
    foreach ($attendanceData->take(5) as $attendance) {
        echo "     $attendance->date: $attendance->count طالب\n";
    }
    
    // فحص التسميع
    $recitationData = DB::table('recitation_sessions')
        ->where('teacher_id', $firstTeacher->id)
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'desc')
        ->get();
    
    echo "   جلسات التسميع:\n";
    foreach ($recitationData->take(5) as $recitation) {
        echo "     $recitation->date: $recitation->count جلسة\n";
    }
}

echo "\n🎯 فحص تاريخ محدد (2025-06-30):\n";
$specificDate = '2025-06-30';

// فحص الحضور لتاريخ محدد
$attendanceCount = DB::table('attendances')
    ->join('students', 'attendances.attendable_id', '=', 'students.id')
    ->join('teachers', 'students.quran_circle_id', '=', 'teachers.quran_circle_id')
    ->where('attendances.attendable_type', 'App\\Models\\Student')
    ->where('attendances.date', $specificDate)
    ->where('teachers.is_active_user', true)
    ->count();

echo "عدد سجلات الحضور في $specificDate: $attendanceCount\n";

// فحص التسميع لتاريخ محدد
$recitationCount = DB::table('recitation_sessions')
    ->whereDate('created_at', $specificDate)
    ->count();

echo "عدد جلسات التسميع في $specificDate: $recitationCount\n";

?>
