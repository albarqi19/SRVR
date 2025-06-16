<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherCircleAssignment;

class TestConflictValidation extends Command
{
    protected $signature = 'test:conflict-validation';
    protected $description = 'اختبار دالة فحص تعارض الأوقات';

    public function handle()
    {
        $this->info('🔍 اختبار دالة فحص تعارض الأوقات');
        $this->newLine();

        // اختبار المعلم أحمد10 (ID: 1) مع الحلقات المختلفة
        $teacherId = 1;
        
        $this->info('📊 اختبار تعارضات المعلم أحمد10:');
        
        // اختبار تكليف في حلقة الضاحية (عصر) - ID: 1
        $hasConflict1 = TeacherCircleAssignment::hasTimeConflict($teacherId, 1, now());
        $this->line("   - حلقة الضاحية (عصر): " . ($hasConflict1 ? '❌ تعارض' : '✅ لا تعارض'));
        
        // اختبار تكليف في حلقة الفردوس (مغرب) - ID: 2
        $hasConflict2 = TeacherCircleAssignment::hasTimeConflict($teacherId, 2, now());
        $this->line("   - حلقة الفردوس (مغرب): " . ($hasConflict2 ? '❌ تعارض' : '✅ لا تعارض'));
        
        // اختبار تكليف في حلقة خمسون (عصر) - ID: 3
        $hasConflict3 = TeacherCircleAssignment::hasTimeConflict($teacherId, 3, now());
        $this->line("   - حلقة خمسون (عصر): " . ($hasConflict3 ? '❌ تعارض' : '✅ لا تعارض'));
        
        $this->newLine();
        
        // عرض التكليفات الحالية
        $this->info('📋 التكليفات الحالية:');
        $assignments = TeacherCircleAssignment::with(['teacher', 'circle'])
            ->where('is_active', true)
            ->get();
            
        foreach ($assignments as $assignment) {
            $this->line("   - {$assignment->teacher->name} → {$assignment->circle->name} ({$assignment->circle->time_period})");
        }
        
        $this->newLine();
        $this->info('💡 التفسير:');
        $this->line('   - يجب أن تكون هناك تعارضات في حلقات العصر (الضاحية وخمسون)');
        $this->line('   - يجب ألا يكون هناك تعارض في حلقة المغرب (الفردوس)');
        
        return 0;
    }
}
