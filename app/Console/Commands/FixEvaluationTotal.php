<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherEvaluation;

class FixEvaluationTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:evaluation-total';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix total score calculation for teacher evaluations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 إصلاح حساب النتيجة الإجمالية للتقييمات...');
        
        $evaluations = TeacherEvaluation::all();
        $fixed = 0;
        
        foreach ($evaluations as $evaluation) {
            $correctTotal = $evaluation->performance_evaluation + 
                           $evaluation->attendance_evaluation + 
                           $evaluation->student_interaction_evaluation + 
                           $evaluation->attitude_cooperation_evaluation + 
                           $evaluation->memorization_evaluation + 
                           $evaluation->general_evaluation;
            
            if ($evaluation->total_score != $correctTotal) {
                $evaluation->total_score = $correctTotal;
                $evaluation->save();
                $fixed++;
                
                $this->line("✅ تم إصلاح تقييم رقم {$evaluation->id}: {$correctTotal}/120");
            }
        }
        
        $this->info("🎉 تم إصلاح {$fixed} تقييم من أصل {$evaluations->count()}");
        
        // عرض إحصائيات التقييمات بعد الإصلاح
        $this->info('');
        $this->info('📊 إحصائيات التقييمات بعد الإصلاح:');
        
        foreach ($evaluations->fresh() as $eval) {
            $this->line("ID {$eval->id}: {$eval->total_score}/120 ({$eval->percentage}%) - {$eval->status}");
        }
    }
}
