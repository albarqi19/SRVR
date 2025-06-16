<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Teacher-Student Relationships ===\n";

// Test Teacher model
$teacher = App\Models\Teacher::first();
if ($teacher) {
    echo "Teacher ID: " . $teacher->id . "\n";
    echo "Teacher Name: " . $teacher->name . "\n";
    echo "QuranCircle ID: " . ($teacher->quran_circle_id ?? 'NULL') . "\n";
    echo "Mosque ID: " . ($teacher->mosque_id ?? 'NULL') . "\n";
    
    // Test quranCircle relationship
    if ($teacher->quranCircle) {
        echo "Circle Name: " . $teacher->quranCircle->name . "\n";
        echo "Students in Circle: " . $teacher->quranCircle->students()->count() . "\n";
    } else {
        echo "No QuranCircle assigned to this teacher\n";
    }
    
    // Test mosque relationship
    if ($teacher->mosque) {
        echo "Mosque Name: " . $teacher->mosque->name . "\n";
    } else {
        echo "No Mosque assigned to this teacher\n";
    }
    
} else {
    echo "No teachers found\n";
}

echo "\n=== Testing Student Counts ===\n";
echo "Total Students: " . App\Models\Student::count() . "\n";
echo "Total QuranCircles: " . App\Models\QuranCircle::count() . "\n";

// Test how to get students for a teacher
echo "\n=== Possible ways to get students for teacher ===\n";

if ($teacher && $teacher->quran_circle_id) {
    $studentsViaCircle = App\Models\Student::where('quran_circle_id', $teacher->quran_circle_id)->count();
    echo "Students via QuranCircle: " . $studentsViaCircle . "\n";
}

if ($teacher && $teacher->mosque_id) {
    $studentsViaMosque = App\Models\Student::where('mosque_id', $teacher->mosque_id)->count();
    echo "Students via Mosque: " . $studentsViaMosque . "\n";
}
