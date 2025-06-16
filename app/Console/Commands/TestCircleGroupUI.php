<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuranCircle;
use App\Models\Teacher;

class TestCircleGroupUI extends Command
{
    protected $signature = 'test:circle-group-ui {circle_id}';
    protected $description = 'اختبار واجهة إضافة الحلقات الفرعية';

    public function handle()
    {
        $circleId = $this->argument('circle_id');
        $quranCircle = QuranCircle::find($circleId);
        
        if (!$quranCircle) {
            $this->error("❌ الحلقة غير موجودة!");
            return 1;
        }
        
        $this->info("🔍 اختبار واجهة الحلقة: {$quranCircle->name}");
        
        // تطبيق نفس المنطق المستخدم في الواجهة
        $options = [];
        
        // 1. جلب المعلمين المكلفين نشطين
        $assignedTeachers = $quranCircle->activeTeachers;
        $this->info("📊 المعلمون المكلفون: " . $assignedTeachers->count());
        
        if ($assignedTeachers->isNotEmpty()) {
            foreach ($assignedTeachers as $teacher) {
                $options[$teacher->id] = $teacher->name . ' (مكلف)';
                $this->line("   ✅ {$teacher->name} (ID: {$teacher->id}) - مكلف");
            }
        }
        
        // 2. جلب معلمي المسجد
        if ($quranCircle->mosque_id) {
            $mosqueTeachers = Teacher::where('mosque_id', $quranCircle->mosque_id)
                ->orderBy('name')
                ->get();
            
            $this->info("📊 معلمو المسجد: " . $mosqueTeachers->count());
            
            foreach ($mosqueTeachers as $teacher) {
                if (!isset($options[$teacher->id])) {
                    $options[$teacher->id] = $teacher->name;
                    $this->line("   ✅ {$teacher->name} (ID: {$teacher->id}) - من المسجد");
                }
            }
        }
        
        // 3. خيار احتياطي
        if (empty($options)) {
            $this->warn("⚠️ لا توجد خيارات، جلب جميع المعلمين...");
            $allTeachers = Teacher::orderBy('name')->get();
            foreach ($allTeachers as $teacher) {
                $options[$teacher->id] = $teacher->name;
            }
        }
        
        $this->info("🎯 النتيجة النهائية:");
        $this->table(['ID', 'اسم المعلم'], 
            collect($options)->map(fn($name, $id) => [$id, $name])->toArray()
        );
        
        $this->info("✅ إجمالي الخيارات المتاحة: " . count($options));
        
        return 0;
    }
}
