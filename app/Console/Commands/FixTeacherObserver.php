<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppTemplateService;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;

class FixTeacherObserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-teacher-observer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 اختبار وإصلاح Observer المعلمين');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص الإعدادات
        $this->info('1️⃣ فحص الإعدادات:');
        $notifyEnabled = WhatsAppSetting::get('notify_teacher_added', 'false');
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        
        $this->line("   - إشعارات المعلمين: {$notifyEnabled}");
        $this->line("   - رابط API: {$apiUrl}");
        $this->line("   - Token: {$apiToken}");

        if ($notifyEnabled !== 'true') {
            $this->error('❌ إشعارات المعلمين غير مفعلة');
            return;
        }

        // 2. إنشاء معلم جديد
        $mosque = Mosque::first();
        if (!$mosque) {
            $this->error('❌ لا توجد مساجد');
            return;
        }

        $this->info('2️⃣ إنشاء معلم جديد:');
        $teacher = Teacher::create([
            'identity_number' => '1234567' . time(), // رقم ديناميكي لتجنب التكرار
            'name' => 'محمد أحمد التجريبي',
            'nationality' => 'سعودي',
            'phone' => '966501234567',
            'mosque_id' => $mosque->id,
            'job_title' => 'معلم حفظ',
            'task_type' => 'معلم بمكافأة',
            'circle_type' => 'حلقة فردية',
            'work_time' => 'عصر',
            'is_active_user' => true,
            'must_change_password' => true,
        ]);

        $this->info("✅ تم إنشاء المعلم: {$teacher->name}");

        // 3. اختبار Template Service يدوياً
        $this->info('3️⃣ اختبار Template Service:');
        try {
            $message = WhatsAppTemplateService::teacherWelcomeMessage($teacher->name, $teacher->mosque->name);
            $this->info('✅ تم إنشاء رسالة الترحيب:');
            $this->line("   - المحتوى: " . substr($message, 0, 100) . "...");
            
            // حفظ الرسالة في قاعدة البيانات
            $whatsappMessage = WhatsAppMessage::create([
                'user_type' => 'teacher',
                'user_id' => $teacher->id,
                'phone_number' => $teacher->phone,
                'message_type' => 'welcome',
                'content' => $message,
                'status' => 'pending'
            ]);
            
            $this->info("✅ تم حفظ الرسالة في قاعدة البيانات - ID: {$whatsappMessage->id}");
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Template Service: " . $e->getMessage());
        }

        // 4. فحص الرسائل الجديدة
        $this->info('4️⃣ فحص الرسائل:');
        $teacherMessages = WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->get();

        if ($teacherMessages->count() > 0) {
            $this->info("✅ تم إنشاء {$teacherMessages->count()} رسالة للمعلم");
            foreach ($teacherMessages as $msg) {
                $this->line("   - ID: {$msg->id}, النوع: {$msg->message_type}, الحالة: {$msg->status}");
            }
        } else {
            $this->error('❌ لم يتم إنشاء رسائل للمعلم');
        }

        // 5. اختبار إرسال رسالة مباشرة للـ API
        $this->info('5️⃣ اختبار إرسال مباشر للـ API:');
        try {
            $response = Http::post($apiUrl, [
                'action' => 'send_message',
                'phone' => $teacher->phone,
                'message' => "مرحباً {$teacher->name}! تم إنشاء حسابك في النظام بنجاح."
            ]);

            if ($response->successful()) {
                $this->info('✅ تم إرسال رسالة تجريبية مباشرة للـ API');
                $this->line("   - كود الاستجابة: {$response->status()}");
                $this->line("   - الاستجابة: " . $response->body());
            } else {
                $this->error("❌ فشل الإرسال المباشر - كود: {$response->status()}");
                $this->line("   - الخطأ: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الإرسال المباشر: " . $e->getMessage());
        }

        // 6. تنظيف
        $this->info('6️⃣ تنظيف البيانات:');
        $teacher->delete();
        $this->info('✅ تم حذف المعلم التجريبي');

        $this->info('🏁 انتهى الاختبار!');
    }
}
