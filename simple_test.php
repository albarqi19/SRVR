<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "Testing Teacher-Student Relationships\n";
echo "=====================================\n";

try {
    // Get first teacher
    $teacher = Teacher::first();
    
    if (!$teacher) {
        echo "No teachers found in database\n";
        exit;
    }
    
    echo "Teacher Found:\n";
    echo "- ID: {$teacher->id}\n";
    echo "- Name: {$teacher->name}\n";
    echo "- QuranCircle ID: " . ($teacher->quran_circle_id ?? 'NULL') . "\n";
    echo "- Mosque ID: " . ($teacher->mosque_id ?? 'NULL') . "\n\n";
    
    // Check students via QuranCircle
    if ($teacher->quran_circle_id) {
        $studentsInCircle = Student::where('quran_circle_id', $teacher->quran_circle_id)->count();
        echo "Students in same QuranCircle: {$studentsInCircle}\n";
    }
    
    // Check students via Mosque
    if ($teacher->mosque_id) {
        $studentsInMosque = Student::where('mosque_id', $teacher->mosque_id)->count();
        echo "Students in same Mosque: {$studentsInMosque}\n";
    }
    
    echo "\nDatabase Counts:\n";
    echo "- Total Teachers: " . Teacher::count() . "\n";
    echo "- Total Students: " . Student::count() . "\n";
    echo "- Total QuranCircles: " . QuranCircle::count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
