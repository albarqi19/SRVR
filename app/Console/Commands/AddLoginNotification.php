<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddLoginNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:login-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إضافة ميزة إشعارات تسجيل الدخول للمعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📱 إضافة ميزة إشعارات تسجيل الدخول');
        $this->info('=' . str_repeat('=', 50));

        // إنشاء Event لتسجيل الدخول
        $this->info('1️⃣ إنشاء Event لتسجيل الدخول:');
        $this->createLoginEvent();

        // إنشاء Listener لإرسال إشعار WhatsApp
        $this->info('2️⃣ إنشاء Listener لإرسال إشعار:');
        $this->createLoginListener();

        // تحديث EventServiceProvider
        $this->info('3️⃣ تحديث EventServiceProvider:');
        $this->updateEventServiceProvider();

        // إضافة template رسالة تسجيل الدخول
        $this->info('4️⃣ إضافة template رسالة:');
        $this->addLoginMessageTemplate();

        // إضافة إعداد للتحكم في تفعيل/إلغاء الميزة
        $this->info('5️⃣ إضافة إعدادات التحكم:');
        $this->addLoginNotificationSettings();

        $this->info('🏁 تم إضافة الميزة بنجاح!');
        $this->info('📝 لتفعيل الميزة:');
        $this->line('   1. قم بتشغيل: php artisan migrate');
        $this->line('   2. في إعدادات WhatsApp، فعّل "notify_teacher_login"');
        $this->line('   3. سيتم إرسال رسالة عند كل تسجيل دخول للمعلم');
    }

    private function createLoginEvent()
    {
        $eventPath = app_path('Events/TeacherLoginEvent.php');
        
        if (file_exists($eventPath)) {
            $this->line('   - Event موجود مسبقاً');
            return;
        }

        $eventContent = '<?php

namespace App\Events;

use App\Models\Teacher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherLoginEvent
{
    use Dispatchable, SerializesModels;

    public $teacher;
    public $loginTime;
    public $ipAddress;
    public $userAgent;

    /**
     * Create a new event instance.
     */
    public function __construct(Teacher $teacher, string $ipAddress = null, string $userAgent = null)
    {
        $this->teacher = $teacher;
        $this->loginTime = now();
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}';

        file_put_contents($eventPath, $eventContent);
        $this->info('   ✅ تم إنشاء TeacherLoginEvent');
    }

    private function createLoginListener()
    {
        $listenerPath = app_path('Listeners/SendLoginNotification.php');
        
        if (file_exists($listenerPath)) {
            $this->line('   - Listener موجود مسبقاً');
            return;
        }

        $listenerContent = '<?php

namespace App\Listeners;

use App\Events\TeacherLoginEvent;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppTemplateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLoginNotification
{
    /**
     * Handle the event.
     */
    public function handle(TeacherLoginEvent $event): void
    {
        try {
            Log::info("بدء معالجة إشعار تسجيل دخول للمعلم: {$event->teacher->name}");

            // التحقق من تفعيل الإشعارات
            if (!WhatsAppSetting::notificationsEnabled()) {
                Log::info("إشعارات WhatsApp غير مفعلة");
                return;
            }

            // التحقق من تفعيل إشعارات تسجيل الدخول
            if (!WhatsAppSetting::isNotificationEnabled("notify_teacher_login")) {
                Log::info("إشعارات تسجيل دخول المعلمين غير مفعلة");
                return;
            }

            // التحقق من وجود رقم هاتف
            if (empty($event->teacher->phone)) {
                Log::info("لا يوجد رقم هاتف للمعلم: {$event->teacher->name}");
                return;
            }

            // إنشاء رسالة تسجيل الدخول
            $mosqueName = $event->teacher->mosque ? $event->teacher->mosque->name : "غير محدد";
            $message = WhatsAppTemplateService::teacherLoginMessage(
                $event->teacher->name,
                $mosqueName,
                $event->loginTime->format("Y-m-d H:i")
            );

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($event->teacher->phone);

            // حفظ الرسالة في قاعدة البيانات
            $whatsAppMessage = WhatsAppMessage::create([
                "user_type" => "teacher",
                "user_id" => $event->teacher->id,
                "phone_number" => $phoneNumber,
                "content" => $message,
                "message_type" => "notification",
                "status" => "pending",
                "metadata" => json_encode([
                    "teacher_id" => $event->teacher->id,
                    "teacher_name" => $event->teacher->name,
                    "mosque_name" => $mosqueName,
                    "login_time" => $event->loginTime,
                    "ip_address" => $event->ipAddress,
                    "event_type" => "login"
                ])
            ]);

            Log::info("تم إنشاء رسالة تسجيل الدخول في قاعدة البيانات - ID: {$whatsAppMessage->id}");

            // إرسال الرسالة عبر API
            $apiUrl = WhatsAppSetting::get("api_url");
            if ($apiUrl) {
                $response = Http::timeout(10)->post($apiUrl, [
                    "action" => "send_message",
                    "phone" => str_replace("+", "", $phoneNumber),
                    "message" => $message
                ]);

                if ($response->successful()) {
                    $whatsAppMessage->update([
                        "status" => "sent",
                        "sent_at" => now(),
                        "response_data" => $response->json()
                    ]);
                    Log::info("تم إرسال إشعار تسجيل الدخول للمعلم: {$event->teacher->name}");
                } else {
                    $whatsAppMessage->update([
                        "status" => "failed",
                        "error_message" => "HTTP Error: " . $response->status() . " - " . $response->body()
                    ]);
                    Log::error("فشل إرسال إشعار تسجيل الدخول للمعلم: {$event->teacher->name}");
                }
            }

        } catch (\Exception $e) {
            Log::error("خطأ في إرسال إشعار تسجيل دخول المعلم: {$event->teacher->name} - {$e->getMessage()}");
        }
    }

    private function formatPhoneNumber(string $phoneNumber): string
    {
        $phone = preg_replace("/[^\d+]/", "", $phoneNumber);
        
        if (!str_starts_with($phone, "+") && !str_starts_with($phone, "966")) {
            if (str_starts_with($phone, "05")) {
                $phone = "+966" . substr($phone, 1);
            } else {
                $phone = "+966" . $phone;
            }
        }
        
        return $phone;
    }
}';

        file_put_contents($listenerPath, $listenerContent);
        $this->info('   ✅ تم إنشاء SendLoginNotification');
    }

    private function updateEventServiceProvider()
    {
        $providerPath = app_path('Providers/EventServiceProvider.php');
        $content = file_get_contents($providerPath);

        // التحقق من وجود Event مسبقاً
        if (strpos($content, 'TeacherLoginEvent') !== false) {
            $this->line('   - EventServiceProvider محدث مسبقاً');
            return;
        }

        // إضافة Event و Listener
        $newMapping = "        \\App\\Events\\TeacherLoginEvent::class => [\n            \\App\\Listeners\\SendLoginNotification::class,\n        ],";
        
        $pattern = '/protected \$listen = \[(.*?)\];/s';
        $replacement = "protected \$listen = [\n{$newMapping}\n        Registered::class => [\n            SendEmailVerificationNotification::class,\n        ],\n    ];";
        
        $updatedContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($providerPath, $updatedContent);
        
        $this->info('   ✅ تم تحديث EventServiceProvider');
    }

    private function addLoginMessageTemplate()
    {
        $servicePath = app_path('Services/WhatsAppTemplateService.php');
        $content = file_get_contents($servicePath);

        // التحقق من وجود الدالة مسبقاً
        if (strpos($content, 'teacherLoginMessage') !== false) {
            $this->line('   - Template موجود مسبقاً');
            return;
        }

        // إضافة دالة template جديدة
        $newTemplate = '
    /**
     * Get login notification message for teacher.
     *
     * @param string $teacherName
     * @param string $mosqueName
     * @param string $loginTime
     * @return string
     */
    public static function teacherLoginMessage(string $teacherName, string $mosqueName, string $loginTime): string
    {
        return "🔐 تسجيل دخول جديد\n\n" .
               "الأستاذ: {$teacherName}\n" .
               "المسجد: {$mosqueName}\n" .
               "الوقت: {$loginTime}\n\n" .
               "مرحباً بك في نظام مركز القرآن الكريم 📚";
    }';

        // البحث عن آخر دالة وإضافة الدالة الجديدة قبل إغلاق الكلاس
        $pattern = '/(\s+)(\}\s*$)/';
        $replacement = $newTemplate . '$1$2';
        
        $updatedContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($servicePath, $updatedContent);
        
        $this->info('   ✅ تم إضافة template رسالة تسجيل الدخول');
    }

    private function addLoginNotificationSettings()
    {
        $this->info('   - سيتم إضافة إعداد "notify_teacher_login" يدوياً في إعدادات WhatsApp');
        $this->line('   - أو يمكن إضافته عبر: INSERT INTO whatsapp_settings (key, value) VALUES ("notify_teacher_login", "true")');
    }
}
