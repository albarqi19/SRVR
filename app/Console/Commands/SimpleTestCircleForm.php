<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuranCircle;
use App\Models\Teacher;
use App\Models\TeacherCircleAssignment;

class SimpleTestCircleForm extends Command
{
    protected $signature = 'test:simple-form {circle_id?}';
    protected $description = 'اختبار بسيط للمنطق الجديد';

    public function handle()
    {
        $circleId = $this->argument('circle_id');
        
        if (!$circleId) {
            return $this->testConflictValidation();
        }
        
        $circle = QuranCircle::find($circleId);
        
        if (!$circle) {
            $this->error('الحلقة غير موجودة');
            return;
        }

        $this->info("🔍 اختبار المنطق الجديد للحلقة: {$circle->name}");
        
        // نفس المنطق المستخدم في CircleGroupsRelationManager المحدث
        $teachers = collect();
        
        // المعلمون النشطون في هذه الحلقة
        $activeTeachers = $circle->activeTeachers()->get();
        $this->line("📊 المعلمون النشطون: {$activeTeachers->count()}");
        
        foreach ($activeTeachers as $teacher) {
            $teachers->put($teacher->id, $teacher->name);
            $this->line("   ✅ {$teacher->name} (ID: {$teacher->id})");
        }
        
        // معلمو المسجد (إضافيون)
        if ($circle->mosque_id) {
            $mosqueTeachers = Teacher::where('mosque_id', $circle->mosque_id)->get();
            $this->line("📊 معلمو المسجد: {$mosqueTeachers->count()}");
            
            foreach ($mosqueTeachers as $teacher) {
                if (!$teachers->has($teacher->id)) {
                    $teachers->put($teacher->id, $teacher->name);
                    $this->line("   ➕ {$teacher->name} (ID: {$teacher->id}) - من المسجد");
                }
            }
        }
        
        // النتيجة النهائية
        $finalArray = $teachers->toArray();
        $this->info("🎯 النتيجة النهائية:");
        $this->line(json_encode($finalArray, JSON_UNESCAPED_UNICODE));
        
        if (empty($finalArray)) {
            $this->error("❌ لا يوجد معلمون متاحون!");
        } else {
            $this->info("✅ يوجد {$teachers->count()} معلم متاح");
        }
    }
    
    private function testConflictValidation()
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
        
        $this->newLine();
        $this->info('🔍 تحليل مفصل للتعارضات:');
        
        // تحليل مفصل لحلقة خمسون
        $this->line('🔍 لماذا لا يظهر تعارض في حلقة خمسون؟');
        
        $conflicts = TeacherCircleAssignment::where('teacher_id', 1)
                                          ->where('is_active', true)
                                          ->where('quran_circle_id', '!=', 3) // استبعاد خمسون
                                          ->whereHas('circle', function($q) {
                                              $q->where('time_period', 'عصر');
                                          })
                                          ->with('circle')
                                          ->get();
        
        $this->line("عدد التكليفات المتعارضة مع حلقة خمسون: {$conflicts->count()}");
        foreach ($conflicts as $conflict) {
            $this->line("   - {$conflict->circle->name} ({$conflict->circle->time_period}) - بداية: {$conflict->start_date} - نهاية: " . ($conflict->end_date ?? 'مفتوح'));
        }
        
        return 0;
    }
}
