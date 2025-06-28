<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DiagnoseWhatsApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشخيص شامل لمشاكل نظام الواتساب';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 بدء تشخيص نظام الواتساب');
        $this->info('=' . str_repeat('=', 50));
        
        // 1. فحص الإعدادات
        $this->checkSettings();
        
        // 2. فحص قاعدة البيانات
        $this->checkDatabase();
        
        // 3. اختبار الاتصال
        $this->testConnection();
        
        // 4. فحص آخر الرسائل
        $this->checkRecentMessages();
        
        // 5. إنشاء رسالة اختبار
        $this->createTestMessage();
        
        // 6. اختبار الإرسال
        $this->testSending();
        
        $this->info('✅ انتهى التشخيص');
    }
    
    private function checkSettings()
    {
        $this->info("\n📋 1. فحص الإعدادات:");
        
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        $enabled = WhatsAppSetting::get('notifications_enabled', false);
        
        $this->line("   - رابط API: " . ($apiUrl ?: 'غير محدد'));
        $this->line("   - رمز API: " . ($apiToken ? 'موجود' : 'غير محدد'));
        $this->line("   - الإشعارات مفعلة: " . ($enabled ? 'نعم' : 'لا'));
        
        if (!$apiUrl || !$apiToken) {
            $this->error("   ❌ الإعدادات غير مكتملة!");
            return false;
        }
        
        // فحص صيغة URL
        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            $this->error("   ❌ رابط API غير صالح!");
            return false;
        }
        
        $this->info("   ✅ الإعدادات صحيحة");
        return true;
    }
    
    private function checkDatabase()
    {
        $this->info("\n📊 2. فحص قاعدة البيانات:");
        
        try {
            $totalMessages = WhatsAppMessage::count();
            $pendingMessages = WhatsAppMessage::where('status', 'pending')->count();
            $sentMessages = WhatsAppMessage::where('status', 'sent')->count();
            $failedMessages = WhatsAppMessage::where('status', 'failed')->count();
            
            $this->line("   - إجمالي الرسائل: {$totalMessages}");
            $this->line("   - الرسائل المعلقة: {$pendingMessages}");
            $this->line("   - الرسائل المرسلة: {$sentMessages}");
            $this->line("   - الرسائل الفاشلة: {$failedMessages}");
            
            // فحص قائمة الانتظار
            $queueJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            $this->line("   - المهام في القائمة: {$queueJobs}");
            $this->line("   - المهام الفاشلة: {$failedJobs}");
            
            if ($pendingMessages > 20) {
                $this->warn("   ⚠️ يوجد رسائل معلقة كثيرة!");
            }
            
            if ($failedMessages > 0) {
                $this->warn("   ⚠️ يوجد رسائل فاشلة!");
            }
            
            $this->info("   ✅ قاعدة البيانات تعمل");
            return true;
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في قاعدة البيانات: " . $e->getMessage());
            return false;
        }
    }
    
    private function testConnection()
    {
        $this->info("\n🌐 3. اختبار الاتصال:");
        
        $apiUrl = WhatsAppSetting::get('api_url');
        
        if (!$apiUrl) {
            $this->error("   ❌ لا يوجد رابط API");
            return false;
        }
        
        try {
            // اختبار GET أولاً
            $this->line("   🔍 اختبار الوصول للخادم...");
            
            $response = Http::timeout(10)->get($apiUrl);
            $this->line("   - رمز الاستجابة: " . $response->status());
            
            if ($response->successful() || $response->status() === 404) {
                $this->info("   ✅ الخادم يستجيب");
            } else {
                $this->warn("   ⚠️ الخادم يستجيب لكن برمز غير متوقع");
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("   ❌ فشل الاتصال: " . $e->getMessage());
            return false;
        }
    }
    
    private function checkRecentMessages()
    {
        $this->info("\n📨 4. فحص آخر الرسائل:");
        
        try {
            $recentMessages = WhatsAppMessage::latest()->take(5)->get();
            
            if ($recentMessages->isEmpty()) {
                $this->line("   - لا توجد رسائل");
                return;
            }
            
            foreach ($recentMessages as $msg) {
                $error = '';
                if ($msg->metadata && isset($msg->metadata['error'])) {
                    $error = " (خطأ: " . substr($msg->metadata['error'], 0, 50) . "...)";
                }
                
                $this->line("   - ID: {$msg->id} | الحالة: {$msg->status} | {$msg->phone_number}{$error}");
            }
            
            $this->info("   ✅ تم فحص آخر الرسائل");
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في فحص الرسائل: " . $e->getMessage());
        }
    }
    
    private function createTestMessage()
    {
        $this->info("\n🧪 5. إنشاء رسالة اختبار:");
        
        try {
            $message = WhatsAppMessage::createNotification(
                'test',
                null,
                '966501234567',
                'رسالة اختبار من التشخيص - ' . now()->format('Y-m-d H:i:s'),
                'test'
            );
            
            $this->line("   - تم إنشاء رسالة رقم: {$message->id}");
            $this->line("   - الهاتف: {$message->phone_number}");
            $this->line("   - الحالة: {$message->status}");
            
            $this->info("   ✅ تم إنشاء رسالة الاختبار");
            return $message;
            
        } catch (\Exception $e) {
            $this->error("   ❌ فشل إنشاء رسالة الاختبار: " . $e->getMessage());
            return null;
        }
    }
    
    private function testSending()
    {
        $this->info("\n📤 6. اختبار الإرسال المباشر:");
        
        $apiUrl = WhatsAppSetting::get('api_url');
        
        if (!$apiUrl) {
            $this->error("   ❌ لا يوجد رابط API");
            return false;
        }
        
        try {
            $data = [
                'to' => '966501234567',
                'message' => 'اختبار مباشر من التشخيص - ' . now()->format('H:i:s'),
                'type' => 'text'
            ];
            
            $this->line("   📡 إرسال إلى: {$apiUrl}");
            $this->line("   📄 البيانات: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, $data);
            
            $this->line("   📨 رمز الاستجابة: " . $response->status());
            $this->line("   📋 الاستجابة: " . $response->body());
            
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['status']) && $responseData['status']) {
                    $this->info("   ✅ نجح الإرسال المباشر!");
                    if (isset($responseData['messageId'])) {
                        $this->line("   📞 معرف الرسالة: " . $responseData['messageId']);
                    }
                    return true;
                } else {
                    $this->error("   ❌ فشل الإرسال: " . ($responseData['message'] ?? 'غير محدد'));
                    return false;
                }
            } else {
                $this->error("   ❌ فشل الطلب برمز: " . $response->status());
                return false;
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الإرسال: " . $e->getMessage());
            
            // تحليل نوع الخطأ
            if (str_contains($e->getMessage(), 'cURL error 3')) {
                $this->error("   💡 السبب: مشكلة في تنسيق URL");
                $this->line("   🔧 الحل المقترح: تحقق من رابط API واستبدل المسافات");
            } elseif (str_contains($e->getMessage(), 'Connection refused')) {
                $this->error("   💡 السبب: الخادم غير متاح");
                $this->line("   🔧 الحل المقترح: تأكد من تشغيل خادم الواتساب");
            } elseif (str_contains($e->getMessage(), 'timeout')) {
                $this->error("   💡 السبب: انتهت مهلة الاتصال");
                $this->line("   🔧 الحل المقترح: زيادة timeout أو فحص سرعة الاتصال");
            }
            
            return false;
        }
    }
}
