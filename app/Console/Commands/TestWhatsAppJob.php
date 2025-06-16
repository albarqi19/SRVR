<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Jobs\SendWhatsAppMessage;

class TestWhatsAppJob extends Command
{
    protected $signature = 'test:whatsapp-job';
    protected $description = 'اختبار WhatsApp Job مباشرة';

    public function handle()
    {
        $this->info('🧪 اختبار WhatsApp Job مباشرة');
        $this->info('=' . str_repeat('=', 40));

        // البحث عن رسالة منتظرة
        $pendingMessage = WhatsAppMessage::where('status', 'pending')->first();
        
        if (!$pendingMessage) {
            $this->error('❌ لا توجد رسائل منتظرة للاختبار');
            return;
        }

        $this->info("📨 اختبار الرسالة ID: {$pendingMessage->id}");
        $this->line("   - الهاتف: {$pendingMessage->phone_number}");
        $this->line("   - النوع: {$pendingMessage->message_type}");
        $this->line("   - الحالة: {$pendingMessage->status}");

        // فحص الإعدادات
        $this->info('🔧 فحص الإعدادات:');
        $notificationsEnabled = WhatsAppSetting::notificationsEnabled();
        $this->line("   - الإشعارات مُفعلة: " . ($notificationsEnabled ? 'نعم' : 'لا'));

        if (!$notificationsEnabled) {
            $this->error('❌ الإشعارات غير مُفعلة - سيتم تخطي الرسالة');
            return;
        }

        // فحص إعدادات API
        $apiConfig = WhatsAppSetting::getApiConfig();
        $this->line("   - API URL: " . ($apiConfig['url'] ?? 'غير محدد'));
        $this->line("   - API Token: " . ($apiConfig['token'] ? 'محدد' : 'غير محدد'));

        // تنفيذ الـ Job مباشرة
        $this->info('🚀 تنفيذ الـ Job مباشرة...');
        
        try {
            $job = new SendWhatsAppMessage($pendingMessage->id);
            $job->handle();
            
            // فحص الرسالة بعد التنفيذ
            $pendingMessage->refresh();
            $this->info("✅ تم تنفيذ الـ Job");
            $this->line("   - الحالة الجديدة: {$pendingMessage->status}");
            
            if ($pendingMessage->error_message) {
                $this->error("   - رسالة الخطأ: {$pendingMessage->error_message}");
            }
            
            if ($pendingMessage->api_response) {
                $this->line("   - استجابة API: {$pendingMessage->api_response}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في تنفيذ الـ Job: " . $e->getMessage());
            $this->error("   - السطر: " . $e->getLine());
            $this->error("   - الملف: " . $e->getFile());
        }

        $this->info('🏁 انتهى الاختبار');
    }
}
