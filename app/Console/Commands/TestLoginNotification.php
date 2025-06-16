<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;
use App\Events\TeacherLoginEvent;
use Illuminate\Support\Facades\DB;

class TestLoginNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار إشعارات تسجيل دخول المعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 اختبار إشعارات تسجيل دخول المعلمين');
        $this->info('=' . str_repeat('=', 50));

        // 1. إضافة إعداد تسجيل الدخول
        $this->info('1️⃣ إضافة إعداد تسجيل الدخول:');
        $setting = WhatsAppSetting::updateOrCreate(
            ['setting_key' => 'notify_teacher_login'],
            ['setting_value' => 'true', 'description' => 'إرسال إشعار عند تسجيل دخول المعلم']
        );
        $this->info("✅ تم إضافة إعداد notify_teacher_login: {$setting->setting_value}");

        // 2. الحصول على معلم للاختبار
        $this->info('2️⃣ الحصول على معلم للاختبار:');
        $teacher = Teacher::with('mosque')->first();
        
        if (!$teacher) {
            $this->error('❌ لا يوجد معلمين في النظام');
            return;
        }

        $this->info("✅ تم العثور على المعلم: {$teacher->name}");
        $this->line("   - المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد'));
        $this->line("   - الهاتف: {$teacher->phone}");

        // 3. فحص الرسائل قبل الحدث
        $messagesBefore = WhatsAppMessage::count();
        $this->info("3️⃣ الرسائل قبل الحدث: {$messagesBefore}");

        // 4. إطلاق حدث تسجيل الدخول
        $this->info('4️⃣ إطلاق حدث تسجيل الدخول:');
        try {
            $event = new TeacherLoginEvent(
                $teacher,
                '192.168.1.100', // IP تجريبي
                'Mozilla/5.0 (Test Browser)' // User Agent تجريبي
            );
            
            event($event);
            $this->info('✅ تم إطلاق TeacherLoginEvent بنجاح');
            
            // انتظار قصير للمعالجة
            sleep(2);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في إطلاق الحدث: " . $e->getMessage());
            return;
        }

        // 5. فحص الرسائل بعد الحدث
        $messagesAfter = WhatsAppMessage::count();
        $newMessages = $messagesAfter - $messagesBefore;
        $this->info("5️⃣ الرسائل بعد الحدث: {$messagesAfter}");
        $this->line("   - رسائل جديدة: {$newMessages}");

        // 6. فحص رسائل تسجيل الدخول للمعلم
        $loginMessages = WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->whereJsonContains('metadata->event_type', 'login')
            ->get();

        if ($loginMessages->count() > 0) {
            $this->info("6️⃣ رسائل تسجيل الدخول ({$loginMessages->count()}):");
            foreach ($loginMessages as $msg) {
                $this->line("   ✅ رسالة ID: {$msg->id}");
                $this->line("      - الحالة: {$msg->status}");
                $this->line("      - الوقت: {$msg->created_at}");
                $this->line("      - المحتوى: " . substr($msg->content, 0, 50) . "...");
                $this->line("      ---");
            }
        } else {
            $this->error('6️⃣ ❌ لم يتم إنشاء رسائل تسجيل دخول');
        }

        // 7. اختبار Template مباشرة
        $this->info('7️⃣ اختبار Template مباشرة:');
        try {
            $message = \App\Services\WhatsAppTemplateService::teacherLoginMessage(
                $teacher->name,
                $teacher->mosque ? $teacher->mosque->name : 'غير محدد',
                now()->format('Y-m-d H:i')
            );
            
            $this->info('✅ تم إنشاء رسالة Template:');
            $this->line("   - المحتوى:");
            $this->line("     " . str_replace("\n", "\n     ", $message));
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Template: " . $e->getMessage());
        }

        // 8. فحص الإعدادات المطلوبة
        $this->info('8️⃣ فحص الإعدادات:');
        $this->line("   - إشعارات عامة: " . (WhatsAppSetting::notificationsEnabled() ? 'مفعلة' : 'معطلة'));
        $this->line("   - إشعارات تسجيل دخول: " . (WhatsAppSetting::isNotificationEnabled('notify_teacher_login') ? 'مفعلة' : 'معطلة'));
        
        $this->info('🏁 انتهى الاختبار!');
    }
}
