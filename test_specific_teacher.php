<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 اختبار المعلم عبدالله الشنقيطي (7 طلاب):\n";
echo str_repeat('=', 50) . "\n\n";

$teacherId = 89; // عبدالله الشنقيطي
$date = '2025-06-30';

echo "📋 المعلم: عبدالله الشنقيطي (ID: $teacherId)\n";
echo "📅 التاريخ: $date\n\n";

// البحث عن مجموعة المعلم
$teacherGroup = DB::table('circle_groups')
    ->where('teacher_id', $teacherId)
    ->first();

if ($teacherGroup) {
    echo "✅ مجموعة المعلم: $teacherGroup->name (ID: $teacherGroup->id)\n";
    
    // عدد الطلاب في المجموعة
    $studentsCount = DB::table('students')
        ->where('circle_group_id', $teacherGroup->id)
        ->where('is_active', true)
        ->count();
    
    echo "👥 عدد الطلاب في المجموعة: $studentsCount\n\n";
    
    // أسماء الطلاب
    $students = DB::table('students')
        ->where('circle_group_id', $teacherGroup->id)
        ->where('is_active', true)
        ->select('id', 'name')
        ->get();
    
    echo "📋 أسماء الطلاب:\n";
    foreach ($students as $student) {
        echo "   - $student->name (ID: $student->id)\n";
    }
    
    // فحص الحضور
    echo "\n📊 فحص الحضور في $date:\n";
    $attendanceRecords = DB::table('attendances')
        ->join('students', 'attendances.attendable_id', '=', 'students.id')
        ->where('attendances.attendable_type', 'App\Models\Student')
        ->whereDate('attendances.date', $date)
        ->whereIn('students.id', $students->pluck('id'))
        ->select('students.name', 'attendances.status')
        ->get();
    
    if ($attendanceRecords->count() > 0) {
        foreach ($attendanceRecords as $record) {
            echo "   ✅ $record->name: $record->status\n";
        }
    } else {
        echo "   ❌ لا توجد سجلات حضور\n";
    }
    
    // فحص التسميع
    echo "\n🎤 فحص التسميع في $date:\n";
    $recitationRecords = DB::table('recitation_sessions')
        ->join('students', 'recitation_sessions.student_id', '=', 'students.id')
        ->where('recitation_sessions.teacher_id', $teacherId)
        ->whereDate('recitation_sessions.created_at', $date)
        ->select('students.name', 'recitation_sessions.recitation_type', 'recitation_sessions.grade')
        ->get();
    
    if ($recitationRecords->count() > 0) {
        foreach ($recitationRecords as $record) {
            echo "   ✅ $record->name: $record->recitation_type (درجة: $record->grade)\n";
        }
    } else {
        echo "   ❌ لا توجد جلسات تسميع\n";
    }
    
} else {
    echo "❌ المعلم ليس له مجموعة\n";
}

echo "\n✅ انتهى الاختبار!\n";
?>
