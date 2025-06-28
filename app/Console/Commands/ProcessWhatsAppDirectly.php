<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppDirectly extends Command
{
    protected $signature = 'whatsapp:process-direct 
                            {--limit=10 : عدد الرسائل المراد معالجتها}
                            {--dry-run : معاينة بدون إرسال فعلي}';

    protected $description = 'معالجة رسائل WhatsApp مباشرة بدون queue';

    private $whatsappService;

    public function __construct()
    {
        parent::__construct();
        $this->whatsappService = app(WhatsAppService::class);
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        
        $this->info('📱 معالجة رسائل WhatsApp مباشرة');
        $this->info('=' . str_repeat('=', 50));
        
        if ($dryRun) {
            $this->warn('🔍 وضع المعاينة - لن يتم إرسال رسائل فعلية');
        }

        // جلب الرسائل المعلقة
        $pendingMessages = WhatsAppMessage::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info('✅ لا توجد رسائل معلقة للمعالجة');
            return 0;
        }

        $this->info("🔄 معالجة {$pendingMessages->count()} رسالة...");
        
        $successful = 0;
        $failed = 0;

        foreach ($pendingMessages as $message) {
            $this->line("📤 معالجة رسالة للهاتف: {$message->phone_number}");
            
            if ($dryRun) {
                $this->line("   📝 [معاينة] الرسالة: " . substr($message->message, 0, 50) . "...");
                $successful++;
                continue;
            }            try {
                // إرسال الرسالة مباشرة بدون تغيير الحالة أولاً
                $result = $this->whatsappService->sendMessage(
                    $message->phone_number,
                    $message->message,
                    $message->template_name
                );

                if ($result && isset($result['success']) && $result['success']) {
                    // تحديث حالة الرسالة إلى مرسلة
                    $message->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_data' => json_encode($result)
                    ]);
                    
                    $this->line("   ✅ تم الإرسال بنجاح");
                    $successful++;
                    
                } else {
                    // تحديث حالة الرسالة إلى فاشلة
                    $message->update([
                        'status' => 'failed',
                        'error_message' => 'فشل في الإرسال',
                        'response_data' => json_encode($result)
                    ]);
                    
                    $this->line("   ❌ فشل في الإرسال");
                    $failed++;
                }

            } catch (\Exception $e) {
                // تحديث حالة الرسالة إلى فاشلة مع تسجيل الخطأ
                $message->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
                
                $this->line("   ❌ خطأ: " . $e->getMessage());
                $failed++;
                
                Log::error('WhatsApp Direct Send Error', [
                    'message_id' => $message->id,
                    'phone' => $message->phone_number,
                    'error' => $e->getMessage()
                ]);
            }
            
            // تأخير قصير بين الرسائل لتجنب Rate Limiting
            usleep(500000); // 0.5 ثانية
        }

        // عرض النتائج
        $this->info("\n📊 النتائج:");
        $this->info("   ✅ رسائل ناجحة: {$successful}");
        $this->info("   ❌ رسائل فاشلة: {$failed}");
        
        if (!$dryRun && $successful > 0) {
            $this->info("   🎉 تم إرسال {$successful} رسالة بنجاح!");
        }

        return 0;
    }
}
