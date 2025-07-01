<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\RecitationSession;
use App\Models\StudentAttendance;

echo "🚀 اختبار شامل لربط التسميع والحضور مع المعلم\n";
echo str_repeat("=", 60) . "\n\n";

// معلومات الاختبار
$teacherId = 1;
$studentId = 36; // ناصر فاروق ناصر الجويسم
$circleId = 1;
$testDate = '2025-07-01';

echo "📋 معلومات الاختبار:\n";
echo "   المعلم ID: $teacherId\n";
echo "   الطالب ID: $studentId\n";
echo "   الحلقة ID: $circleId\n";
echo "   التاريخ: $testDate\n\n";

// الخطوة 1: تسجيل حضور الطالب
echo "1️⃣ تسجيل حضور الطالب:\n";
try {
    // استخدام الجدول الصحيح student_attendances
    $attendanceId = DB::table('student_attendances')->insertGetId([
        'student_id' => $studentId,
        'date' => $testDate,
        'status' => 'حاضر',
        'period' => null,
        'notes' => 'تسجيل تلقائي من اختبار API',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "   ✅ تم تسجيل الحضور بنجاح - ID: {$attendanceId}\n";
    echo "   📅 التاريخ: {$testDate}\n";
    echo "   📊 الحالة: حاضر\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في تسجيل الحضور: {$e->getMessage()}\n\n";
}

// الخطوة 2: إنشاء جلسة تسميع
echo "2️⃣ إنشاء جلسة تسميع:\n";
try {
    $session = RecitationSession::create([
        'student_id' => $studentId,
        'teacher_id' => $teacherId,
        'quran_circle_id' => $circleId,
        'session_id' => 'WORKFLOW_' . time() . '_' . uniqid(),
        'start_surah_number' => 2,
        'start_verse' => 1,
        'end_surah_number' => 2,
        'end_verse' => 20,
        'recitation_type' => 'حفظ',
        'duration_minutes' => 25,
        'grade' => 8.0,
        'evaluation' => 'جيد جداً',
        'teacher_notes' => 'اختبار شامل لربط الحضور والتسميع - تم الإنشاء تلقائياً',
        'status' => 'مكتملة'
    ]);
    
    echo "   ✅ تم إنشاء جلسة التسميع بنجاح - ID: {$session->id}\n";
    echo "   🆔 معرف الجلسة: {$session->session_id}\n";
    echo "   📖 السور: {$session->start_surah_number}:{$session->start_verse} - {$session->end_surah_number}:{$session->end_verse}\n";
    echo "   🎯 النوع: {$session->recitation_type}\n";
    echo "   📊 التقييم: {$session->evaluation} ({$session->grade})\n";
    echo "   ⏱️ المدة: {$session->duration_minutes} دقيقة\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء جلسة التسميع: {$e->getMessage()}\n\n";
}

// الخطوة 3: التحقق من الربط
echo "3️⃣ التحقق من الربط بين الحضور والتسميع:\n";

// جلب الحضور
$todayAttendance = DB::table('student_attendances')
    ->where('student_id', $studentId)
    ->whereDate('date', $testDate)
    ->get();

echo "   📋 سجل الحضور اليوم: " . $todayAttendance->count() . " سجل\n";
foreach ($todayAttendance as $att) {
    echo "      - الحالة: {$att->status} (الملاحظات: " . ($att->notes ?? 'لا توجد') . ")\n";
}

// جلب جلسات التسميع
$todaySessions = DB::table('recitation_sessions')
    ->where('student_id', $studentId)
    ->where('teacher_id', $teacherId)
    ->whereDate('created_at', $testDate)
    ->get();

echo "   📖 جلسات التسميع اليوم: " . $todaySessions->count() . " جلسة\n";
foreach ($todaySessions as $ses) {
    echo "      - الجلسة: {$ses->session_id} ({$ses->recitation_type})\n";
    echo "        التقييم: {$ses->evaluation} - الدرجة: {$ses->grade}\n";
    echo "        الحالة: {$ses->status}\n";
}

// الخطوة 4: إحصائيات المعلم
echo "\n4️⃣ إحصائيات المعلم لهذا اليوم:\n";

$teacherStats = [
    'attendance_recorded' => DB::table('student_attendances')
        ->whereDate('date', $testDate)
        ->count(),
    'sessions_created' => DB::table('recitation_sessions')
        ->where('teacher_id', $teacherId)
        ->whereDate('created_at', $testDate)
        ->count(),
    'students_attended' => DB::table('student_attendances')
        ->where('status', 'حاضر')
        ->whereDate('date', $testDate)
        ->distinct('student_id')
        ->count(),
    'students_recited' => DB::table('recitation_sessions')
        ->where('teacher_id', $teacherId)
        ->whereDate('created_at', $testDate)
        ->distinct('student_id')
        ->count()
];

echo "   📊 سجلات الحضور: {$teacherStats['attendance_recorded']}\n";
echo "   📖 جلسات التسميع: {$teacherStats['sessions_created']}\n";
echo "   👥 الطلاب الحاضرون: {$teacherStats['students_attended']}\n";
echo "   🎯 الطلاب الذين سمعوا: {$teacherStats['students_recited']}\n";

// حساب نسبة التطابق
$matchRate = 0;
if ($teacherStats['students_attended'] > 0) {
    $matchRate = ($teacherStats['students_recited'] / $teacherStats['students_attended']) * 100;
}

echo "   📈 نسبة التطابق (تسميع/حضور): " . number_format($matchRate, 1) . "%\n";

// الخطوة 5: ملخص النتائج
echo "\n" . str_repeat("=", 60) . "\n";
echo "📋 ملخص اختبار الربط بين التسميع والحضور:\n";
echo str_repeat("=", 60) . "\n";

$testResults = [
    '✅ تسجيل الحضور' => isset($attendanceId) ? 'نجح' : 'فشل',
    '✅ إنشاء جلسة التسميع' => isset($session) ? 'نجح' : 'فشل',
    '✅ ربط البيانات' => ($todayAttendance->count() > 0 && $todaySessions->count() > 0) ? 'نجح' : 'فشل',
    '✅ إحصائيات المعلم' => ($teacherStats['attendance_recorded'] > 0 || $teacherStats['sessions_created'] > 0) ? 'نجح' : 'فشل'
];

foreach ($testResults as $test => $result) {
    $color = ($result === 'نجح') ? 'نجح ✓' : 'فشل ✗';
    echo "$test: $color\n";
}

echo "\n🎯 النتيجة النهائية:\n";
if (count(array_filter($testResults, fn($r) => $r === 'نجح')) === count($testResults)) {
    echo "🏆 جميع الاختبارات نجحت! النظام يربط بين التسميع والحضور بنجاح.\n";
} else {
    echo "⚠️ بعض الاختبارات فشلت. راجع الأخطاء أعلاه.\n";
}

echo "\n📊 معلومات مفيدة:\n";
if (isset($session)) {
    echo "🆔 معرف جلسة التسميع الجديدة: {$session->session_id}\n";
}
if (isset($attendanceId)) {
    echo "📅 معرف سجل الحضور: {$attendanceId}\n";
}

echo "\n✨ انتهى الاختبار!\n";

?>
