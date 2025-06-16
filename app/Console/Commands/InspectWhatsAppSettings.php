<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\DB;

class InspectWhatsAppSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:inspect-whats-app-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص إعدادات WhatsApp الحالية';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 فحص إعدادات WhatsApp الحالية');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص جدول WhatsApp Settings
        $this->info('1️⃣ جدول whatsapp_settings:');
        $settings = WhatsAppSetting::all();
        
        if ($settings->count() > 0) {
            foreach ($settings as $setting) {
                $this->line("   - {$setting->key}: {$setting->value}");
            }
        } else {
            $this->warn('   ❌ لا توجد إعدادات في الجدول');
        }

        // 2. فحص الإعدادات المطلوبة
        $this->info('2️⃣ الإعدادات المطلوبة:');
        $requiredSettings = [
            'api_url',
            'api_token', 
            'notify_teacher_added',
            'teacher_notifications'
        ];

        foreach ($requiredSettings as $key) {
            $value = WhatsAppSetting::get($key);
            $status = $value ? '✅' : '❌';
            $this->line("   {$status} {$key}: " . ($value ?: 'غير محدد'));
        }

        // 3. اختبار الاتصال بـ API
        $this->info('3️⃣ اختبار الاتصال:');
        $apiUrl = WhatsAppSetting::get('api_url');
        
        if ($apiUrl) {
            $this->line("   - رابط API: {$apiUrl}");
            
            // اختبار الاتصال
            try {
                $response = file_get_contents($apiUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5
                    ]
                ]));
                $this->info('   ✅ الاتصال بـ API يعمل');
            } catch (\Exception $e) {
                $this->error('   ❌ فشل الاتصال: ' . $e->getMessage());
            }
        } else {
            $this->error('   ❌ رابط API غير محدد');
        }

        // 4. فحص بنية جدول WhatsApp Messages
        $this->info('4️⃣ جدول whatsapp_messages:');
        $messageCount = DB::table('whatsapp_messages')->count();
        $this->line("   - إجمالي الرسائل: {$messageCount}");
        
        $recentMessages = DB::table('whatsapp_messages')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        if ($recentMessages->count() > 0) {
            $this->line('   - آخر 3 رسائل:');
            foreach ($recentMessages as $msg) {
                $this->line("     * ID: {$msg->id}, النوع: {$msg->message_type}, الحالة: {$msg->status}");
            }
        }

        $this->info('🏁 انتهى الفحص!');
    }
}
