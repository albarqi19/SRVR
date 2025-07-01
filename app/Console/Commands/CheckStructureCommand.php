<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mosque;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use Illuminate\Support\Facades\Schema;

class CheckStructureCommand extends Command
{
    protected $signature = 'check:structure';
    protected $description = 'فحص بنية المساجد والمدارس القرآنية والحلقات الفرعية';

    public function handle()
    {
        $this->info('🔍 فحص بنية قاعدة البيانات للمساجد والحلقات...');
        $this->newLine();

        // 1. فحص جدول المساجد
        $this->line('🕌 فحص جدول المساجد (mosques):');
        $this->line(str_repeat('-', 50));
        
        $mosqueColumns = Schema::getColumnListing('mosques');
        $this->comment('الأعمدة المتاحة: ' . implode(', ', $mosqueColumns));
        
        $mosques = Mosque::take(3)->get();
        $this->info("إجمالي المساجد: " . Mosque::count());
        
        foreach ($mosques as $mosque) {
            $this->line("ID: {$mosque->id} | الاسم: {$mosque->name}");
        }
        
        $this->newLine();

        // 2. فحص جدول المدارس القرآنية
        $this->line('📚 فحص جدول المدارس القرآنية (quran_circles):');
        $this->line(str_repeat('-', 50));
        
        $circleColumns = Schema::getColumnListing('quran_circles');
        $this->comment('الأعمدة المتاحة: ' . implode(', ', $circleColumns));
        
        $circles = QuranCircle::with('mosque')->take(5)->get();
        $this->info("إجمالي المدارس القرآنية: " . QuranCircle::count());
        
        foreach ($circles as $circle) {
            $mosqueName = $circle->mosque ? $circle->mosque->name : 'غير محدد';
            $this->line("ID: {$circle->id} | الاسم: {$circle->name} | المسجد: {$mosqueName}");
            if (isset($circle->circle_type)) {
                $this->comment("  النوع: {$circle->circle_type}");
            }
        }
        
        $this->newLine();

        // 3. فحص جدول الحلقات الفرعية
        $this->line('👥 فحص جدول الحلقات الفرعية (circle_groups):');
        $this->line(str_repeat('-', 50));
        
        $groupColumns = Schema::getColumnListing('circle_groups');
        $this->comment('الأعمدة المتاحة: ' . implode(', ', $groupColumns));
        
        $groups = CircleGroup::with(['quranCircle', 'quranCircle.mosque'])->take(5)->get();
        $this->info("إجمالي الحلقات الفرعية: " . CircleGroup::count());
        
        foreach ($groups as $group) {
            $circleName = $group->quranCircle ? $group->quranCircle->name : 'غير محدد';
            $mosqueName = $group->quranCircle && $group->quranCircle->mosque ? $group->quranCircle->mosque->name : 'غير محدد';
            $this->line("ID: {$group->id} | الاسم: {$group->name}");
            $this->comment("  المدرسة القرآنية: {$circleName}");
            $this->comment("  المسجد: {$mosqueName}");
        }
        
        $this->newLine();

        // 4. فحص العلاقات
        $this->line('🔗 فحص العلاقات:');
        $this->line(str_repeat('-', 50));
        
        // مسجد مع مدارسه القرآنية
        $mosqueWithCircles = Mosque::with('quranCircles')->first();
        if ($mosqueWithCircles) {
            $this->info("مسجد: {$mosqueWithCircles->name}");
            $this->comment("عدد المدارس القرآنية: " . $mosqueWithCircles->quranCircles->count());
            
            foreach ($mosqueWithCircles->quranCircles->take(3) as $circle) {
                $this->line("  - {$circle->name}");
            }
        }
        
        $this->newLine();
        
        // مدرسة قرآنية مع حلقاتها الفرعية
        $circleWithGroups = QuranCircle::with('circleGroups')->first();
        if ($circleWithGroups) {
            $this->info("مدرسة قرآنية: {$circleWithGroups->name}");
            $this->comment("عدد الحلقات الفرعية: " . $circleWithGroups->circleGroups->count());
            
            foreach ($circleWithGroups->circleGroups->take(3) as $group) {
                $this->line("  - {$group->name}");
            }
        }

        $this->newLine();
        
        // 5. إحصائيات سريعة
        $this->line('📊 إحصائيات سريعة:');
        $this->line(str_repeat('-', 50));
        $this->info("المساجد: " . Mosque::count());
        $this->info("المدارس القرآنية: " . QuranCircle::count());
        $this->info("الحلقات الفرعية: " . CircleGroup::count());
        
        // 6. اقتراحات للـ API
        $this->newLine();
        $this->line('💡 اقتراحات لبنية الـ API:');
        $this->line(str_repeat('=', 50));
        $this->comment('بناءً على البيانات الفعلية، يجب أن يتضمن الـ API:');
        $this->info('- معرف ونم المسجد');
        $this->info('- معرف ونم المدرسة القرآنية');
        $this->info('- معرف ونم الحلقة الفرعية');
        $this->info('- أنواع المدارس القرآنية (إن وجدت)');
        $this->info('- حالة كل مستوى (نشط/غير نشط)');

        return 0;
    }
}
