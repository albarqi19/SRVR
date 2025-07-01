<?php
require_once 'vendor/autoload.php';

// بدء Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 اختبار دالة getTeacherActivityForDate للمعلم أحمد علي (ID: 8):\n";
echo "===============================================================\n\n";

$teacherId = 8;
$testDate = '2025-06-30';

echo "📋 المعلم ID: $teacherId\n";
echo "📅 التاريخ: $testDate\n\n";

// البحث عن طلاب المعلم
echo "1️⃣ البحث عن طلاب المعلم:\n";
$studentsCount = DB::table('students')
    ->join('quran_circles', 'students.quran_circle_id', '=', 'quran_circles.id')
    ->join('teachers', 'teachers.quran_circle_id', '=', 'quran_circles.id')
    ->where('teachers.id', $teacherId)
    ->count();

echo "   عدد الطلاب: $studentsCount\n\n";

// فحص تسجيل الحضور لهذا التاريخ
echo "2️⃣ فحص تسجيل الحضور:\n";
$attendanceRecorded = DB::table('attendances')
    ->whereDate('date', $testDate)
    ->where('attendable_type', 'App\Models\Student')
    ->whereIn('attendable_id', function($query) use ($teacherId) {
        $query->select('students.id')
            ->from('students')
            ->join('quran_circles', 'students.quran_circle_id', '=', 'quran_circles.id')
            ->join('teachers', 'teachers.quran_circle_id', '=', 'quran_circles.id')
            ->where('teachers.id', $teacherId);
    })
    ->exists();

echo "   تم تسجيل الحضور: " . ($attendanceRecorded ? 'نعم' : 'لا') . "\n";

// عدد الطلاب الذين تم تسجيل حضورهم
$attendanceCount = DB::table('attendances')
    ->whereDate('date', $testDate)
    ->where('attendable_type', 'App\Models\Student')
    ->whereIn('attendable_id', function($query) use ($teacherId) {
        $query->select('students.id')
            ->from('students')
            ->join('quran_circles', 'students.quran_circle_id', '=', 'quran_circles.id')
            ->join('teachers', 'teachers.quran_circle_id', '=', 'quran_circles.id')
            ->where('teachers.id', $teacherId);
    })
    ->count();

echo "   عدد الطلاب الحاضرين: $attendanceCount\n\n";

// فحص تسجيل التسميع لهذا التاريخ
echo "3️⃣ فحص تسجيل التسميع:\n";
$recitationRecorded = DB::table('recitation_sessions')
    ->whereDate('created_at', $testDate)
    ->where('teacher_id', $teacherId)
    ->exists();

echo "   تم تسجيل التسميع: " . ($recitationRecorded ? 'نعم' : 'لا') . "\n";

// عدد جلسات التسميع المسجلة
$recitationCount = DB::table('recitation_sessions')
    ->whereDate('created_at', $testDate)
    ->where('teacher_id', $teacherId)
    ->count();

echo "   عدد جلسات التسميع: $recitationCount\n";

// عدد الطلاب الذين تم تسميعهم
$recitedStudentsCount = DB::table('recitation_sessions')
    ->whereDate('created_at', $testDate)
    ->where('teacher_id', $teacherId)
    ->distinct('student_id')
    ->count();

echo "   عدد الطلاب المسمعين: $recitedStudentsCount\n\n";

// حساب النسب
$attendancePercentage = $studentsCount > 0 ? round(($attendanceCount / $studentsCount) * 100, 1) : 0;
$recitationPercentage = $studentsCount > 0 ? round(($recitedStudentsCount / $studentsCount) * 100, 1) : 0;

echo "4️⃣ النسب المئوية:\n";
echo "   نسبة الحضور: $attendancePercentage%\n";
echo "   نسبة التسميع: $recitationPercentage%\n\n";

// تحديد حالة النشاط
$activityStatus = 'غير نشط';
if ($attendanceRecorded && $recitationRecorded) {
    $activityStatus = 'نشط - مكتمل';
} elseif ($attendanceRecorded || $recitationRecorded) {
    $activityStatus = 'نشط - جزئي';
}

echo "5️⃣ حالة النشاط:\n";
echo "   الحالة: $activityStatus\n";
echo "   له نشاط: " . ($attendanceRecorded || $recitationRecorded ? 'نعم' : 'لا') . "\n\n";

// فحص تفصيلي للبيانات
echo "6️⃣ فحص تفصيلي:\n";
echo "   🔍 طلاب المعلم:\n";
$students = DB::table('students')
    ->join('quran_circles', 'students.quran_circle_id', '=', 'quran_circles.id')
    ->join('teachers', 'teachers.quran_circle_id', '=', 'quran_circles.id')
    ->where('teachers.id', $teacherId)
    ->select('students.id', 'students.name')
    ->get();

foreach ($students->take(5) as $student) {
    echo "     - $student->name (ID: $student->id)\n";
}

echo "\n   📋 سجلات الحضور لهذا التاريخ:\n";
$attendanceRecords = DB::table('attendances')
    ->join('students', 'attendances.attendable_id', '=', 'students.id')
    ->whereDate('attendances.date', $testDate)
    ->where('attendances.attendable_type', 'App\Models\Student')
    ->whereIn('students.id', $students->pluck('id'))
    ->select('students.name', 'attendances.status', 'attendances.period')
    ->get();

foreach ($attendanceRecords->take(5) as $record) {
    echo "     - $record->name: $record->status ($record->period)\n";
}

echo "\n   🎤 جلسات التسميع لهذا التاريخ:\n";
$recitationRecords = DB::table('recitation_sessions')
    ->join('students', 'recitation_sessions.student_id', '=', 'students.id')
    ->whereDate('recitation_sessions.created_at', $testDate)
    ->where('recitation_sessions.teacher_id', $teacherId)
    ->select('students.name', 'recitation_sessions.recitation_type', 'recitation_sessions.grade')
    ->get();

foreach ($recitationRecords as $record) {
    echo "     - $record->name: $record->recitation_type (درجة: $record->grade)\n";
}

echo "\n✅ انتهى الفحص التفصيلي!\n";

?>
