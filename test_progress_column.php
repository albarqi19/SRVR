<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentCurriculum;

try {
    echo "Testing ProgressColumn functionality...\n";
    echo "=====================================\n";
    
    // Check if we have student_curricula records
    $count = DB::table('student_curricula')->count();
    echo "Total student_curricula records: $count\n";
    
    if ($count > 0) {
        // Update some sample records with completion percentages
        echo "\nUpdating sample records with completion percentages...\n";
        
        $records = DB::table('student_curricula')->limit(3)->get();
        foreach ($records as $index => $record) {
            $percentage = ($index + 1) * 25; // 25%, 50%, 75%
            DB::table('student_curricula')
                ->where('id', $record->id)
                ->update(['completion_percentage' => $percentage]);
            echo "Updated record ID {$record->id} with {$percentage}%\n";
        }
        
        // Verify the updates
        echo "\nVerifying updates:\n";
        $updatedRecords = DB::table('student_curricula')
            ->whereNotNull('completion_percentage')
            ->where('completion_percentage', '>', 0)
            ->get(['id', 'completion_percentage']);
            
        foreach ($updatedRecords as $record) {
            echo "Record ID {$record->id}: {$record->completion_percentage}%\n";
        }
        
    } else {
        echo "No student_curricula records found.\n";
        echo "Creating a sample record...\n";
        
        // Check if we have students and curricula
        $studentCount = DB::table('students')->count();
        $curriculumCount = DB::table('curricula')->count();
        
        if ($studentCount > 0 && $curriculumCount > 0) {
            $student = DB::table('students')->first();
            $curriculum = DB::table('curricula')->first();
            
            DB::table('student_curricula')->insert([
                'student_id' => $student->id,
                'curriculum_id' => $curriculum->id,
                'status' => 'قيد التنفيذ',
                'start_date' => now(),
                'completion_percentage' => 35.5,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "Sample record created with 35.5% completion.\n";
        } else {
            echo "No students or curricula found to create sample data.\n";
        }
    }
    
    echo "\n✓ Progress column functionality test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
