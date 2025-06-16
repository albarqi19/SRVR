<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;
use App\Models\CurriculumPlan;

echo "Checking and creating sample data for student_curriculum_progress...\n";
echo "==================================================================\n";

try {
    // Check existing data
    $progressCount = StudentCurriculumProgress::count();
    echo "Current records in student_curriculum_progress: {$progressCount}\n\n";
    
    if ($progressCount === 0) {
        echo "Creating sample data...\n";
        
        // Get a student curriculum record
        $studentCurriculum = StudentCurriculum::first();
        if (!$studentCurriculum) {
            echo "❌ No student curriculum records found. Please create a student curriculum first.\n";
            exit;
        }
        
        // Get curriculum plans
        $curriculumPlans = CurriculumPlan::where('curriculum_id', $studentCurriculum->curriculum_id)->get();
        if ($curriculumPlans->count() === 0) {
            echo "❌ No curriculum plans found for this curriculum. Creating sample plans...\n";
            
            // Create sample curriculum plans
            for ($i = 1; $i <= 5; $i++) {                CurriculumPlan::create([
                    'curriculum_id' => $studentCurriculum->curriculum_id,
                    'name' => "خطة المنهج {$i}",
                    'content' => "وصف خطة المنهج رقم {$i}",
                    'is_active' => true,
                ]);
            }
            $curriculumPlans = CurriculumPlan::where('curriculum_id', $studentCurriculum->curriculum_id)->get();
        }
        
        // Create progress records for each plan
        foreach ($curriculumPlans as $plan) {
            StudentCurriculumProgress::create([
                'student_curriculum_id' => $studentCurriculum->id,
                'curriculum_plan_id' => $plan->id,
                'start_date' => now()->subDays(rand(1, 30)),
                'completion_date' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                'status' => rand(0, 1) ? 'مكتمل' : 'قيد التنفيذ',
                'completion_percentage' => rand(0, 100),
                'teacher_notes' => "ملاحظات المعلم حول التقدم في {$plan->name}",
            ]);
        }
        
        echo "✓ Created progress records for " . $curriculumPlans->count() . " curriculum plans\n";
    }
      // Display current data
    $progressRecords = StudentCurriculumProgress::with(['studentCurriculum.student', 'plan'])
        ->limit(10)
        ->get();
    
    echo "\nCurrent progress records:\n";
    echo "------------------------\n";    foreach ($progressRecords as $progress) {
        echo "ID: {$progress->id} | ";
        echo "Student: " . ($progress->studentCurriculum->student->name ?? 'غير محدد') . " | ";
        echo "Plan: " . ($progress->plan->name ?? 'غير محدد') . " | ";
        echo "Progress: {$progress->completion_percentage}% | ";
        echo "Status: {$progress->status}\n";
    }
    
    echo "\n✓ Sample data is ready for testing!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
