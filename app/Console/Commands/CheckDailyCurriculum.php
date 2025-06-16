<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentCurriculum;
use App\Models\Student;
use App\Models\Curriculum;
use App\Services\DailyCurriculumTrackingService;

class CheckDailyCurriculum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:daily-curriculum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص نظام المنهج اليومي والبيانات المرتبطة به';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== فحص نظام المنهج اليومي ===');
        
        // فحص جدول student_curricula
        $this->line('');
        $this->info('1. فحص جدول student_curricula:');
        
        $count = StudentCurriculum::count();
        $this->line("   عدد السجلات: $count");
        
        if ($count > 0) {
            $first = StudentCurriculum::first();
            $this->line("   أول سجل - ID: {$first->id}");
            $this->line("   Student ID: {$first->student_id}");
            $this->line("   Curriculum ID: {$first->curriculum_id}");
            $this->line("   Daily Memorization Pages: " . ($first->daily_memorization_pages ?? 'NULL'));
            $this->line("   Daily Minor Review Pages: " . ($first->daily_minor_review_pages ?? 'NULL'));
            $this->line("   Daily Major Review Pages: " . ($first->daily_major_review_pages ?? 'NULL'));
            $this->line("   Current Page: " . ($first->current_page ?? 'NULL'));
            $this->line("   Current Surah: " . ($first->current_surah ?? 'NULL'));
            $this->line("   Current Ayah: " . ($first->current_ayah ?? 'NULL'));
            $this->line("   Status: " . ($first->status ?? 'NULL'));
            $this->line("   Is Active: " . ($first->is_active ? 'true' : 'false'));
            $this->line("   Start Date: " . ($first->start_date ?? 'NULL'));
        }
        
        // فحص العلاقات
        $this->line('');
        $this->info('2. فحص العلاقات:');
        
        $students = Student::count();
        $curricula = Curriculum::count();
        $this->line("   عدد الطلاب: $students");
        $this->line("   عدد المناهج: $curricula");
        
        // اختبار خدمة DailyCurriculumTrackingService
        $this->line('');
        $this->info('3. اختبار خدمة DailyCurriculumTrackingService:');
        
        if ($count > 0) {
            $service = new DailyCurriculumTrackingService();
            $first = StudentCurriculum::first();
            
            try {
                $dailyCurriculum = $service->getDailyCurriculum($first->student_id);
                
                if ($dailyCurriculum) {
                    $this->line("   ✅ تم الحصول على المنهج اليومي بنجاح!");
                    $this->line("   اسم الطالب: " . $dailyCurriculum['student_name']);
                    $this->line("   اسم المنهج: " . $dailyCurriculum['curriculum_name']);
                    $this->line("   الصفحة الحالية: " . $dailyCurriculum['current_page']);
                    $this->line("   نسبة التقدم: " . $dailyCurriculum['progress_percentage'] . "%");
                } else {
                    $this->error("   ❌ فشل في الحصول على المنهج اليومي");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ خطأ في الخدمة: " . $e->getMessage());
            }
        }
        
        // فحص مسارات Filament
        $this->line('');
        $this->info('4. فحص مسارات Filament:');
        
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        $dailyCurriculumRoutes = 0;
        
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'daily-curriculum-management')) {
                $dailyCurriculumRoutes++;
            }
        }
        
        $this->line("   عدد مسارات المنهج اليومي: $dailyCurriculumRoutes");
        
        $this->line('');
        $this->info('=== انتهى الفحص ===');
        
        return 0;
    }
}
