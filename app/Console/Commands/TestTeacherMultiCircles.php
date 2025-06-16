<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\TeacherCircleAssignment;
use App\Models\CircleGroup;
use Carbon\Carbon;

class TestTeacherMultiCircles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-circles {--detailed : Show detailed information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار شامل لنظام تعدد الحلقات للمعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 بدء اختبار نظام تعدد الحلقات للمعلمين');
        $this->newLine();

        // 1. فحص البيانات الأساسية
        $this->checkBasicData();
        
        // 2. فحص التكليفات
        $this->checkAssignments();
        
        // 3. فحص منطق التعارض
        $this->checkTimeConflicts();
        
        // 4. فحص الحلقات الفرعية
        $this->checkCircleGroups();
        
        // 5. اختبار العلاقات الجديدة
        $this->checkNewRelationships();

        $this->newLine();
        $this->info('✅ انتهى الاختبار');
    }

    private function checkBasicData()
    {
        $this->info('📊 فحص البيانات الأساسية:');
        
        $teachersCount = Teacher::count();
        $circlesCount = QuranCircle::count();
        $assignmentsCount = TeacherCircleAssignment::count();
        $circleGroupsCount = CircleGroup::count();

        $this->line("   👨‍🏫 المعلمون: {$teachersCount}");
        $this->line("   🕌 الحلقات: {$circlesCount}");
        $this->line("   📋 التكليفات: {$assignmentsCount}");
        $this->line("   📚 الحلقات الفرعية: {$circleGroupsCount}");
        
        if ($this->option('detailed')) {
            $this->showDetailedBasicData();
        }
        
        $this->newLine();
    }

    private function showDetailedBasicData()
    {
        $this->line("\n   📋 تفاصيل المعلمين:");
        Teacher::all()->each(function ($teacher) {
            $this->line("      - {$teacher->name} (ID: {$teacher->id})");
        });

        $this->line("\n   📋 تفاصيل الحلقات:");
        QuranCircle::all()->each(function ($circle) {
            $this->line("      - {$circle->name} - {$circle->time_period} (ID: {$circle->id})");
        });
    }

    private function checkAssignments()
    {
        $this->info('📋 فحص التكليفات:');

        $assignments = TeacherCircleAssignment::with(['teacher', 'circle'])->get();
        
        if ($assignments->isEmpty()) {
            $this->warn('   ⚠️ لا توجد تكليفات حالياً');
            return;
        }

        foreach ($assignments as $assignment) {
            $status = $assignment->is_active ? '✅ نشط' : '❌ غير نشط';
            $this->line("   📌 {$assignment->teacher->name} ← {$assignment->circle->name} ({$assignment->circle->time_period}) - {$status}");
            
            if ($this->option('detailed')) {
                $this->line("      📅 من: {$assignment->start_date} إلى: " . ($assignment->end_date ?? 'مفتوح'));
                if ($assignment->notes) {
                    $this->line("      📝 ملاحظات: {$assignment->notes}");
                }
            }
        }
        
        $this->newLine();
    }

    private function checkTimeConflicts()
    {
        $this->info('⏰ فحص تعارض الأوقات:');

        $assignments = TeacherCircleAssignment::where('is_active', true)->get();
        $conflicts = [];

        foreach ($assignments as $assignment1) {
            foreach ($assignments as $assignment2) {
                if ($assignment1->id >= $assignment2->id) continue;
                
                if ($assignment1->teacher_id === $assignment2->teacher_id) {
                    // نفس المعلم - تحقق من التعارض
                    $circle1 = $assignment1->circle;
                    $circle2 = $assignment2->circle;
                    
                    if ($circle1->time_period === $circle2->time_period) {
                        $conflicts[] = [
                            'teacher' => $assignment1->teacher->name,
                            'circle1' => $circle1->name,
                            'circle2' => $circle2->name,
                            'time' => $circle1->time_period
                        ];
                    }
                }
            }
        }

        if (empty($conflicts)) {
            $this->line('   ✅ لا توجد تعارضات في الأوقات');
        } else {
            $this->warn('   ⚠️ توجد تعارضات:');
            foreach ($conflicts as $conflict) {
                $this->line("      🚫 {$conflict['teacher']}: {$conflict['circle1']} و {$conflict['circle2']} في {$conflict['time']}");
            }
        }
        
        $this->newLine();
    }

    private function checkCircleGroups()
    {
        $this->info('📚 فحص الحلقات الفرعية:');

        $circleGroups = CircleGroup::with(['teacher', 'quranCircle'])->get();
        
        if ($circleGroups->isEmpty()) {
            $this->warn('   ⚠️ لا توجد حلقات فرعية');
            return;
        }

        foreach ($circleGroups as $group) {
            $teacherInfo = $group->teacher ? $group->teacher->name : '❌ لا يوجد معلم';
            $this->line("   📖 {$group->name} (الحلقة الرئيسية: {$group->quranCircle->name}) - المعلم: {$teacherInfo}");
            
            // فحص ما إذا كان المعلم مكلف في الحلقة الرئيسية
            if ($group->teacher) {
                $isAssigned = TeacherCircleAssignment::where('teacher_id', $group->teacher_id)
                    ->where('quran_circle_id', $group->quran_circle_id)
                    ->where('is_active', true)
                    ->exists();
                
                $assignmentStatus = $isAssigned ? '✅ مكلف في النظام الجديد' : '⚠️ غير مكلف في النظام الجديد';
                $this->line("      📋 حالة التكليف: {$assignmentStatus}");
            }
        }
        
        $this->newLine();
    }

    private function checkNewRelationships()
    {
        $this->info('🔗 فحص العلاقات الجديدة:');

        $teachers = Teacher::with(['circleAssignments', 'activeCircles'])->get();
        
        foreach ($teachers as $teacher) {
            $activeCirclesCount = $teacher->activeCircles->count();
            $allAssignmentsCount = $teacher->circleAssignments->count();
            
            $this->line("   👨‍🏫 {$teacher->name}:");
            $this->line("      📊 إجمالي التكليفات: {$allAssignmentsCount}");
            $this->line("      ✅ الحلقات النشطة: {$activeCirclesCount}");
            
            if ($this->option('detailed') && $activeCirclesCount > 0) {
                $this->line("      📋 الحلقات النشطة:");
                foreach ($teacher->activeCircles as $circle) {
                    $this->line("         - {$circle->name} ({$circle->time_period})");
                }
            }
        }

        // فحص الحلقات
        $this->line("\n   🕌 فحص علاقات الحلقات:");
        $circles = QuranCircle::with(['activeTeachers', 'teacherAssignments'])->get();
        
        foreach ($circles as $circle) {
            $activeTeachersCount = $circle->activeTeachers->count();
            $allAssignmentsCount = $circle->teacherAssignments->count();
            
            $this->line("   🕌 {$circle->name} ({$circle->time_period}):");
            $this->line("      📊 إجمالي التكليفات: {$allAssignmentsCount}");
            $this->line("      ✅ المعلمون النشطون: {$activeTeachersCount}");
            
            if ($this->option('detailed') && $activeTeachersCount > 0) {
                $this->line("      👥 المعلمون النشطون:");
                foreach ($circle->activeTeachers as $teacher) {
                    $this->line("         - {$teacher->name}");
                }
            }
        }
        
        $this->newLine();
    }
}
