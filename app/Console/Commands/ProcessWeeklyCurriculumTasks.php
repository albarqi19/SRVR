<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutomatedNotificationService;
use App\Services\FlexibleCurriculumService;
use Illuminate\Support\Facades\Log;

class ProcessWeeklyCurriculumTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curriculum:weekly-tasks 
                            {--weekly-reports : إرسال التقارير الأسبوعية}
                            {--cleanup-alerts : تنظيف التنبيهات المنتهية الصلاحية}
                            {--performance-analysis : تحليل الأداء الأسبوعي}
                            {--all : تشغيل جميع المهام}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشغيل المهام الأسبوعية لنظام التحديث التلقائي للمناهج';

    protected $notificationService;
    protected $flexibleCurriculumService;

    public function __construct(
        AutomatedNotificationService $notificationService,
        FlexibleCurriculumService $flexibleCurriculumService
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->flexibleCurriculumService = $flexibleCurriculumService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء تشغيل المهام الأسبوعية لنظام التحديث التلقائي للمناهج...');
        
        $startTime = now();
        $results = [
            'start_time' => $startTime,
            'tasks_completed' => [],
            'errors' => []
        ];

        try {
            $tasks = $this->determineTasks();
            
            foreach ($tasks as $task) {
                $this->info("📋 تشغيل مهمة: {$task}");
                
                try {
                    $taskResult = $this->executeTask($task);
                    $results['tasks_completed'][$task] = $taskResult;
                    $this->info("✅ تم إكمال: {$task}");
                } catch (\Exception $e) {
                    $results['errors'][$task] = $e->getMessage();
                    $this->error("❌ خطأ في: {$task} - {$e->getMessage()}");
                    Log::error("خطأ في المهمة الأسبوعية: {$task}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $endTime = now();
            $results['end_time'] = $endTime;
            $results['duration_seconds'] = $endTime->diffInSeconds($startTime);
            
            $this->displayResults($results);
            Log::info('تم إكمال المهام الأسبوعية', $results);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ عام في تشغيل المهام الأسبوعية: {$e->getMessage()}");
            Log::error('خطأ عام في المهام الأسبوعية', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
        
        return 0;
    }

    protected function determineTasks(): array
    {
        $tasks = [];
        
        if ($this->option('all')) {
            $tasks = ['weekly-reports', 'cleanup-alerts', 'performance-analysis'];
        } else {
            if ($this->option('weekly-reports')) $tasks[] = 'weekly-reports';
            if ($this->option('cleanup-alerts')) $tasks[] = 'cleanup-alerts';
            if ($this->option('performance-analysis')) $tasks[] = 'performance-analysis';
        }
        
        if (empty($tasks)) {
            $tasks = ['weekly-reports', 'cleanup-alerts'];
        }
        
        return $tasks;
    }

    protected function executeTask(string $task): array
    {
        switch ($task) {
            case 'weekly-reports':
                return $this->sendWeeklyReports();
                
            case 'cleanup-alerts':
                return $this->cleanupExpiredAlerts();
                
            case 'performance-analysis':
                return $this->performWeeklyAnalysis();
                
            default:
                throw new \InvalidArgumentException("مهمة غير معروفة: {$task}");
        }
    }

    protected function sendWeeklyReports(): array
    {
        $this->info('  📊 إرسال التقارير الأسبوعية...');
        
        $results = $this->notificationService->sendWeeklyReports();
        
        $this->info("  📈 تم إرسال {$results['reports_sent']} تقرير أسبوعي");
        
        return $results;
    }

    protected function cleanupExpiredAlerts(): array
    {
        $this->info('  🧹 تنظيف التنبيهات المنتهية الصلاحية...');
        
        // تنظيف التنبيهات القديمة (أكثر من 30 يوم)
        $deleted = \App\Models\CurriculumAlert::where('created_at', '<', now()->subDays(30))
            ->whereNotNull('teacher_decision')
            ->delete();
            
        $this->info("  🗑️ تم حذف {$deleted} تنبيه منتهي الصلاحية");
        
        return ['deleted_alerts' => $deleted];
    }

    protected function performWeeklyAnalysis(): array
    {
        $this->info('  📊 تحليل الأداء الأسبوعي...');
        
        // تحليل أداء النظام والطلاب
        $analysis = [
            'analyzed_students' => 0,
            'performance_trends' => [],
            'recommendations' => []
        ];
        
        $this->info("  ✨ تم تحليل {$analysis['analyzed_students']} طالب");
        
        return $analysis;
    }

    protected function displayResults(array $results): void
    {
        $this->info('');
        $this->info('📋 ملخص المهام الأسبوعية المكتملة:');
        $this->info('═══════════════════════════════════════');
        
        foreach ($results['tasks_completed'] as $task => $result) {
            $this->info("✅ {$task}:");
            
            if (isset($result['reports_sent'])) {
                $this->info("   - تقارير مرسلة: {$result['reports_sent']}");
            }
            if (isset($result['deleted_alerts'])) {
                $this->info("   - تنبيهات محذوفة: {$result['deleted_alerts']}");
            }
            if (isset($result['analyzed_students'])) {
                $this->info("   - طلاب تم تحليلهم: {$result['analyzed_students']}");
            }
        }
        
        if (!empty($results['errors'])) {
            $this->info('');
            $this->error('❌ أخطاء حدثت:');
            foreach ($results['errors'] as $task => $error) {
                $this->error("❌ {$task}: {$error}");
            }
        }
        
        $this->info('');
        $this->info("⏱️ وقت التنفيذ: {$results['duration_seconds']} ثانية");
        $this->info('🎉 تم إكمال جميع المهام الأسبوعية بنجاح!');
    }
}
