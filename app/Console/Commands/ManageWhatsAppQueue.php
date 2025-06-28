<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\WhatsAppMessage;

class ManageWhatsAppQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */    protected $signature = 'whatsapp:manage 
                            {action : الإجراء المطلوب (status|clear|retry|restart|process|send)}
                            {--force : تنفيذ الإجراء بدون تأكيد}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إدارة رسائل WhatsApp العالقة في قائمة الانتظار';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        $this->info('🔧 إدارة رسائل WhatsApp');
        $this->info('=' . str_repeat('=', 50));        switch ($action) {
            case 'status':
                $this->showStatus();
                break;
                
            case 'clear':
                $this->clearQueue();
                break;
                
            case 'retry':
                $this->retryFailed();
                break;
                
            case 'restart':
                $this->restartQueue();
                break;
                
            case 'process':
                $this->processPendingMessages();
                break;
                
            case 'send':
                $this->processPendingMessages();
                $this->processQueueOnce();
                break;
                
            default:
                $this->error('❌ إجراء غير صحيح. الإجراءات المتاحة: status, clear, retry, restart, process, send');
                return 1;
        }

        return 0;
    }

    /**
     * إظهار حالة قائمة الانتظار
     */
    private function showStatus()
    {
        $this->info('📊 حالة قائمة الانتظار:');
        
        // فحص جدول jobs
        $pendingJobs = DB::table('jobs')->count();
        $this->line("   🔄 الوظائف المعلقة: {$pendingJobs}");
        
        // فحص failed_jobs
        $failedJobs = DB::table('failed_jobs')->count();
        $this->line("   ❌ الوظائف الفاشلة: {$failedJobs}");
        
        // فحص رسائل WhatsApp
        if (DB::getSchemaBuilder()->hasTable('whatsapp_messages')) {
            $pendingMessages = WhatsAppMessage::where('status', 'pending')->count();
            $sentMessages = WhatsAppMessage::where('status', 'sent')->count();
            $failedMessages = WhatsAppMessage::where('status', 'failed')->count();
            
            $this->line("   📱 رسائل WhatsApp المعلقة: {$pendingMessages}");
            $this->line("   ✅ رسائل WhatsApp المرسلة: {$sentMessages}");
            $this->line("   ❌ رسائل WhatsApp الفاشلة: {$failedMessages}");
        }

        // إظهار تفاصيل الوظائف المعلقة
        if ($pendingJobs > 0) {
            $this->info("\n🔍 تفاصيل الوظائف المعلقة:");
            $jobs = DB::table('jobs')
                ->select('id', 'queue', 'payload', 'attempts', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
                
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['displayName'] ?? 'Unknown';
                $this->line("   - ID: {$job->id} | Class: {$jobClass} | Attempts: {$job->attempts}");
            }
        }

        // إظهار تفاصيل الوظائف الفاشلة
        if ($failedJobs > 0) {
            $this->info("\n❌ تفاصيل الوظائف الفاشلة:");
            $failed = DB::table('failed_jobs')
                ->select('id', 'payload', 'exception', 'failed_at')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get();
                
            foreach ($failed as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['displayName'] ?? 'Unknown';
                $this->line("   - ID: {$job->id} | Class: {$jobClass}");
                $this->line("     خطأ: " . substr($job->exception, 0, 100) . "...");
            }
        }
    }

    /**
     * مسح جميع الوظائف العالقة
     */
    private function clearQueue()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        if ($pendingJobs == 0 && $failedJobs == 0) {
            $this->info('✅ لا توجد وظائف لمسحها');
            return;
        }

        $this->warn("⚠️  سيتم مسح {$pendingJobs} وظيفة معلقة و {$failedJobs} وظيفة فاشلة");
        
        if (!$this->option('force') && !$this->confirm('هل أنت متأكد من المتابعة؟')) {
            $this->info('تم إلغاء العملية');
            return;
        }

        try {
            // مسح الوظائف المعلقة
            DB::table('jobs')->truncate();
            $this->info("✅ تم مسح {$pendingJobs} وظيفة معلقة");

            // مسح الوظائف الفاشلة
            DB::table('failed_jobs')->truncate();
            $this->info("✅ تم مسح {$failedJobs} وظيفة فاشلة");

            // إعادة تشغيل قائمة الانتظار
            Artisan::call('queue:restart');
            $this->info('✅ تم إعادة تشغيل قائمة الانتظار');

        } catch (\Exception $e) {
            $this->error('❌ خطأ في مسح الوظائف: ' . $e->getMessage());
        }
    }

    /**
     * إعادة محاولة الوظائف الفاشلة
     */
    private function retryFailed()
    {
        $failedJobs = DB::table('failed_jobs')->count();
        
        if ($failedJobs == 0) {
            $this->info('✅ لا توجد وظائف فاشلة لإعادة المحاولة');
            return;
        }

        $this->info("🔄 إعادة محاولة {$failedJobs} وظيفة فاشلة...");

        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            $this->info('✅ تم إعادة إضافة جميع الوظائف الفاشلة لقائمة الانتظار');
            
            // إعادة تشغيل قائمة الانتظار
            Artisan::call('queue:restart');
            $this->info('✅ تم إعادة تشغيل قائمة الانتظار');

        } catch (\Exception $e) {
            $this->error('❌ خطأ في إعادة المحاولة: ' . $e->getMessage());
        }
    }

    /**
     * إعادة تشغيل قائمة الانتظار
     */
    private function restartQueue()
    {
        $this->info('🔄 إعادة تشغيل قائمة الانتظار...');

        try {
            // إيقاف workers الحاليين
            Artisan::call('queue:restart');
            $this->info('✅ تم إعادة تشغيل قائمة الانتظار');

            // إظهار الحالة بعد إعادة التشغيل
            $this->info("\n📊 الحالة بعد إعادة التشغيل:");
            $this->showStatus();

        } catch (\Exception $e) {
            $this->error('❌ خطأ في إعادة التشغيل: ' . $e->getMessage());
        }
    }

    /**
     * معالجة الرسائل المعلقة في قاعدة البيانات
     */
    private function processPendingMessages()
    {
        if (!DB::getSchemaBuilder()->hasTable('whatsapp_messages')) {
            $this->error('❌ جدول whatsapp_messages غير موجود');
            return;
        }

        $pendingMessages = WhatsAppMessage::where('status', 'pending')->get();
        
        if ($pendingMessages->isEmpty()) {
            $this->info('✅ لا توجد رسائل معلقة للمعالجة');
            return;
        }

        $this->info("🔄 معالجة {$pendingMessages->count()} رسالة معلقة...");

        foreach ($pendingMessages as $message) {
            try {
                // إعادة إضافة الرسالة لقائمة الانتظار
                \App\Jobs\SendWhatsAppMessage::dispatch(
                    $message->phone_number,
                    $message->message,
                    $message->template_name ?? null
                )->onQueue('whatsapp');
                
                $this->line("   ✅ تم إعادة إضافة رسالة للهاتف: {$message->phone_number}");
                
            } catch (\Exception $e) {
                $this->line("   ❌ فشل في معالجة رسالة للهاتف: {$message->phone_number} - {$e->getMessage()}");
            }
        }

        $this->info('✅ تم الانتهاء من معالجة الرسائل المعلقة');
    }

    /**
     * تشغيل queue worker مرة واحدة لمعالجة الوظائف
     */
    private function processQueueOnce()
    {
        $this->info('🔄 تشغيل معالج قائمة الانتظار...');
        
        try {
            Artisan::call('queue:work', [
                '--once' => true,
                '--timeout' => 60,
                '--memory' => 128,
            ]);
            
            $output = Artisan::output();
            $this->line($output);
            
        } catch (\Exception $e) {
            $this->error('❌ خطأ في معالجة قائمة الانتظار: ' . $e->getMessage());
        }
    }
}
