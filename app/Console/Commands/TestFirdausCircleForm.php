<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\TeacherCircleAssignment;

class TestFirdausCircleForm extends Command
{
    protected $signature = 'test:firdaus-form';
    protected $description = 'اختبار محدد لحلقة الفردوس ومنطق عرض المعلمين';

    public function handle()
    {
        $this->info('🔍 اختبار محدد لحلقة الفردوس');
        $this->newLine();

        // البحث عن حلقة الفردوس
        $firdausCircle = QuranCircle::where('name', 'الفردوس')->first();
        
        if (!$firdausCircle) {
            $this->error('❌ لم يتم العثور على حلقة الفردوس');
            return 1;
        }

        $this->info("📋 حلقة: {$firdausCircle->name}");
        $this->line("   🏛️ المسجد: {$firdausCircle->mosque->name}");
        $this->line("   ⏰ الوقت: {$firdausCircle->time_period}");
        $this->newLine();

        // التحقق من المعلمين المكلفين
        $this->info('👨‍🏫 المعلمين المكلفين في هذه الحلقة:');
        $activeTeachers = $firdausCircle->activeTeachers;
        
        if ($activeTeachers->isEmpty()) {
            $this->warn('⚠️ لا يوجد معلمين مكلفين في هذه الحلقة');
        } else {
            foreach ($activeTeachers as $teacher) {
                $this->line("   ✅ {$teacher->name} (ID: {$teacher->id})");
            }
        }
        $this->newLine();

        // محاكاة منطق CircleGroupsRelationManager
        $this->info('🎯 محاكاة منطق عرض المعلمين في نموذج إضافة الحلقة الفرعية:');
        $this->newLine();

        $options = [];
        
        // 1. المعلمين المكلفين في هذه الحلقة (أولوية عالية)
        $this->info('1️⃣ المعلمين المكلفين في هذه الحلقة:');
        $currentCircleTeachers = $firdausCircle->activeTeachers;
        foreach ($currentCircleTeachers as $teacher) {
            $options[$teacher->id] = $teacher->name . ' (مكلف في هذه الحلقة)';
            $this->line("   ✅ {$teacher->name} → سيظهر في القائمة");
        }
        
        if ($currentCircleTeachers->isEmpty()) {
            $this->warn('   ⚠️ لا يوجد معلمين مكلفين في هذه الحلقة');
        }
        $this->newLine();

        // 2. المعلمين المكلفين في حلقات أخرى
        $this->info('2️⃣ المعلمين المكلفين في حلقات أخرى:');
        $allAssignedTeachers = Teacher::whereHas('circleAssignments', function ($query) use ($firdausCircle) {
            $query->where('is_active', true)
                  ->where('quran_circle_id', '!=', $firdausCircle->id);
        })->with(['circleAssignments.circle'])->get();
        
        if ($allAssignedTeachers->isEmpty()) {
            $this->warn('   ⚠️ لا يوجد معلمين مكلفين في حلقات أخرى');
        } else {
            foreach ($allAssignedTeachers as $teacher) {
                if (!isset($options[$teacher->id])) {
                    // التحقق من تعارض الأوقات
                    $hasConflict = false;
                    $conflictCircles = [];
                    foreach ($teacher->circleAssignments as $assignment) {
                        if ($assignment->is_active && $assignment->circle) {
                            $this->line("   🔍 المعلم {$teacher->name} مكلف في: {$assignment->circle->name} (وقت: {$assignment->circle->time_period})");
                            if ($assignment->circle->time_period === $firdausCircle->time_period) {
                                $hasConflict = true;
                                $conflictCircles[] = $assignment->circle->name;
                            }
                        }
                    }
                    
                    if ($hasConflict) {
                        $options[$teacher->id] = $teacher->name . ' (تعارض في الوقت ⚠️)';
                        $this->line("   ⚠️ {$teacher->name} → تعارض مع: " . implode(', ', $conflictCircles));
                    } else {
                        $options[$teacher->id] = $teacher->name . ' (مكلف في حلقة أخرى)';
                        $this->line("   ✅ {$teacher->name} → سيظهر في القائمة");
                    }
                }
            }
        }
        $this->newLine();

        // 3. معلمي نفس المسجد غير المكلفين
        $this->info('3️⃣ معلمي نفس المسجد غير المكلفين:');
        if ($firdausCircle->mosque_id) {
            $mosqueTeachers = Teacher::where('mosque_id', $firdausCircle->mosque_id)
                ->whereDoesntHave('circleAssignments', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();
            
            if ($mosqueTeachers->isEmpty()) {
                $this->warn('   ⚠️ لا يوجد معلمين غير مكلفين في نفس المسجد');
            } else {
                foreach ($mosqueTeachers as $teacher) {
                    if (!isset($options[$teacher->id])) {
                        $options[$teacher->id] = $teacher->name . ' (من نفس المسجد)';
                        $this->line("   ✅ {$teacher->name} → سيظهر في القائمة");
                    }
                }
            }
        }
        $this->newLine();

        // النتيجة النهائية
        $this->info('🎯 النتيجة النهائية - المعلمين الذين سيظهرون في قائمة الحلقة الفرعية:');
        if (empty($options)) {
            $this->error('❌ لا يوجد معلمين متاحين!');
        } else {
            foreach ($options as $id => $name) {
                $this->line("   ✅ {$name}");
            }
        }
        $this->newLine();

        // اختبار العلاقات مباشرة
        $this->info('🔗 اختبار العلاقات مباشرة:');
        $this->line('   📊 activeTeachers count: ' . $firdausCircle->activeTeachers()->count());
        $this->line('   📊 teacherAssignments count: ' . $firdausCircle->teacherAssignments()->where('is_active', true)->count());
        
        // تشغيل الكود الفعلي من CircleGroupsRelationManager للمقارنة
        $this->newLine();
        $this->info('🔄 تشغيل الكود الفعلي من CircleGroupsRelationManager:');
        $actualOptions = $this->getActualFormOptions($firdausCircle);
        
        if (empty($actualOptions)) {
            $this->error('❌ الكود الفعلي لا يُرجع أي معلمين!');
        } else {
            $this->info('✅ المعلمين من الكود الفعلي:');
            foreach ($actualOptions as $id => $name) {
                $this->line("   ✅ {$name}");
            }
        }

        return 0;
    }

    private function getActualFormOptions($quranCircle)
    {
        $options = [];
        
        // 1. جلب المعلمين المكلفين في هذه الحلقة (أولوية عالية)
        $currentCircleTeachers = $quranCircle->activeTeachers;
        foreach ($currentCircleTeachers as $teacher) {
            $options[$teacher->id] = $teacher->name . ' (مكلف في هذه الحلقة)';
        }
        
        // 2. جلب جميع المعلمين المكلفين في أي حلقة قرآنية أخرى
        $allAssignedTeachers = Teacher::whereHas('circleAssignments', function ($query) use ($quranCircle) {
            $query->where('is_active', true)
                  ->where('quran_circle_id', '!=', $quranCircle->id);
        })->with(['circleAssignments.circle'])->get();
        
        foreach ($allAssignedTeachers as $teacher) {
            if (!isset($options[$teacher->id])) {
                // التحقق من تعارض الأوقات
                $hasConflict = false;
                foreach ($teacher->circleAssignments as $assignment) {
                    if ($assignment->is_active && $assignment->circle) {
                        if ($assignment->circle->time_period === $quranCircle->time_period) {
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
        
        // 3. جلب معلمي نفس المسجد كخيارات إضافية
        if ($quranCircle->mosque_id) {
            $mosqueTeachers = Teacher::where('mosque_id', $quranCircle->mosque_id)
                ->whereDoesntHave('circleAssignments', function ($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('name')
                ->get();
            
            foreach ($mosqueTeachers as $teacher) {
                if (!isset($options[$teacher->id])) {
                    $options[$teacher->id] = $teacher->name . ' (من نفس المسجد)';
                }
            }
        }
        
        return $options;
    }
}
