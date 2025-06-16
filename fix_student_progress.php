<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Student Progress Issue ===" . PHP_EOL;

// Check if we have any curriculum
$curriculum = App\Models\Curriculum::first();
if (!$curriculum) {
    // Create a basic curriculum
    $curriculum = App\Models\Curriculum::create([
        'name' => 'المنهج الأساسي',
        'description' => 'منهج تعليم القرآن الكريم',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✅ Created curriculum ID: {$curriculum->id}" . PHP_EOL;
} else {
    echo "✅ Found existing curriculum ID: {$curriculum->id}" . PHP_EOL;
}

// Check if student 1 has active progress
$existingProgress = App\Models\StudentProgress::where('student_id', 1)
    ->where('is_active', true)
    ->first();

if ($existingProgress) {
    echo "✅ Student ID 1 already has active progress" . PHP_EOL;
    echo "   Curriculum ID: {$existingProgress->curriculum_id}" . PHP_EOL;
} else {
    // Create student progress record
    $studentProgress = App\Models\StudentProgress::create([
        'student_id' => 1,
        'curriculum_id' => $curriculum->id,
        'current_surah' => 1,
        'current_verse' => 1,
        'total_verses_memorized' => 0,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Created student progress record" . PHP_EOL;
    echo "   Student ID: {$studentProgress->student_id}" . PHP_EOL;
    echo "   Curriculum ID: {$studentProgress->curriculum_id}" . PHP_EOL;
    echo "   Is Active: " . ($studentProgress->is_active ? 'Yes' : 'No') . PHP_EOL;
}

echo PHP_EOL . "=== Verification ===" . PHP_EOL;

// Verify the fix
$activeProgress = App\Models\StudentProgress::where('student_id', 1)
    ->where('is_active', true)
    ->first();

if ($activeProgress) {
    echo "✅ Student ID 1 now has active curriculum: {$activeProgress->curriculum_id}" . PHP_EOL;
    echo "✅ API should now work correctly" . PHP_EOL;
} else {
    echo "❌ Still no active progress found" . PHP_EOL;
}
