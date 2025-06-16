<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppTemplateService;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\DB;

class FinalFixTeacherNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'final:fix-teacher-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'الإصلاح النهائي لنظام إشعارات المعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 الإصلاح النهائي لنظام إشعارات المعلمين');
        $this->info('=' . str_repeat('=', 50));

        // 1. إصلاح TeacherObserver
        $this->info('1️⃣ إصلاح TeacherObserver:');
        $this->fixTeacherObserver();

        // 2. اختبار النظام
        $this->info('2️⃣ اختبار النظام بعد الإصلاح:');
        $this->testSystem();

        $this->info('🏁 تم الإصلاح بنجاح!');
    }

    private function fixTeacherObserver()
    {
        $observerPath = app_path('Observers/TeacherObserver.php');
        
        if (!file_exists($observerPath)) {
            $this->error('❌ ملف TeacherObserver غير موجود');
            return;
        }

        $content = file_get_contents($observerPath);
        
        // إصلاح message_type من 'welcome' إلى 'notification' 
        $oldPattern = "'message_type' => 'welcome'";
        $newPattern = "'message_type' => 'notification'";
        
        if (strpos($content, $oldPattern) !== false) {
            $content = str_replace($oldPattern, $newPattern, $content);
            file_put_contents($observerPath, $content);
            $this->info('✅ تم إصلاح message_type في TeacherObserver');
        } else {
            $this->line('ℹ️  message_type يبدو أنه صحيح بالفعل');
        }

        // التحقق من أن الدالة تستخدم القيم الصحيحة
        if (strpos($content, 'sendWelcomeNotification') !== false) {
            $this->info('✅ دالة sendWelcomeNotification موجودة');
        } else {
            $this->warn('⚠️  دالة sendWelcomeNotification غير موجودة');
        }
    }

    private function testSystem()
    {
        // إنشاء معلم تجريبي
        $mosque = Mosque::first();
        if (!$mosque) {
            $this->error('❌ لا توجد مساجد');
            return;
        }

        $messagesBefore = WhatsAppMessage::count();
        $this->line("   - الرسائل قبل الإنشاء: {$messagesBefore}");

        // إنشاء معلم جديد
        $teacher = Teacher::create([
            'identity_number' => '999' . time(),
            'name' => 'المعلم التجريبي النهائي',
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

        $this->info("✅ تم إنشاء المعلم: ID {$teacher->id}");

        // انتظار للسماح للـ Observer بالعمل
        sleep(1);

        $messagesAfter = WhatsAppMessage::count();
        $newMessages = $messagesAfter - $messagesBefore;
        $this->line("   - الرسائل بعد الإنشاء: {$messagesAfter}");
        $this->line("   - رسائل جديدة: {$newMessages}");

        // فحص الرسائل الخاصة بالمعلم
        $teacherMessages = WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->get();

        if ($teacherMessages->count() > 0) {
            $this->info("✅ تم إنشاء {$teacherMessages->count()} رسالة للمعلم");
            foreach ($teacherMessages as $msg) {
                $this->line("   - ID: {$msg->id}, النوع: {$msg->message_type}, الحالة: {$msg->status}");
                $this->line("   - المحتوى: " . substr($msg->content, 0, 50) . "...");
            }
        } else {
            $this->error('❌ لم يتم إنشاء رسائل للمعلم');
            
            // اختبار يدوي لإنشاء الرسالة
            $this->line('⚙️  محاولة إنشاء رسالة يدوياً:');
            try {
                $message = WhatsAppTemplateService::teacherWelcomeMessage($teacher->name, $teacher->mosque->name);
                
                $whatsappMessage = WhatsAppMessage::create([
                    'user_type' => 'teacher',
                    'user_id' => $teacher->id,
                    'phone_number' => $teacher->phone,
                    'message_type' => 'notification', // استخدام القيمة الصحيحة
                    'content' => $message,
                    'status' => 'pending'
                ]);
                
                $this->info("✅ تم إنشاء رسالة يدوياً - ID: {$whatsappMessage->id}");
                
            } catch (\Exception $e) {
                $this->error("❌ فشل الإنشاء اليدوي: " . $e->getMessage());
            }
        }

        // تنظيف
        $teacher->delete();
        $this->info('🧹 تم حذف المعلم التجريبي');
    }
}
