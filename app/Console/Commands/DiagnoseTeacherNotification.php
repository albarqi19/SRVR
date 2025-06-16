<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;
use App\Observers\TeacherObserver;
use App\Services\WhatsAppTemplateService;
use Illuminate\Support\Facades\Event;

class DiagnoseTeacherNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:teacher-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشخيص مشكلة إشعارات المعلمين الجدد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 تشخيص مشكلة إشعارات المعلمين الجدد');
        $this->info('=' . str_repeat('=', 60));

        // 1. فحص تسجيل Observer
        $this->info('1️⃣ فحص تسجيل TeacherObserver:');
        try {
            $observers = app('events')->getListeners('eloquent.created: App\Models\Teacher');
            if (empty($observers)) {
                $this->error('   ❌ TeacherObserver غير مسجل للحدث created');
            } else {
                $this->info('   ✅ TeacherObserver مسجل بنجاح');
                $this->line('   عدد المستمعين: ' . count($observers));
            }
        } catch (\Exception $e) {
            $this->warn('   ⚠️ لا يمكن فحص Observer: ' . $e->getMessage());
        }

        // 2. فحص إعدادات WhatsApp
        $this->info('2️⃣ فحص إعدادات WhatsApp:');
        $settings = WhatsAppSetting::all();
        if ($settings->isEmpty()) {
            $this->error('   ❌ لا توجد إعدادات WhatsApp');
        } else {
            foreach ($settings as $setting) {
                $value = strlen($setting->value) > 50 ? substr($setting->value, 0, 50) . '...' : $setting->value;
                $this->line("   {$setting->key}: {$value}");
            }
        }

        // 3. اختبار WhatsAppTemplateService
        $this->info('3️⃣ اختبار WhatsAppTemplateService:');
        try {
            $service = app(WhatsAppTemplateService::class);
            if ($service) {
                $this->info('   ✅ WhatsAppTemplateService متوفر');
                
                // اختبار إنشاء محتوى الرسالة
                $testTeacher = new Teacher([
                    'name' => 'اختبار المعلم',
                    'phone' => '0530000000'
                ]);
                
                $content = $service->teacherWelcomeMessage('اختبار المعلم', 'مسجد الاختبار');
                if ($content) {
                    $this->info('   ✅ تم إنشاء محتوى الرسالة بنجاح');
                    $this->line('   عينة من المحتوى: ' . substr($content, 0, 100) . '...');
                } else {
                    $this->error('   ❌ فشل في إنشاء محتوى الرسالة');
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ خطأ في WhatsAppTemplateService: ' . $e->getMessage());
        }

        // 4. اختبار مباشر للـ Observer
        $this->info('4️⃣ اختبار مباشر لـ TeacherObserver:');
        try {
            $mosque = Mosque::first();
            if (!$mosque) {
                $mosque = Mosque::create([
                    'name' => 'مسجد التشخيص',
                    'neighborhood' => 'حي التشخيص',
                    'location_lat' => '24.7136',
                    'location_long' => '46.6753',
                ]);
            }

            $messagesBefore = WhatsAppMessage::count();
            $this->line("   رسائل WhatsApp قبل الإنشاء: {$messagesBefore}");

            // إنشاء معلم مع مراقبة الأحداث
            $this->line('   إنشاء معلم جديد...');
            
            $teacher = new Teacher([
                'identity_number' => '9876543210',
                'name' => 'معلم التشخيص',
                'nationality' => 'سعودي',
                'phone' => '0530111222',
                'mosque_id' => $mosque->id,
                'job_title' => 'معلم حفظ',
                'task_type' => 'معلم بمكافأة',
                'circle_type' => 'حلقة فردية',
                'work_time' => 'عصر',
                'is_active_user' => true,
                'must_change_password' => true,
            ]);

            // استدعاء Observer مباشرةً
            $observer = new TeacherObserver();
            $observer->created($teacher);

            $messagesAfter = WhatsAppMessage::count();
            $this->line("   رسائل WhatsApp بعد استدعاء Observer: {$messagesAfter}");
            
            if ($messagesAfter > $messagesBefore) {
                $this->info('   ✅ Observer يعمل بشكل صحيح');
                $newMessage = WhatsAppMessage::latest()->first();
                $this->line("   الرسالة الجديدة: {$newMessage->message_type}");
                $this->line("   الهاتف: {$newMessage->phone_number}");
            } else {
                $this->error('   ❌ Observer لا ينشئ رسائل');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ خطأ في اختبار Observer: ' . $e->getMessage());
        }

        // 5. اختبار حفظ النموذج
        $this->info('5️⃣ اختبار حفظ النموذج مع Observer:');
        try {
            $messagesBefore = WhatsAppMessage::count();
            
            $teacher = Teacher::create([
                'identity_number' => '1111222233',
                'name' => 'معلم الحفظ التلقائي',
                'nationality' => 'سعودي',
                'phone' => '0530333444',
                'mosque_id' => $mosque->id,
                'job_title' => 'معلم حفظ',
                'task_type' => 'معلم بمكافأة',
                'circle_type' => 'حلقة فردية',
                'work_time' => 'عصر',
                'is_active_user' => true,
                'must_change_password' => true,
            ]);

            sleep(1); // انتظار قصير

            $messagesAfter = WhatsAppMessage::count();
            $this->line("   رسائل قبل: {$messagesBefore}, بعد: {$messagesAfter}");
            
            if ($messagesAfter > $messagesBefore) {
                $this->info('   ✅ Observer يعمل تلقائياً مع create()');
            } else {
                $this->error('   ❌ Observer لا يعمل تلقائياً');
            }

            // تنظيف
            $teacher->delete();

        } catch (\Exception $e) {
            $this->error('   ❌ خطأ في اختبار الحفظ: ' . $e->getMessage());
        }

        $this->info('🏁 انتهى التشخيص!');
    }
}
