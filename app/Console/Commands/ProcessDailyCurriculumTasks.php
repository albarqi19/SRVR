<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FlexibleCurriculumService;
use App\Services\AutomatedNotificationService;
use App\Services\DailyCurriculumTrackingService;
use Illuminate\Support\Facades\Log;

class ProcessDailyCurriculumTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'curriculum:daily-tasks 
                            {--evaluate-students : تقييم جميع الطلاب النشطين}
                            {--send-notifications : إرسال الإشعارات}
                            {--send-reminders : إرسال التذكيرات}
                            {--daily-reports : إرسال التقارير اليومية}
                            {--all : تشغيل جميع المهام}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشغيل المهام اليومية لنظام التحديث التلقائي للمناهج';

    protected $flexibleCurriculumService;
    protected $notificationService;
    protected $dailyTrackingService;

    public function __construct(
        FlexibleCurriculumService $flexibleCurriculumService,
        AutomatedNotificationService $notificationService,
        DailyCurriculumTrackingService $dailyTrackingService
    ) {
        parent::__construct();
        $this->flexibleCurriculumService = $flexibleCurriculumService;
        $this->notificationService = $notificationService;
        $this->dailyTrackingService = $dailyTrackingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء تشغيل المهام اليومية لنظام التحديث التلقائي للمناهج...');
        
        $startTime = now();
        $results = [
            'start_time' => $startTime,
            'tasks_completed' => [],
            'errors' => []
        ];

        try {
            // تحديد المهام المطلوب تشغيلها
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
                    Log::error("خطأ في المهمة اليومية: {$task}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            $results['end_time'] = $endTime;
            $results['duration_seconds'] = $duration;
            
            $this->displayResults($results);
            
            Log::info('تم إكمال المهام اليومية', $results);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ عام في تشغيل المهام اليومية: {$e->getMessage()}");
            Log::error('خطأ عام في المهام اليومية', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }

    /**
     * تحديد المهام المطلوب تشغيلها
     */
    protected function determineTasks(): array
    {
        $tasks = [];
        
        if ($this->option('all')) {
            $tasks = ['evaluate-students', 'send-notifications', 'send-reminders', 'daily-reports'];
        } else {
            if ($this->option('evaluate-students')) $tasks[] = 'evaluate-students';
            if ($this->option('send-notifications')) $tasks[] = 'send-notifications';
            if ($this->option('send-reminders')) $tasks[] = 'send-reminders';
            if ($this->option('daily-reports')) $tasks[] = 'daily-reports';
        }
        
        // إذا لم يتم تحديد أي مهمة، تشغيل المهام الأساسية
        if (empty($tasks)) {
            $tasks = ['evaluate-students', 'send-notifications'];
        }
        
        return $tasks;
    }

    /**
     * تنفيذ مهمة محددة
     */
    protected function executeTask(string $task): array
    {
        switch ($task) {
            case 'evaluate-students':
                return $this->evaluateStudents();
                
            case 'send-notifications':
                return $this->sendNotifications();
                
            case 'send-reminders':
                return $this->sendReminders();
                
            case 'daily-reports':
                return $this->sendDailyReports();
                
            default:
                throw new \InvalidArgumentException("مهمة غير معروفة: {$task}");
        }
    }

    /**
     * تقييم جميع الطلاب النشطين
     */
    protected function evaluateStudents(): array
    {
        $this->info('  📊 تقييم الطلاب النشطين...');
        
        $results = $this->flexibleCurriculumService->evaluateAllActiveStudents();
        
        $this->info("  ✨ تم تقييم {$results['evaluated']} طالب");
        $this->info("  🔔 تم إنشاء {$results['alerts_created']} تنبيه جديد");
        
        if (!empty($results['errors'])) {
            $this->warn("  ⚠️ حدثت " . count($results['errors']) . " أخطاء أثناء التقييم");
        }
        
        return $results;
    }

    /**
     * إرسال الإشعارات الجديدة
     */
    protected function sendNotifications(): array
    {
        $this->info('  📤 إرسال الإشعارات الجديدة...');
        
        $results = $this->notificationService->sendNewAlertsNotifications();
        
        $this->info("  📧 تم إرسال {$results['notifications_sent']} إشعار");
        
        if (!empty($results['errors'])) {
            $this->warn("  ⚠️ حدثت " . count($results['errors']) . " أخطاء أثناء الإرسال");
        }
        
        return $results;
    }

    /**
     * إرسال التذكيرات
     */
    protected function sendReminders(): array
    {
        $this->info('  🔔 إرسال التذكيرات للطلاب المتأخرين...');
        
        $results = $this->notificationService->sendRecitationReminders();
        
        $this->info("  📱 تم إرسال {$results['reminders_sent']} تذكير");
        
        if (!empty($results['errors'])) {
            $this->warn("  ⚠️ حدثت " . count($results['errors']) . " أخطاء أثناء إرسال التذكيرات");
        }
        
        return $results;
    }

    /**
     * إرسال التقارير اليومية
     */
    protected function sendDailyReports(): array
    {
        $this->info('  📈 إرسال التقارير اليومية...');
        
        $results = $this->notificationService->sendDailyPerformanceReports();
        
        $this->info("  📊 تم إرسال {$results['reports_sent']} تقرير يومي");
        
        if (!empty($results['errors'])) {
            $this->warn("  ⚠️ حدثت " . count($results['errors']) . " أخطاء أثناء إرسال التقارير");
        }
        
        return $results;
    }

    /**
     * عرض نتائج المهام
     */
    protected function displayResults(array $results): void
    {
        $this->info('');
        $this->info('📋 ملخص المهام المكتملة:');
        $this->info('═══════════════════════════════');
        
        foreach ($results['tasks_completed'] as $task => $result) {
            $this->info("✅ {$task}:");
            
            if (isset($result['evaluated'])) {
                $this->info("   - تم تقييم: {$result['evaluated']} طالب");
            }
            if (isset($result['alerts_created'])) {
                $this->info("   - تنبيهات جديدة: {$result['alerts_created']}");
            }
            if (isset($result['notifications_sent'])) {
                $this->info("   - إشعارات مرسلة: {$result['notifications_sent']}");
            }
            if (isset($result['reminders_sent'])) {
                $this->info("   - تذكيرات مرسلة: {$result['reminders_sent']}");
            }
            if (isset($result['reports_sent'])) {
                $this->info("   - تقارير مرسلة: {$result['reports_sent']}");
            }
        }
        
        if (!empty($results['errors'])) {
            $this->info('');
            $this->error('❌ أخطاء حدثت:');
            $this->error('═══════════════════');
            
            foreach ($results['errors'] as $task => $error) {
                $this->error("❌ {$task}: {$error}");
            }
        }
        
        $this->info('');
        $this->info("⏱️ وقت التنفيذ: {$results['duration_seconds']} ثانية");
        $this->info("🕐 تم البدء: {$results['start_time']->format('Y-m-d H:i:s')}");
        $this->info("🕐 تم الانتهاء: {$results['end_time']->format('Y-m-d H:i:s')}");
        $this->info('');
        $this->info('🎉 تم إكمال جميع المهام اليومية بنجاح!');
    }
}
