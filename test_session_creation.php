<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Session Creation ===" . PHP_EOL;

// Check if user ID 1 exists
$user = App\Models\User::find(1);
if ($user) {
    echo "✅ User ID 1 exists: {$user->name}" . PHP_EOL;
} else {
    echo "❌ User ID 1 does not exist" . PHP_EOL;
    
    // Create a test user
    $user = App\Models\User::create([
        'name' => 'معلم تجريبي',
        'email' => 'teacher@test.com',
        'password' => bcrypt('password123')
    ]);
    echo "✅ Created test user ID: {$user->id}" . PHP_EOL;
}

// Check if student ID 1 exists
$student = App\Models\Student::find(1);
if ($student) {
    echo "✅ Student ID 1 exists: {$student->name}" . PHP_EOL;
} else {
    echo "❌ Student ID 1 does not exist" . PHP_EOL;
}

// Check if circle ID 1 exists
$circle = App\Models\QuranCircle::find(1);
if ($circle) {
    echo "✅ Circle ID 1 exists: {$circle->name}" . PHP_EOL;
} else {
    echo "❌ Circle ID 1 does not exist" . PHP_EOL;
}

echo PHP_EOL . "=== Creating Recitation Session ===" . PHP_EOL;

try {
    $session = App\Models\RecitationSession::create([
        'student_id' => 1,
        'teacher_id' => 1,
        'quran_circle_id' => 1,
        'session_id' => 'session_' . time(),
        'start_surah_number' => 1,
        'start_verse' => 1,
        'end_surah_number' => 1,
        'end_verse' => 5,
        'recitation_type' => 'حفظ',
        'duration_minutes' => 30,
        'grade' => 8.5,
        'evaluation' => 'ممتاز',
        'teacher_notes' => 'Test session from PHP script',
        'status' => 'جارية'
    ]);
    
    echo "✅ Session created successfully!" . PHP_EOL;
    echo "Session ID: " . $session->id . PHP_EOL;
    echo "Session UUID: " . $session->session_id . PHP_EOL;
    echo "Student: " . $session->student->name . PHP_EOL;
    echo "Teacher: " . $session->teacher->name . PHP_EOL;
    echo "Status: " . $session->status . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error creating session: " . $e->getMessage() . PHP_EOL;
    echo "Error details: " . $e->getTraceAsString() . PHP_EOL;
}
