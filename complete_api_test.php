<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== API vs Direct Creation Test ===" . PHP_EOL;

// Test 1: Direct Model Creation (يعمل)
echo "--- Test 1: Direct Model Creation ---" . PHP_EOL;
try {
    $session = App\Models\RecitationSession::create([
        'student_id' => 1,
        'teacher_id' => 1,
        'quran_circle_id' => 1,
        'session_id' => 'direct_' . time(),
        'start_surah_number' => 1,
        'start_verse' => 1,
        'end_surah_number' => 1,
        'end_verse' => 5,
        'recitation_type' => 'حفظ',
        'duration_minutes' => 30,
        'grade' => 8.5,
        'evaluation' => 'جيد جداً',
        'teacher_notes' => 'Direct creation test',
        'status' => 'مكتملة'
    ]);
    
    echo "✅ Direct creation SUCCESS - Session ID: {$session->id}" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Direct creation FAILED: {$e->getMessage()}" . PHP_EOL;
}

// Test 2: Validation Rules
echo "--- Test 2: Validation Rules ---" . PHP_EOL;

$testData = [
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
    'teacher_notes' => 'Test validation',
    'status' => 'مكتملة'
];

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
    'evaluation' => 'required|in:ممتاز,جيد جداً,جيد جدا,جيد,مقبول,ضعيف',
    'teacher_notes' => 'nullable|string|max:1000',
    'curriculum_id' => 'nullable|exists:curriculums,id',
    'status' => 'nullable|in:جارية,غير مكتملة,مكتملة'
];

$validator = Illuminate\Support\Facades\Validator::make($testData, $rules);

if ($validator->fails()) {
    echo "❌ Validation FAILED:" . PHP_EOL;
    foreach ($validator->errors()->all() as $error) {
        echo "  - {$error}" . PHP_EOL;
    }
} else {
    echo "✅ Validation PASSED" . PHP_EOL;
}

// Test 3: Simulate API Request
echo "--- Test 3: Simulate API Request ---" . PHP_EOL;

try {
    // Create Request with data
    $request = Illuminate\Http\Request::create('/api/recitation/sessions', 'POST', $testData);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('Accept', 'application/json');
    
    echo "Request data count: " . count($request->all()) . PHP_EOL;
    echo "Request all(): " . json_encode($request->all()) . PHP_EOL;
    
    // Now test the exact logic from Controller
    $requestData = $request->all();
    if (empty($requestData) && $request->isJson()) {
        $requestData = json_decode($request->getContent(), true) ?? [];
    }
    
    echo "Final requestData count: " . count($requestData) . PHP_EOL;
    
    // Test validation with the same data Controller would use
    $validator2 = Illuminate\Support\Facades\Validator::make($requestData, $rules);
    
    if ($validator2->fails()) {
        echo "❌ Controller-style validation FAILED:" . PHP_EOL;
        foreach ($validator2->errors()->all() as $error) {
            echo "  - {$error}" . PHP_EOL;
        }
    } else {
        echo "✅ Controller-style validation PASSED" . PHP_EOL;
        
        // Try to create session with Controller logic
        $sessionData = $requestData;
        $sessionData['session_id'] = 'controller_' . time() . '_' . uniqid();
        
        if (!isset($sessionData['status'])) {
            $sessionData['status'] = 'جارية';
        }
        
        $session = App\Models\RecitationSession::create($sessionData);
        echo "✅ Controller-style creation SUCCESS - Session ID: {$session->id}" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ API simulation FAILED: {$e->getMessage()}" . PHP_EOL;
    echo "File: {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
}

echo "=== Test Completed ===" . PHP_EOL;
