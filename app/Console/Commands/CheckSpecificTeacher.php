<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSpecificTeacher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:teacher-70';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص المعلم رقم 70 والمسجد 16';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 فحص المعلم ID: 70 في المسجد ID: 16");
        $this->info(str_repeat('=', 50));

        $teacher = \App\Models\Teacher::find(70);
        if (!$teacher) {
            $this->error("❌ المعلم غير موجود!");
            return;
        }

        $this->info("📋 بيانات المعلم:");
        $this->line("   - ID: {$teacher->id}");
        $this->line("   - الاسم: {$teacher->name}");
        $this->line("   - المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد'));
        $this->line("   - mosque_id: {$teacher->mosque_id}");
        $this->line("   - quran_circle_id: {$teacher->quran_circle_id}");
        $this->line("   - نشط: " . ($teacher->is_active ? 'نعم' : 'لا'));

        $mosque = \App\Models\Mosque::find(16);
        $this->info("\n🕌 بيانات المسجد 16:");
        if ($mosque) {
            $this->line("   - اسم المسجد: {$mosque->name}");
        } else {
            $this->error("   ❌ المسجد غير موجود!");
            return;
        }

        $this->info("\n📚 الحلقات في المسجد 16:");
        $circles = \App\Models\QuranCircle::where('mosque_id', 16)->get();
        foreach ($circles as $circle) {
            $this->line("   - {$circle->name} (ID: {$circle->id}) - {$circle->period}");
        }

        $this->info("\n📋 تكليفات المعلم في النظام الجديد:");
        $assignments = \App\Models\TeacherCircleAssignment::where('teacher_id', 70)->get();
        if ($assignments->count() > 0) {
            foreach ($assignments as $assignment) {
                $circle = $assignment->quranCircle;
                $this->line("   - {$circle->name} (ID: {$circle->id}) - " . 
                           ($assignment->is_active ? '✅ نشط' : '❌ غير نشط'));
            }
        } else {
            $this->warn("   ⚠️ لا توجد تكليفات في النظام الجديد");
        }

        $this->info("\n📖 الحلقات الفرعية للمعلم:");
        $circleGroups = \App\Models\CircleGroup::where('teacher_id', 70)->get();
        if ($circleGroups->count() > 0) {
            foreach ($circleGroups as $circleGroup) {
                $mainCircle = $circleGroup->quranCircle;
                $this->line("   - {$circleGroup->name} (الحلقة الرئيسية: {$mainCircle->name})");
                $this->line("     * ID الحلقة الفرعية: {$circleGroup->id}");
                $this->line("     * ID الحلقة الرئيسية: {$circleGroup->quran_circle_id}");
                $this->line("     * مسجد الحلقة الرئيسية: {$mainCircle->mosque_id}");
                
                // فحص التكليف
                $hasAssignment = \App\Models\TeacherCircleAssignment::where('teacher_id', 70)
                                                                   ->where('quran_circle_id', $circleGroup->quran_circle_id)
                                                                   ->where('is_active', true)
                                                                   ->exists();
                $this->line("     * مكلف في النظام الجديد: " . ($hasAssignment ? '✅' : '❌'));
            }
        } else {
            $this->warn("   ⚠️ لا توجد حلقات فرعية");
        }

        // تحليل المشكلة
        $this->info("\n🔧 تحليل المشكلة:");
        
        $circlesInMosque16 = \App\Models\QuranCircle::where('mosque_id', 16)->pluck('id')->toArray();
        $circleGroupsInMosque16 = \App\Models\CircleGroup::where('teacher_id', 70)
                                                    ->whereIn('quran_circle_id', $circlesInMosque16)
                                                    ->get();
        
        if ($circleGroupsInMosque16->count() > 0) {
            $this->info("   📊 يوجد {$circleGroupsInMosque16->count()} حلقة فرعية للمعلم في المسجد 16");
            
            $missingAssignments = [];
            foreach ($circleGroupsInMosque16 as $circleGroup) {
                $hasAssignment = \App\Models\TeacherCircleAssignment::where('teacher_id', 70)
                                                                   ->where('quran_circle_id', $circleGroup->quran_circle_id)
                                                                   ->where('is_active', true)
                                                                   ->exists();
                if (!$hasAssignment) {
                    $missingAssignments[] = [
                        'circle_id' => $circleGroup->quran_circle_id,
                        'circle_name' => $circleGroup->quranCircle->name
                    ];
                }
            }
            
            if (count($missingAssignments) > 0) {
                $this->error("   ❌ المشكلة: المعلم غير مكلف في النظام الجديد للحلقات التالية:");
                foreach ($missingAssignments as $missing) {
                    $this->line("      * {$missing['circle_name']} (ID: {$missing['circle_id']})");
                }
                
                $this->info("\n💡 الحل المقترح:");
                $this->line("   يجب إضافة تكليفات للمعلم في النظام الجديد للحلقات المذكورة أعلاه");
                
                if ($this->confirm('هل تريد إضافة التكليفات المفقودة الآن؟')) {
                    foreach ($missingAssignments as $missing) {
                        \App\Models\TeacherCircleAssignment::create([
                            'teacher_id' => 70,
                            'quran_circle_id' => $missing['circle_id'],
                            'is_active' => true,
                            'assigned_at' => now()
                        ]);
                        $this->info("   ✅ تم إضافة تكليف للحلقة: {$missing['circle_name']}");
                    }
                    $this->info("\n🎉 تم إصلاح المشكلة! المعلم سيظهر الآن في قسم معلمين الحلقة");
                }
            } else {
                $this->info("   ✅ المعلم مكلف بشكل صحيح في جميع الحلقات");
            }
        } else {
            $this->warn("   ⚠️ المعلم ليس له حلقات فرعية في المسجد 16");
        }
    }
}
