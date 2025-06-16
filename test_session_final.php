<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== GARB Recitation Session Test ===\n";

// 1. فحص البيانات
echo "Students: " . App\Models\Student::count() . "\n";
echo "Users: " . App\Models\User::count() . "\n";
echo "Circles: " . App\Models\QuranCircle::count() . "\n\n";

// 2. محاولة إنشاء جلسة
echo "Creating session...\n";
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
        'teacher_notes' => 'test session'
    ]);
    
    echo "✅ SUCCESS!\n";
    echo "Session ID: " . $session->session_id . "\n";
    echo "Database ID: " . $session->id . "\n";
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// 3. عرض جميع الجلسات
echo "\nAll sessions:\n";
$sessions = App\Models\RecitationSession::all();
echo "Total sessions: " . $sessions->count() . "\n";
foreach($sessions as $s) {
    echo "- ID: {$s->session_id} | Grade: {$s->grade} | Type: {$s->recitation_type}\n";
}

// 4. اختبار Controller
echo "\n=== Testing Controller ===\n";
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
        'teacher_notes' => 'controller test'
    ]);
    
    $controller = new App\Http\Controllers\Api\RecitationSessionController();
    $result = $controller->store($request);
    
    echo "Controller Status: " . $result->getStatusCode() . "\n";
    echo "Controller Response:\n" . $result->getContent() . "\n";
    
} catch(Exception $e) {
    echo "❌ Controller ERROR: " . $e->getMessage() . "\n";
}
