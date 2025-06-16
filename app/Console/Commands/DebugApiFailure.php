<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;

class DebugApiFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:api-failure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشخيص فشل API وإصلاحه';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 تشخيص فشل API');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص الرسائل الفاشلة
        $this->info('1️⃣ فحص الرسائل الفاشلة:');
        $failedMessages = WhatsAppMessage::where('status', 'failed')
            ->latest()
            ->limit(3)
            ->get();

        if ($failedMessages->count() > 0) {
            foreach ($failedMessages as $msg) {
                $this->line("   - ID: {$msg->id}");
                $this->line("     الهاتف: {$msg->phone_number}");
                $this->line("     الخطأ: " . ($msg->error_message ?? 'غير محدد'));
                $this->line("     ---");
            }
        }

        // 2. مقارنة إعدادات API مع Observer
        $this->info('2️⃣ مقارنة إعدادات API:');
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        
        $this->line("   - API URL من الإعدادات: {$apiUrl}");
        $this->line("   - API Token من الإعدادات: {$apiToken}");

        // 3. اختبار API بنفس طريقة Observer
        $this->info('3️⃣ اختبار API بطريقة Observer:');
        
        try {
            $testPhone = '+966501234567';
            $testMessage = 'رسالة اختبار من تشخيص API';
            
            $this->line("   - إرسال إلى: {$testPhone}");
            $this->line("   - الرسالة: {$testMessage}");
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'phone' => $testPhone,
                    'message' => $testMessage,
                    'type' => 'welcome'
                ]);

            $this->line("   - كود الاستجابة: {$response->status()}");
            $this->line("   - نجح الإرسال: " . ($response->successful() ? 'نعم' : 'لا'));
            $this->line("   - الاستجابة: " . $response->body());
            
            if (!$response->successful()) {
                $this->error("❌ فشل الإرسال - كود: {$response->status()}");
                $this->line("   - تفاصيل الخطأ: " . $response->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاتصال: " . $e->getMessage());
        }

        // 4. اختبار بدون Headers
        $this->info('4️⃣ اختبار بدون Authorization Header:');
        
        try {
            $response = Http::timeout(10)
                ->post($apiUrl, [
                    'action' => 'send_message', // كما جربنا سابقاً
                    'phone' => '966501234567',
                    'message' => 'اختبار بدون headers'
                ]);

            $this->line("   - كود الاستجابة: {$response->status()}");
            $this->line("   - نجح الإرسال: " . ($response->successful() ? 'نعم' : 'لا'));
            $this->line("   - الاستجابة: " . $response->body());

        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاتصال: " . $e->getMessage());
        }

        // 5. اقتراح الإصلاح
        $this->info('5️⃣ اقتراح الإصلاح:');
        $this->line('   بناءً على النتائج، يبدو أن المشكلة في:');
        $this->line('   1. طريقة إرسال البيانات للـ API');
        $this->line('   2. Headers المطلوبة');
        $this->line('   3. تنسيق البيانات');
        
        $this->info('🏁 انتهى التشخيص!');
    }
}
