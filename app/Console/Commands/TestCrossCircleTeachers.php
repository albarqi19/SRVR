<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\TeacherCircleAssignment;
use App\Models\Mosque;

class TestCrossCircleTeachers extends Command
{
    protected $signature = 'test:cross-circle-teachers';
    protected $description = 'اختبار عرض المعلمين من حلقات مختلفة';

    public function handle()
    {
        $this->info('🔍 اختبار عرض المعلمين من حلقات مختلفة');
        $this->newLine();

        // إنشاء بيانات تجريبية
        $this->createTestData();
        
        // اختبار منطق عرض المعلمين
        $this->testTeacherDisplay();
        
        $this->info('✅ انتهى الاختبار');
        return 0;
    }

    private function createTestData()
    {
        $this->info('📊 استخدام البيانات الموجودة للاختبار...');
        
        // التحقق من وجود بيانات كافية
        $teachersCount = Teacher::count();
        $circlesCount = QuranCircle::count();
        $assignmentsCount = TeacherCircleAssignment::where('is_active', true)->count();
        
        $this->info("✅ البيانات الموجودة:");
        $this->line("   - المعلمين: {$teachersCount}");
        $this->line("   - الحلقات القرآنية: {$circlesCount}");
        $this->line("   - التكليفات النشطة: {$assignmentsCount}");
        $this->newLine();
        
        // إنشاء تكليف إضافي للاختبار إذا لزم الأمر
        if ($assignmentsCount < 2 && $teachersCount > 0 && $circlesCount > 1) {
            $teacher = Teacher::first();
            $circles = QuranCircle::take(2)->get();
            
            if ($circles->count() >= 2) {
                foreach ($circles as $circle) {
                    TeacherCircleAssignment::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'quran_circle_id' => $circle->id
                    ], [
                        'is_active' => true,
                        'start_date' => now(),
                        'notes' => 'تكليف تجريبي للاختبار'
                    ]);
                }
                $this->info('✅ تم إنشاء تكليفات إضافية للاختبار');
            }
        }
    }

    private function testTeacherDisplay()
    {
        $this->info('🧪 اختبار عرض المعلمين لكل حلقة:');
        $this->newLine();

        $circles = QuranCircle::with(['mosque', 'activeTeachers'])->get();

        foreach ($circles as $circle) {
            $this->info("📋 حلقة: {$circle->name} (مسجد: {$circle->mosque->name})");
            $this->line("   ⏰ الوقت: {$circle->time_period}");
            
            // عرض المعلمين المكلفين حالياً
            $this->line("   👨‍🏫 المعلمين المكلفين حالياً:");
            foreach ($circle->activeTeachers as $teacher) {
                $this->line("      - {$teacher->name}");
            }

            // محاكاة منطق عرض المعلمين في الحلقات الفرعية (المنطق الجديد)
            $this->line("   📝 المعلمين المتاحين للحلقات الفرعية:");
            
            $options = [];
            
            // 1. المعلمين المكلفين في هذه الحلقة
            foreach ($circle->activeTeachers as $teacher) {
                $options[$teacher->id] = $teacher->name . ' (مكلف في هذه الحلقة)';
            }
            
            // 2. المعلمين المكلفين في حلقات أخرى
            $allAssignedTeachers = Teacher::whereHas('circleAssignments', function ($query) use ($circle) {
                $query->where('is_active', true)
                      ->where('quran_circle_id', '!=', $circle->id);
            })->with(['circleAssignments.circle'])->get();
            
            foreach ($allAssignedTeachers as $teacher) {
                if (!isset($options[$teacher->id])) {
                    // التحقق من تعارض الأوقات
                    $hasConflict = false;
                    foreach ($teacher->circleAssignments as $assignment) {
                        if ($assignment->is_active && $assignment->circle) {
                            if ($assignment->circle->time_period === $circle->time_period) {
                                $hasConflict = true;
                                break;
                            }
                        }
                    }
                    
                    if ($hasConflict) {
                        $options[$teacher->id] = $teacher->name . ' (تعارض في الوقت ⚠️)';
                    } else {
                        $options[$teacher->id] = $teacher->name . ' (مكلف في حلقة أخرى)';
                    }
                }
            }
            
            // 3. معلمي نفس المسجد غير المكلفين
            if ($circle->mosque_id) {
                $mosqueTeachers = Teacher::where('mosque_id', $circle->mosque_id)
                    ->whereDoesntHave('circleAssignments', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->get();
                
                foreach ($mosqueTeachers as $teacher) {
                    if (!isset($options[$teacher->id])) {
                        $options[$teacher->id] = $teacher->name . ' (من نفس المسجد)';
                    }
                }
            }

            foreach ($options as $id => $name) {
                $this->line("      - {$name}");
            }
            
            $this->newLine();
        }
    }
}
