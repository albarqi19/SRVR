<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== API Validation Debug ===" . PHP_EOL;

// محاكاة البيانات التي ترسل عبر API
$apiData = [
    'student_id' => 1,
    'teacher_id' => 1,
    'quran_circle_id' => 1,
    'curriculum_id' => 1,
    'start_surah_number' => 1,
    'start_verse' => 1,
    'end_surah_number' => 1,
    'end_verse' => 7,
    'recitation_type' => 'حفظ',
    'duration_minutes' => 15,
    'grade' => 8.5,
    'evaluation' => 'جيد جداً',
    'teacher_notes' => 'Good performance',
    'status' => 'مكتملة'
];

echo "البيانات المرسلة:" . PHP_EOL;
foreach ($apiData as $key => $value) {
    echo "  $key: $value" . PHP_EOL;
}

echo PHP_EOL . "=== فحص التحقق من الصحة ===" . PHP_EOL;

// استخدام نفس قواعد التحقق الموجودة في Controller
$rules = [
    'student_id' => 'required|exists:students,id',
    'teacher_id' => 'required|exists:users,id',
    'quran_circle_id' => 'required|exists:quran_circles,id',
    'start_surah_number' => 'required|integer|min:1|max:114',
    'start_verse' => 'required|integer|min:1',
    'end_surah_number' => 'required|integer|min:1|max:114',
    'end_verse' => 'required|integer|min:1',
    'recitation_type' => 'required|in:حفظ,مراجعة صغرى,مراجعة كبرى,تثبيت',
    'duration_minutes' => 'nullable|integer|min:1',
    'grade' => 'required|numeric|min:0|max:10',
    'evaluation' => 'required|in:ممتاز,جيد جداً,جيد,مقبول,ضعيف',
    'teacher_notes' => 'nullable|string|max:1000',
    'curriculum_id' => 'nullable|exists:curriculums,id',
    'status' => 'nullable|in:جارية,غير مكتملة,مكتملة'
];

$validator = Illuminate\Support\Facades\Validator::make($apiData, $rules);

if ($validator->fails()) {
    echo "❌ فشل التحقق من الصحة:" . PHP_EOL;
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error" . PHP_EOL;
    }
} else {
    echo "✅ نجح التحقق من الصحة!" . PHP_EOL;
}

echo PHP_EOL . "=== فحص البيانات المطلوبة ===" . PHP_EOL;

// فحص وجود الطالب
$student = App\Models\Student::find($apiData['student_id']);
echo ($student ? "✅" : "❌") . " Student ID {$apiData['student_id']}: " . ($student ? $student->name : "غير موجود") . PHP_EOL;

// فحص وجود المعلم
$teacher = App\Models\User::find($apiData['teacher_id']);
echo ($teacher ? "✅" : "❌") . " Teacher ID {$apiData['teacher_id']}: " . ($teacher ? $teacher->name : "غير موجود") . PHP_EOL;

// فحص وجود الحلقة
$circle = App\Models\QuranCircle::find($apiData['quran_circle_id']);
echo ($circle ? "✅" : "❌") . " Circle ID {$apiData['quran_circle_id']}: " . ($circle ? $circle->name : "غير موجود") . PHP_EOL;

// فحص وجود المنهج
$curriculum = App\Models\Curriculum::find($apiData['curriculum_id']);
echo ($curriculum ? "✅" : "❌") . " Curriculum ID {$apiData['curriculum_id']}: " . ($curriculum ? $curriculum->name : "غير موجود") . PHP_EOL;

// فحص القيم المحددة
echo PHP_EOL . "=== فحص القيم المحددة ===" . PHP_EOL;

$validRecitationTypes = ['حفظ', 'مراجعة صغرى', 'مراجعة كبرى', 'تثبيت'];
$isValidRecitationType = in_array($apiData['recitation_type'], $validRecitationTypes);
echo ($isValidRecitationType ? "✅" : "❌") . " recitation_type: {$apiData['recitation_type']}" . PHP_EOL;

$validEvaluations = ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'];
$isValidEvaluation = in_array($apiData['evaluation'], $validEvaluations);
echo ($isValidEvaluation ? "✅" : "❌") . " evaluation: {$apiData['evaluation']}" . PHP_EOL;

$validStatuses = ['جارية', 'غير مكتملة', 'مكتملة'];
$isValidStatus = in_array($apiData['status'], $validStatuses);
echo ($isValidStatus ? "✅" : "❌") . " status: {$apiData['status']}" . PHP_EOL;

echo PHP_EOL . "=== محاكاة إنشاء الجلسة ===" . PHP_EOL;

try {
    // محاكاة نفس العملية التي تحدث في API Controller
    if (!isset($apiData['curriculum_id'])) {
        $studentProgress = App\Models\StudentProgress::where('student_id', $apiData['student_id'])
            ->where('is_active', true)
            ->first();
        
        if ($studentProgress) {
            $apiData['curriculum_id'] = $studentProgress->curriculum_id;
            echo "🔄 تم العثور على curriculum_id من StudentProgress: {$apiData['curriculum_id']}" . PHP_EOL;
        } else {
            echo "❌ لا يوجد StudentProgress نشط للطالب" . PHP_EOL;
        }
    }

    if (!isset($apiData['status'])) {
        $apiData['status'] = 'جارية';
    }

    // محاولة إنشاء الجلسة
    $session = App\Models\RecitationSession::create($apiData);
    echo "✅ تم إنشاء الجلسة بنجاح!" . PHP_EOL;
    echo "Session ID: {$session->id}" . PHP_EOL;
    echo "Session UUID: {$session->session_id}" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الجلسة: " . $e->getMessage() . PHP_EOL;
    echo "تفاصيل الخطأ: " . $e->getTraceAsString() . PHP_EOL;
}
