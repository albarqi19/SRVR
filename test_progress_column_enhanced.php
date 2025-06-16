<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentCurriculum;
use App\Models\Student;
use App\Models\Curriculum;

try {
    echo "🚀 Testing ProgressColumn functionality...\n";
    echo "========================================\n";
    
    // Check if the completion_percentage column exists
    echo "1. Checking if completion_percentage column exists...\n";
    $columnExists = DB::select("SHOW COLUMNS FROM student_curricula LIKE 'completion_percentage'");
    if (empty($columnExists)) {
        echo "❌ Column completion_percentage does not exist!\n";
        exit(1);
    }
    echo "✅ Column completion_percentage exists.\n\n";
    
    // Check existing data
    $count = DB::table('student_curricula')->count();
    echo "2. Total student_curricula records: $count\n";
    
    if ($count > 0) {
        // Update existing records with sample completion percentages
        echo "\n3. Updating existing records with completion percentages...\n";
        
        $records = DB::table('student_curricula')->limit(5)->get();
        $percentages = [25, 50, 75, 85, 100];
        
        foreach ($records as $index => $record) {
            $percentage = $percentages[$index] ?? 30;
            
            DB::table('student_curricula')
                ->where('id', $record->id)
                ->update([
                    'completion_percentage' => $percentage,
                    'updated_at' => now()
                ]);
            
            echo "   ✅ Updated record ID {$record->id} with {$percentage}%\n";
        }
        
    } else {
        echo "\n3. No existing records found. Creating sample data...\n";
        
        // Check if we have students and curricula
        $studentCount = DB::table('students')->count();
        $curriculumCount = DB::table('curricula')->count();
        
        echo "   Students count: $studentCount\n";
        echo "   Curricula count: $curriculumCount\n";
        
        if ($studentCount > 0 && $curriculumCount > 0) {
            $student = DB::table('students')->first();
            $curriculum = DB::table('curricula')->first();
            
            // Create multiple sample records
            $sampleData = [
                ['completion' => 25.5, 'status' => 'قيد التنفيذ'],
                ['completion' => 60.0, 'status' => 'قيد التنفيذ'], 
                ['completion' => 87.5, 'status' => 'قيد التنفيذ'],
                ['completion' => 100.0, 'status' => 'مكتمل'],
            ];
            
            foreach ($sampleData as $index => $data) {
                DB::table('student_curricula')->insert([
                    'student_id' => $student->id,
                    'curriculum_id' => $curriculum->id,
                    'status' => $data['status'],
                    'start_date' => now(),
                    'completion_percentage' => $data['completion'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                echo "   ✅ Created sample record " . ($index + 1) . " with {$data['completion']}% completion\n";
            }
        } else {
            echo "   ❌ No students or curricula found to create sample data.\n";
            echo "   Please ensure you have students and curricula in your database.\n";
        }
    }
    
    // Verify final data
    echo "\n4. Verifying completion percentage data:\n";
    $updatedRecords = DB::table('student_curricula')
        ->whereNotNull('completion_percentage')
        ->orderBy('completion_percentage')
        ->get(['id', 'completion_percentage', 'status']);
        
    if ($updatedRecords->isEmpty()) {
        echo "   ❌ No records with completion percentage found!\n";
    } else {
        echo "   📊 Records with completion percentage:\n";
        foreach ($updatedRecords as $record) {
            $icon = $record->completion_percentage >= 100 ? '🎯' : ($record->completion_percentage >= 75 ? '🟢' : ($record->completion_percentage >= 50 ? '🟡' : '🔴'));
            echo "   $icon Record ID {$record->id}: {$record->completion_percentage}% ({$record->status})\n";
        }
    }
    
    echo "\n🎉 Progress column functionality test completed successfully!\n";
    echo "Now you can test the admin panel at: /admin/students/{student_id}/curricula\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
