<?php

namespace App\Listeners;

use App\Events\TeacherLoginEvent;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppTemplateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($event->teacher->phone);

            // حماية من الرسائل المتكررة لنفس رقم الهاتف خلال آخر 10 دقائق (بغض النظر عن نوع المحاولة)
            $cacheKey = "login_notification_sent_" . md5($phoneNumber);
            if (Cache::has($cacheKey)) {
                Log::info("تم إرسال إشعار تسجيل دخول مسبقاً لرقم الهاتف: {$phoneNumber} خلال آخر 10 دقائق - تجاهل الطلب");
                return;
            }

            // التحقق من عدم إرسال رسالة مكررة خلال آخر 5 دقائق
            $recentMessage = WhatsAppMessage::where('user_type', 'teacher')
                ->where('user_id', $event->teacher->id)
                ->where('message_type', 'notification')
                ->where('metadata->event_type', 'login')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($recentMessage) {
                Log::info("تم إرسال إشعار تسجيل دخول مؤخراً للمعلم: {$event->teacher->name}. تجاهل الطلب المكرر.");
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
                    
                    // تعيين cache لمنع الرسائل المتكررة لمدة 10 دقائق
                    Cache::put($cacheKey, true, now()->addMinutes(10));
                    
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
}