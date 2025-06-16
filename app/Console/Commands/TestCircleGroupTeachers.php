<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\TeacherCircleAssignment;
use App\Models\CircleGroup;

class TestCircleGroupTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:circle-group-teachers {circle_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار مشكلة عدم ظهور المعلمين في الحلقات الفرعية';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 اختبار مشكلة المعلمين في الحلقات الفرعية');
        $this->newLine();

        $circleId = $this->argument('circle_id');
        
        if ($circleId) {
            $this->testSpecificCircle($circleId);
        } else {
            $this->testAllCircles();
        }

        $this->newLine();
        $this->info('✅ انتهى الاختبار');
    }

    private function testAllCircles()
    {
        $circles = QuranCircle::all();
        
        $this->info('📋 جميع الحلقات الموجودة:');
        foreach ($circles as $circle) {
            $this->line("   🕌 {$circle->name} (ID: {$circle->id}) - {$circle->time_period}");
        }
        
        $this->newLine();
        $this->info('اختر رقم الحلقة لاختبارها:');
        
        foreach ($circles as $circle) {
            $this->testSpecificCircle($circle->id);
            $this->newLine();
        }
    }

    private function testSpecificCircle($circleId)
    {
        $circle = QuranCircle::find($circleId);
        
        if (!$circle) {
            $this->error("❌ الحلقة غير موجودة!");
            return;
        }

        $this->info("🕌 اختبار الحلقة: {$circle->name} (ID: {$circle->id})");
        $this->line("   ⏰ الفترة الزمنية: {$circle->time_period}");
        
        // 1. فحص المعلمين في النظام القديم
        $this->checkOldSystemTeachers($circle);
        
        // 2. فحص المعلمين في النظام الجديد (المكلفين)
        $this->checkNewSystemTeachers($circle);
        
        // 3. فحص المعلمين المتاحين للحلقات الفرعية
        $this->checkAvailableTeachersForGroups($circle);
        
        // 4. فحص الحلقات الفرعية الموجودة
        $this->checkExistingCircleGroups($circle);
    }

    private function checkOldSystemTeachers($circle)
    {
        $this->info('📊 النظام القديم - معلمو الحلقة:');
        
        $oldTeachers = $circle->teachers;
        
        if ($oldTeachers->isEmpty()) {
            $this->line('   ❌ لا يوجد معلمون في النظام القديم');
        } else {
            foreach ($oldTeachers as $teacher) {
                $this->line("   👨‍🏫 {$teacher->name} (ID: {$teacher->id})");
            }
        }
    }

    private function checkNewSystemTeachers($circle)
    {
        $this->info('📊 النظام الجديد - المعلمون المكلفون:');
        
        $assignments = $circle->teacherAssignments()->where('is_active', true)->with('teacher')->get();
        
        if ($assignments->isEmpty()) {
            $this->line('   ❌ لا يوجد معلمون مكلفون');
        } else {
            foreach ($assignments as $assignment) {
                $this->line("   👨‍🏫 {$assignment->teacher->name} (ID: {$assignment->teacher->id}) - مكلف منذ: {$assignment->start_date}");
            }
        }
        
        // فحص العلاقة الجديدة activeTeachers
        $activeTeachers = $circle->activeTeachers;
        $this->info('📊 العلاقة الجديدة - المعلمون النشطون:');
        
        if ($activeTeachers->isEmpty()) {
            $this->line('   ❌ لا يوجد معلمون نشطون');
        } else {
            foreach ($activeTeachers as $teacher) {
                $this->line("   👨‍🏫 {$teacher->name} (ID: {$teacher->id})");
            }
        }
    }

    private function checkAvailableTeachersForGroups($circle)
    {
        $this->info('📊 المعلمون المتاحون للحلقات الفرعية:');
        
        // محاكاة نفس المنطق المستخدم في CircleGroupsRelationManager
        $mosque = $circle->mosque;
        
        if (!$mosque) {
            $this->line('   ⚠️ الحلقة غير مرتبطة بمسجد');
            return;
        }
        
        $this->line("   🕌 المسجد: {$mosque->name}");
        
        // الطريقة القديمة: المعلمون المرتبطون بنفس المسجد
        $oldWayTeachers = Teacher::where('mosque_id', $mosque->id)->get();
        $this->line("   📊 الطريقة القديمة - معلمو المسجد: {$oldWayTeachers->count()}");
        
        foreach ($oldWayTeachers as $teacher) {
            $this->line("      👨‍🏫 {$teacher->name}");
        }
        
        // الطريقة الجديدة: المعلمون المكلفون في هذه الحلقة
        $newWayTeachers = $circle->activeTeachers;
        $this->line("   📊 الطريقة الجديدة - المعلمون المكلفون: {$newWayTeachers->count()}");
        
        foreach ($newWayTeachers as $teacher) {
            $this->line("      👨‍🏫 {$teacher->name}");
        }
        
        // الطريقة المدمجة: الاثنان معاً
        $combinedTeachers = $oldWayTeachers->merge($newWayTeachers)->unique('id');
        $this->line("   📊 الطريقة المدمجة - المجموع: {$combinedTeachers->count()}");
        
        foreach ($combinedTeachers as $teacher) {
            $this->line("      👨‍🏫 {$teacher->name}");
        }
    }

    private function checkExistingCircleGroups($circle)
    {
        $this->info('📚 الحلقات الفرعية الموجودة:');
        
        $circleGroups = $circle->circleGroups()->with('teacher')->get();
        
        if ($circleGroups->isEmpty()) {
            $this->line('   ❌ لا توجد حلقات فرعية');
        } else {
            foreach ($circleGroups as $group) {
                $teacherInfo = $group->teacher ? $group->teacher->name : 'لا يوجد معلم';
                $this->line("   📖 {$group->name} - المعلم: {$teacherInfo}");
                
                if ($group->teacher) {
                    // فحص ما إذا كان هذا المعلم مكلف في النظام الجديد
                    $isAssignedInNewSystem = TeacherCircleAssignment::where('teacher_id', $group->teacher_id)
                        ->where('quran_circle_id', $circle->id)
                        ->where('is_active', true)
                        ->exists();
                    
                    $status = $isAssignedInNewSystem ? '✅ مكلف في النظام الجديد' : '⚠️ غير مكلف في النظام الجديد';
                    $this->line("      📋 {$status}");
                }
            }
        }
    }
}
