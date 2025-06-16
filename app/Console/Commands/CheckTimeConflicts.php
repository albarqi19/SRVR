<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherCircleAssignment;

class CheckTimeConflicts extends Command
{
    protected $signature = 'check:time-conflicts';
    protected $description = 'فحص تعارضات الأوقات في التكليفات';

    public function handle()
    {
        $this->info('🔍 فحص تعارضات الأوقات:');
        $this->newLine();

        $assignments = TeacherCircleAssignment::with(['teacher', 'circle'])
            ->where('is_active', true)
            ->get();

        $this->info('📊 التكليفات الحالية:');
        foreach ($assignments as $assignment) {
            $this->line("   - {$assignment->teacher->name} → {$assignment->circle->name} ({$assignment->circle->time_period})");
        }
        $this->newLine();

        // فحص التعارضات
        $conflicts = [];
        foreach ($assignments as $assignment1) {
            foreach ($assignments as $assignment2) {
                if ($assignment1->id !== $assignment2->id && 
                    $assignment1->teacher_id === $assignment2->teacher_id &&
                    $assignment1->circle->time_period === $assignment2->circle->time_period) {
                    
                    $key = $assignment1->teacher->name . '_' . $assignment1->circle->time_period;
                    if (!isset($conflicts[$key])) {
                        $conflicts[$key] = [
                            'teacher' => $assignment1->teacher->name,
                            'time' => $assignment1->circle->time_period,
                            'circles' => []
                        ];
                    }
                    $conflicts[$key]['circles'][] = $assignment1->circle->name;
                    $conflicts[$key]['circles'][] = $assignment2->circle->name;
                }
            }
        }

        if (empty($conflicts)) {
            $this->info('✅ لا توجد تعارضات في الأوقات');
        } else {
            $this->error('⚠️ تم العثور على تعارضات:');
            foreach ($conflicts as $conflict) {
                $circles = array_unique($conflict['circles']);
                $this->line("   - المعلم: {$conflict['teacher']}");
                $this->line("   - الوقت: {$conflict['time']}");
                $this->line("   - الحلقات: " . implode('، ', $circles));
                $this->newLine();
            }
        }

        return 0;
    }
}
