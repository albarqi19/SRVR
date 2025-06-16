<?php
// فحص سريع لمتطلبات API
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== فحص البيانات المطلوبة للـ API ===\n";

// فحص الطلاب
$students = App\Models\Student::count();
echo "عدد الطلاب: $students\n";

// فحص المعلمين
$teachers = App\Models\User::whereIn('role', ['teacher', 'admin'])->count();
echo "عدد المعلمين: $teachers\n";

// فحص الحلقات
$circles = App\Models\QuranCircle::count();
echo "عدد الحلقات: $circles\n";

// فحص قيم evaluation المسموحة
echo "\nقيم evaluation المسموحة:\n";
$allowedEvaluations = ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'];
foreach ($allowedEvaluations as $eval) {
    echo "- $eval\n";
}

// فحص قيم recitation_type المسموحة
echo "\nقيم recitation_type المسموحة:\n";
$allowedTypes = ['حفظ', 'مراجعة صغرى', 'مراجعة كبرى', 'تثبيت'];
foreach ($allowedTypes as $type) {
    echo "- $type\n";
}

echo "\n=== اختبار إنشاء جلسة ===\n";
try {
    $session = App\Models\RecitationSession::create([
        'student_id' => 1,
        'teacher_id' => 1,
        'quran_circle_id' => 1,
        'start_surah_number' => 1,
        'start_verse' => 1,
        'end_surah_number' => 1,
        'end_verse' => 10,
        'recitation_type' => 'مراجعة صغرى',
        'duration_minutes' => 30,
        'grade' => 8.5,
        'evaluation' => 'جيد جداً',
        'teacher_notes' => 'اختبار مباشر'
    ]);
    
    echo "✅ نجح إنشاء الجلسة!\n";
    echo "Session ID: {$session->session_id}\n";
    echo "Database ID: {$session->id}\n";
} catch (Exception $e) {
    echo "❌ فشل في إنشاء الجلسة: " . $e->getMessage() . "\n";
}
