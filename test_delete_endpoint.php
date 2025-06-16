<?php

// Simple test to check if we can create and delete a recitation session
require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Delete Endpoint ===\n";

try {
    // Get a teacher and student
    $teacher = Teacher::first();
    $student = Student::first();
    
    if (!$teacher || !$student) {
        echo "Error: No teacher or student found in database\n";
        exit(1);
    }
    
    echo "Teacher found: ID {$teacher->id}, Name: {$teacher->name}\n";
    echo "Student found: ID {$student->id}, Name: {$student->name}\n";
    
    // Create a test recitation session
    $session = RecitationSession::create([
        'student_id' => $student->id,
        'teacher_id' => $teacher->id,
        'session_date' => now(),
        'surah_id' => 1,
        'from_verse' => 1,
        'to_verse' => 10,
        'session_type' => 'memorization',
        'evaluation' => 'excellent',
        'notes' => 'Test session for deletion'
    ]);
    
    echo "Created test session with ID: {$session->id}\n";
    
    // Verify it was created
    $createdSession = RecitationSession::find($session->id);
    if ($createdSession) {
        echo "✅ Session created successfully\n";
        
        // Now test deletion
        $deleted = $createdSession->delete();
        
        if ($deleted) {
            echo "✅ Session deleted successfully\n";
            
            // Verify it was deleted
            $deletedSession = RecitationSession::find($session->id);
            if ($deletedSession === null) {
                echo "✅ Session confirmed deleted from database\n";
                echo "🎉 Delete endpoint should work correctly!\n";
            } else {
                echo "❌ Session still exists in database after deletion\n";
            }
        } else {
            echo "❌ Failed to delete session\n";
        }
    } else {
        echo "❌ Failed to create session\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test completed ===\n";
