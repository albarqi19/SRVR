<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuranCircle;
use App\Models\Teacher;

class DebugCircleGroupForm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:circle-group-form {circle_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'محاكاة نموذج إضافة الحلقة الفرعية في Filament لتحديد المشكلة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $circleId = $this->argument('circle_id');
        $quranCircle = QuranCircle::find($circleId);
        
        if (!$quranCircle) {
            $this->error("❌ الحلقة غير موجودة!");
            return;
        }

        $this->info("🔍 محاكاة نموذج إضافة حلقة فرعية للحلقة: {$quranCircle->name}");
        $this->newLine();

        // محاكاة نفس المنطق المستخدم في CircleGroupsRelationManager
        $this->simulateFilamentFormLogic($quranCircle);
    }

    private function simulateFilamentFormLogic($quranCircle)
    {
        $this->info("📋 تشغيل نفس منطق options() في CircleGroupsRelationManager:");
        $this->newLine();

        try {
            // نفس الكود المستخدم في Filament
            $this->line("   1️⃣ جلب المعلمين النشطين من النظام الجديد:");
            $activeTeachersCollection = $quranCircle->activeTeachers()->get(['teachers.id', 'teachers.name']);
            $activeTeachers = $activeTeachersCollection->pluck('name', 'id');
            $this->line("      📊 النتيجة: " . $activeTeachers->count() . " معلم");
            foreach ($activeTeachers as $id => $name) {
                $this->line("         - {$name} (ID: {$id})");
            }
            
            $this->newLine();
            $this->line("   2️⃣ جلب معلمي المسجد من النظام القديم:");
            $mosqueTeachers = collect();
            if ($quranCircle->mosque_id) {
                $mosqueTeachers = Teacher::where('mosque_id', $quranCircle->mosque_id)
                    ->pluck('name', 'id');
                $this->line("      📊 النتيجة: " . $mosqueTeachers->count() . " معلم");
                foreach ($mosqueTeachers as $id => $name) {
                    $this->line("         - {$name} (ID: {$id})");
                }
            } else {
                $this->line("      ⚠️ الحلقة غير مرتبطة بمسجد");
            }
            
            $this->newLine();
            $this->line("   3️⃣ دمج القوائم:");
            $allTeachers = $activeTeachers->merge($mosqueTeachers)->unique();
            $this->line("      📊 النتيجة النهائية: " . $allTeachers->count() . " معلم");
            
            if ($allTeachers->isEmpty()) {
                $this->error("      ❌ لا يوجد معلمون متاحون!");
            } else {
                $this->info("      ✅ المعلمون المتاحون في القائمة:");
                foreach ($allTeachers as $id => $name) {
                    $this->line("         - {$name} (ID: {$id})");
                }
            }

            $this->newLine();
            $this->line("   4️⃣ تحويل إلى array (كما يتوقع Filament):");
            $finalArray = $allTeachers->toArray();
            $this->line("      📊 المصفوفة النهائية:");
            $this->line("      " . json_encode($finalArray, JSON_UNESCAPED_UNICODE));

        } catch (\Exception $e) {
            $this->error("❌ خطأ أثناء تشغيل المنطق: " . $e->getMessage());
            $this->line("📍 ملف الخطأ: " . $e->getFile() . " السطر: " . $e->getLine());
        }

        $this->newLine();
        $this->testDirectRelationships($quranCircle);
    }

    private function testDirectRelationships($quranCircle)
    {
        $this->info("🔗 اختبار العلاقات مباشرة:");
        $this->newLine();

        try {
            // اختبار العلاقة activeTeachers
            $this->line("   📊 اختبار activeTeachers():");
            $activeTeachersQuery = $quranCircle->activeTeachers();
            $this->line("      SQL: " . $activeTeachersQuery->toSql());
            $activeTeachersResult = $activeTeachersQuery->get();
            $this->line("      📊 النتيجة: " . $activeTeachersResult->count() . " معلم");

            // اختبار العلاقة teacherAssignments
            $this->line("   📊 اختبار teacherAssignments():");
            $assignmentsQuery = $quranCircle->teacherAssignments()->where('is_active', true);
            $this->line("      SQL: " . $assignmentsQuery->toSql());
            $assignments = $assignmentsQuery->with('teacher')->get();
            $this->line("      📊 النتيجة: " . $assignments->count() . " تكليف");

            foreach ($assignments as $assignment) {
                $this->line("         - {$assignment->teacher->name} (مكلف منذ: {$assignment->start_date})");
            }

        } catch (\Exception $e) {
            $this->error("❌ خطأ في اختبار العلاقات: " . $e->getMessage());
        }
    }
}
