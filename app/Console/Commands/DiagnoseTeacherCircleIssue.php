<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\Mosque;

class DiagnoseTeacherCircleIssue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'diagnose:teacher-circle {teacher_id} {mosque_id}';

    /**
     * The console command description.
     */
    protected $description = 'تشخيص مشكلة عدم ظهور المعلم في قسم معلمين الحلقة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teacherId = $this->argument('teacher_id');
        $mosqueId = $this->argument('mosque_id');

        $this->info("🔍 تشخيص مشكلة المعلم ID: {$teacherId} في المسجد ID: {$mosqueId}");
        $this->info('=' . str_repeat('=', 60));

        // 1. فحص بيانات المعلم
        $this->info('1️⃣ فحص بيانات المعلم:');
        $teacher = Teacher::find($teacherId);
        
        if (!$teacher) {
            $this->error("❌ لا يوجد معلم بهذا المعرف: {$teacherId}");
            return;
        }

        $this->line("   ✅ تم العثور على المعلم:");
        $this->line("      - الاسم: {$teacher->name}");
        $this->line("      - رقم الهوية: {$teacher->identity_number}");
        $this->line("      - الهاتف: {$teacher->phone}");
        $this->line("      - المسجد المُسجل: " . ($teacher->mosque_id ?? 'غير محدد'));
        $this->line("      - الحلقة المُسجلة: " . ($teacher->quran_circle_id ?? 'غير محددة'));
        $this->line("      - نوع المهمة: " . ($teacher->task_type ?? 'غير محدد'));
        $this->line("      - نوع الحلقة: " . ($teacher->circle_type ?? 'غير محدد'));
        $this->line("      - حالة النشاط: " . ($teacher->is_active ? 'نشط' : 'غير نشط'));

        // 2. فحص بيانات المسجد
        $this->info("\n2️⃣ فحص بيانات المسجد:");
        $mosque = Mosque::find($mosqueId);
        
        if (!$mosque) {
            $this->error("❌ لا يوجد مسجد بهذا المعرف: {$mosqueId}");
            return;
        }

        $this->line("   ✅ تم العثور على المسجد:");
        $this->line("      - الاسم: {$mosque->name}");
        $this->line("      - الحي: {$mosque->neighborhood}");

        // 3. فحص الحلقات في هذا المسجد
        $this->info("\n3️⃣ فحص الحلقات في المسجد:");
        $circles = QuranCircle::where('mosque_id', $mosqueId)->get();
        
        if ($circles->isEmpty()) {
            $this->warn("⚠️ لا توجد حلقات في هذا المسجد");
        } else {
            $this->line("   📋 الحلقات الموجودة:");
            foreach ($circles as $circle) {
                $teachersCount = Teacher::where('quran_circle_id', $circle->id)->count();
                $this->line("      - ID: {$circle->id} | الاسم: {$circle->name} | المعلمين: {$teachersCount}");
            }
        }

        // 4. فحص إذا كان المعلم مُسجل في حلقة في هذا المسجد
        $this->info("\n4️⃣ فحص ربط المعلم بالحلقات:");
        
        if ($teacher->quran_circle_id) {
            $teacherCircle = QuranCircle::find($teacher->quran_circle_id);
            if ($teacherCircle) {
                $this->line("   ✅ المعلم مُسجل في حلقة:");
                $this->line("      - اسم الحلقة: {$teacherCircle->name}");
                $this->line("      - معرف الحلقة: {$teacherCircle->id}");
                $this->line("      - مسجد الحلقة: " . ($teacherCircle->mosque_id ?? 'غير محدد'));
                
                if ($teacherCircle->mosque_id == $mosqueId) {
                    $this->info("      ✅ الحلقة تنتمي للمسجد المطلوب");
                } else {
                    $this->warn("      ⚠️ الحلقة تنتمي لمسجد آخر (ID: {$teacherCircle->mosque_id})");
                }
            } else {
                $this->error("      ❌ الحلقة المُسجلة للمعلم غير موجودة (ID: {$teacher->quran_circle_id})");
            }
        } else {
            $this->warn("   ⚠️ المعلم غير مُسجل في أي حلقة");
        }

        // 5. فحص المعلمين في نفس المسجد
        $this->info("\n5️⃣ فحص المعلمين في نفس المسجد:");
        $teachersInMosque = Teacher::where('mosque_id', $mosqueId)->get();
        
        if ($teachersInMosque->isEmpty()) {
            $this->warn("   ⚠️ لا يوجد معلمين مُسجلين في هذا المسجد");
        } else {
            $this->line("   📋 المعلمين في المسجد:");
            foreach ($teachersInMosque as $t) {
                $circle = $t->quranCircle;
                $circleName = $circle ? $circle->name : 'غير محدد';
                $status = $t->is_active ? 'نشط' : 'غير نشط';
                $this->line("      - ID: {$t->id} | {$t->name} | الحلقة: {$circleName} | الحالة: {$status}");
            }
        }

        // 6. فحص المعلمين في الحلقات الفرعية
        $this->info("\n6️⃣ فحص المعلمين في الحلقات الفرعية:");
        $teachersInCircles = Teacher::whereIn('quran_circle_id', $circles->pluck('id'))->get();
        
        if ($teachersInCircles->isEmpty()) {
            $this->warn("   ⚠️ لا يوجد معلمين في الحلقات الفرعية");
        } else {
            $this->line("   📋 المعلمين في الحلقات الفرعية:");
            foreach ($teachersInCircles as $t) {
                $circle = $t->quranCircle;
                $circleName = $circle ? $circle->name : 'غير محدد';
                $status = $t->is_active ? 'نشط' : 'غير نشط';
                $isTarget = $t->id == $teacherId ? ' 👈 (المعلم المستهدف)' : '';
                $this->line("      - ID: {$t->id} | {$t->name} | الحلقة: {$circleName} | الحالة: {$status}{$isTarget}");
            }
        }

        // 7. تشخيص المشكلة
        $this->info("\n7️⃣ تشخيص المشكلة:");
        
        // فحص إذا كان المعلم في المسجد الصحيح
        if ($teacher->mosque_id != $mosqueId) {
            $this->error("   ❌ مشكلة: المعلم مُسجل في مسجد مختلف");
            $this->line("      - المسجد المُسجل للمعلم: {$teacher->mosque_id}");
            $this->line("      - المسجد المطلوب: {$mosqueId}");
            $this->warn("   💡 الحل: تحديث مسجد المعلم");
        }

        // فحص إذا كان المعلم غير نشط
        if (!$teacher->is_active) {
            $this->error("   ❌ مشكلة: المعلم غير نشط");
            $this->warn("   💡 الحل: تفعيل المعلم");
        }

        // فحص إذا كان المعلم غير مُسجل في حلقة
        if (!$teacher->quran_circle_id) {
            $this->error("   ❌ مشكلة: المعلم غير مُسجل في أي حلقة");
            $this->warn("   💡 الحل: تعيين حلقة للمعلم");
        }

        // فحص إذا كانت الحلقة في مسجد مختلف
        if ($teacher->quran_circle_id) {
            $teacherCircle = QuranCircle::find($teacher->quran_circle_id);
            if ($teacherCircle && $teacherCircle->mosque_id != $mosqueId) {
                $this->error("   ❌ مشكلة: حلقة المعلم في مسجد مختلف");
                $this->line("      - مسجد الحلقة: {$teacherCircle->mosque_id}");
                $this->line("      - المسجد المطلوب: {$mosqueId}");
                $this->warn("   💡 الحل: نقل المعلم لحلقة في المسجد الصحيح");
            }
        }

        // 8. اقتراح الحلول
        $this->info("\n8️⃣ الحلول المقترحة:");
        
        if ($teacher->mosque_id != $mosqueId) {
            $this->line("   🔧 تحديث مسجد المعلم:");
            $this->line("      Teacher::find({$teacherId})->update(['mosque_id' => {$mosqueId}]);");
        }

        if (!$teacher->is_active) {
            $this->line("   🔧 تفعيل المعلم:");
            $this->line("      Teacher::find({$teacherId})->update(['is_active' => true]);");
        }

        if (!$circles->isEmpty() && (!$teacher->quran_circle_id || ($teacher->quranCircle && $teacher->quranCircle->mosque_id != $mosqueId))) {
            $this->line("   🔧 تعيين حلقة للمعلم:");
            $firstCircle = $circles->first();
            $this->line("      Teacher::find({$teacherId})->update(['quran_circle_id' => {$firstCircle->id}]);");
        }

        $this->info("\n✅ انتهى التشخيص");
    }
}
