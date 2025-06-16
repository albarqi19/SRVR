<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "=== Testing Validation Rules Directly ===" . PHP_EOL;

// Test data that should pass validation
$testData = [
    'student_id' => 1,
    'teacher_id' => 1,
    'quran_circle_id' => 1,
    'start_surah_number' => 1,
    'start_verse' => 1,
    'end_surah_number' => 1,
    'end_verse' => 5,
    'recitation_type' => 'حفظ',
    'duration_minutes' => 30,
    'grade' => 8.5,
    'evaluation' => 'ممتاز',
    'teacher_notes' => 'Test session',
    'status' => 'جارية'
];

// The exact validation rules from the controller
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

echo "Testing validation with data:" . PHP_EOL;
print_r($testData);

$validator = Validator::make($testData, $rules);

if ($validator->fails()) {
    echo "❌ Validation FAILED!" . PHP_EOL;
    echo "Errors:" . PHP_EOL;
    foreach ($validator->errors()->all() as $error) {
        echo "  - $error" . PHP_EOL;
    }
    
    echo PHP_EOL . "Detailed errors:" . PHP_EOL;
    foreach ($validator->errors()->messages() as $field => $messages) {
        echo "Field '$field':" . PHP_EOL;
        foreach ($messages as $message) {
            echo "  - $message" . PHP_EOL;
        }
    }
} else {
    echo "✅ Validation PASSED!" . PHP_EOL;
}

echo PHP_EOL . "=== Testing individual problematic values ===" . PHP_EOL;

// Test each problematic value individually
$problemValues = [
    'recitation_type' => ['حفظ', 'مراجعة صغرى', 'مراجعة كبرى', 'تثبيت'],
    'evaluation' => ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'],
    'status' => ['جارية', 'غير مكتملة', 'مكتملة']
];

foreach ($problemValues as $field => $values) {
    echo "Testing field '$field':" . PHP_EOL;
    $rule = $rules[$field];
    
    foreach ($values as $value) {
        $singleValidator = Validator::make([$field => $value], [$field => $rule]);
        $status = $singleValidator->fails() ? "❌ FAIL" : "✅ PASS";
        echo "  - Value '$value': $status" . PHP_EOL;
        
        if ($singleValidator->fails()) {
            echo "    Errors: " . implode(', ', $singleValidator->errors()->all()) . PHP_EOL;
        }
    }
    echo PHP_EOL;
}
