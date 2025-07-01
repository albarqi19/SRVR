<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 اختبار ربط الطالب بالمعلم عبر الحلقة:\n";
echo str_repeat("=", 60) . "\n\n";

// تحقق من المعلم ID=8 (أحمد علي)
$teacherId = 8;
$date = '2025-06-30';

echo "📋 المعلم ID: $teacherId (أحمد علي)\n";
echo "📅 التاريخ: $date\n\n";

// 1. الحصول على معلومات المعلم والحلقة
echo "1️⃣ معلومات المعلم والحلقة:\n";
$teacher = DB::table('teachers')
    ->where('id', $teacherId)
    ->first();

if ($teacher) {
    echo "   اسم المعلم: $teacher->name\n";
    echo "   الحلقة ID: $teacher->quran_circle_id\n";
    echo "   نشط: " . ($teacher->is_active_user ? 'نعم' : 'لا') . "\n\n";
} else {
    echo "   ❌ المعلم غير موجود!\n";
    exit;
}

// 2. الحصول على طلاب هذه الحلقة
echo "2️⃣ طلاب الحلقة:\n";
$students = DB::table('students')
    ->where('quran_circle_id', $teacher->quran_circle_id)
    ->select('id', 'name')
    ->get();

echo "   عدد الطلاب: " . $students->count() . "\n";
foreach ($students->take(5) as $student) {
    echo "   - $student->name (ID: $student->id)\n";
}
if ($students->count() > 5) {
    echo "   ... و " . ($students->count() - 5) . " طالب آخر\n";
}

// 3. فحص جلسات التسميع لطلاب هذه الحلقة
echo "\n3️⃣ جلسات التسميع لطلاب الحلقة في $date:\n";
$recitationSessions = DB::table('recitation_sessions')
    ->join('students', 'recitation_sessions.student_id', '=', 'students.id')
    ->where('students.quran_circle_id', $teacher->quran_circle_id)
    ->whereDate('recitation_sessions.created_at', $date)
    ->select('recitation_sessions.*', 'students.name as student_name')
    ->get();

echo "   عدد جلسات التسميع: " . $recitationSessions->count() . "\n";
foreach ($recitationSessions as $session) {
    echo "   - $session->student_name: $session->recitation_type (معلم: $session->teacher_id)\n";
}

// 4. مشكلة الربط
echo "\n4️⃣ تحليل مشكلة الربط:\n";
if ($recitationSessions->count() > 0) {
    $teacherIds = $recitationSessions->pluck('teacher_id')->unique();
    echo "   المعلمون الذين سمعوا لطلاب هذه الحلقة:\n";
    foreach ($teacherIds as $tId) {
        $teacherName = DB::table('teachers')->where('id', $tId)->value('name');
        echo "     - معلم ID: $tId ($teacherName)\n";
    }
    
    if (!$teacherIds->contains($teacherId)) {
        echo "   ❌ المعلم $teacherId لم يسمع لأي طالب في هذا التاريخ\n";
    } else {
        echo "   ✅ المعلم $teacherId سمع لطلاب في هذا التاريخ\n";
    }
} else {
    echo "   ❌ لا توجد جلسات تسميع لطلاب هذه الحلقة في هذا التاريخ\n";
}

// 5. اختبار مع المعلم ID=1 الذي له جلسات
echo "\n" . str_repeat("=", 60) . "\n";
echo "🧪 اختبار مع المعلم ID=1 الذي له جلسات تسميع:\n";
echo str_repeat("=", 60) . "\n\n";

$teacherId2 = 1;
$teacher2 = DB::table('teachers')->where('id', $teacherId2)->first();

if ($teacher2) {
    echo "📋 المعلم: $teacher2->name (ID: $teacherId2)\n";
    echo "📍 الحلقة: $teacher2->quran_circle_id\n\n";
    
    // جلسات التسميع المباشرة
    $directSessions = DB::table('recitation_sessions')
        ->where('teacher_id', $teacherId2)
        ->whereDate('created_at', $date)
        ->get();
    
    echo "جلسات التسميع المباشرة: " . $directSessions->count() . "\n";
    
    // جلسات التسميع عبر الحلقة
    $circleSessions = DB::table('recitation_sessions')
        ->join('students', 'recitation_sessions.student_id', '=', 'students.id')
        ->where('students.quran_circle_id', $teacher2->quran_circle_id)
        ->whereDate('recitation_sessions.created_at', $date)
        ->get();
    
    echo "جلسات التسميع عبر الحلقة: " . $circleSessions->count() . "\n";
}

echo "\n✅ انتهى التحليل!\n";
?>
