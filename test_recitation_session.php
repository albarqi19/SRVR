<?php

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 1. فحص البيانات الموجودة ===\n";
echo "عدد الطلاب: " . App\Models\Student::count() . "\n";
echo "عدد المعلمين: " . App\Models\User::where('role', 'teacher')->count() . "\n";
echo "عدد الحلقات: " . App\Models\QuranCircle::count() . "\n";
echo "\n";

echo "=== 2. عرض أول طالب ومعلم وحلقة ===\n";
$student = App\Models\Student::first();
if($student) {
    echo "أول طالب ID: " . $student->id . " - الاسم: " . $student->name . "\n";
} else {
    echo "لا يوجد طلاب\n";
}

$teacher = App\Models\User::where('role', 'teacher')->first();
if($teacher) {
    echo "أول معلم ID: " . $teacher->id . " - الاسم: " . $teacher->name . "\n";
} else {
    echo "لا يوجد معلمين\n";
}

$circle = App\Models\QuranCircle::first();
if($circle) {
    echo "أول حلقة ID: " . $circle->id . " - الاسم: " . $circle->name . "\n";
} else {
    echo "لا يوجد حلقات\n";
}
echo "\n";

echo "=== 3. محاولة إنشاء جلسة تسميع ===\n";
try {
    $session = App\Models\RecitationSession::create([
        'student_id' => 1,
        'teacher_id' => 1,
        'quran_circle_id' => 1,
        'start_surah_number' => 2,
        'start_verse' => 1,
        'end_surah_number' => 2,
        'end_verse' => 50,
        'recitation_type' => 'حفظ',
        'duration_minutes' => 15,
        'grade' => 8.5,
        'evaluation' => 'جيد جداً',
        'teacher_notes' => 'أداء جيد'
    ]);
    echo "✅ تم إنشاء الجلسة بنجاح!\n";
    echo "Session ID: " . $session->session_id . "\n";
    echo "Database ID: " . $session->id . "\n";
} catch(Exception $e) {
    echo "❌ خطأ في إنشاء الجلسة: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== 4. اختبار Controller ===\n";
try {
    $request = new Illuminate\Http\Request([
        'student_id' => 1,
        'teacher_id' => 1,
        'quran_circle_id' => 1,
        'start_surah_number' => 2,
        'start_verse' => 1,
        'end_surah_number' => 2,
        'end_verse' => 50,
        'recitation_type' => 'حفظ',
        'duration_minutes' => 15,
        'grade' => 8.5,
        'evaluation' => 'جيد جداً',
        'teacher_notes' => 'أداء جيد'
    ]);
    
    $controller = new App\Http\Controllers\Api\RecitationSessionController();
    $result = $controller->store($request);
    echo "✅ Controller Response:\n";
    echo $result->getContent() . "\n";
} catch(Exception $e) {
    echo "❌ خطأ في Controller: " . $e->getMessage() . "\n";
}

echo "\n=== 5. عرض جميع الجلسات الموجودة ===\n";
$sessions = App\Models\RecitationSession::with(['student', 'teacher', 'circle'])->get();
echo "عدد الجلسات: " . $sessions->count() . "\n";
foreach($sessions as $session) {
    echo "- Session ID: " . $session->session_id . " | Grade: " . $session->grade . " | Type: " . $session->recitation_type . "\n";
}
