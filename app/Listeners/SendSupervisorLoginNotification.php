<?php

namespace App\Listeners;

use App\Events\SupervisorLoginEvent;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppTemplateService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SendSupervisorLoginNotification
{
    /**
     * Handle the event.
     */
    public function handle(SupervisorLoginEvent $event): void
    {
        try {
            Log::info("بدء معالجة إشعار تسجيل دخول للمشرف: {$event->supervisor->name}");

            // التحقق من تفعيل الإشعارات
            if (!WhatsAppSetting::notificationsEnabled()) {
                Log::info("إشعارات WhatsApp غير مفعلة");
                return;
            }

            // التحقق من تفعيل إشعارات تسجيل الدخول للمشرفين
            if (!WhatsAppSetting::isNotificationEnabled("notify_supervisor_login")) {
                Log::info("إشعارات تسجيل دخول المشرفين غير مفعلة");
                return;
            }

            // التحقق من وجود رقم هاتف
            if (empty($event->supervisor->phone)) {
                Log::info("لا يوجد رقم هاتف للمشرف: {$event->supervisor->name}");
                return;
            }

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($event->supervisor->phone);

            // حماية من الرسائل المتكررة لنفس رقم الهاتف خلال آخر 10 دقائق
            $cacheKey = "supervisor_login_notification_sent_" . md5($phoneNumber);
            if (Cache::has($cacheKey)) {
                Log::info("تم إرسال إشعار تسجيل دخول مسبقاً لرقم الهاتف: {$phoneNumber} خلال آخر 10 دقائق - تجاهل الطلب");
                return;
            }

            // التحقق من عدم إرسال رسالة مكررة خلال آخر 5 دقائق
            $recentMessage = WhatsAppMessage::where('user_type', 'supervisor')
                ->where('user_id', $event->supervisor->id)
                ->where('message_type', 'notification')
                ->where('metadata->event_type', 'login')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($recentMessage) {
                Log::info("تم إرسال إشعار تسجيل دخول مؤخراً للمشرف: {$event->supervisor->name}. تجاهل الطلب المكرر.");
                return;
            }

            // إنشاء رسالة تسجيل الدخول للمشرف
            $message = WhatsAppTemplateService::supervisorLoginMessage(
                $event->supervisor->name,
                $event->loginTime->format("Y-m-d H:i")
            );

            // حفظ الرسالة في قاعدة البيانات
            $whatsAppMessage = WhatsAppMessage::create([
                "user_type" => "supervisor",
                "user_id" => $event->supervisor->id,
                "phone_number" => $phoneNumber,
                "content" => $message,
                "message_type" => "notification",
                "status" => "pending",
                "metadata" => json_encode([
                    "supervisor_id" => $event->supervisor->id,
                    "supervisor_name" => $event->supervisor->name,
                    "login_time" => $event->loginTime,
                    "ip_address" => $event->ipAddress,
                    "event_type" => "login"
                ])
            ]);

            Log::info("تم إنشاء رسالة تسجيل الدخول للمشرف في قاعدة البيانات - ID: {$whatsAppMessage->id}");

            // إرسال الرسالة عبر API
            $this->sendWhatsAppMessage($whatsAppMessage);

            // تعيين الـ cache لمنع الرسائل المتكررة لمدة 10 دقائق
            Cache::put($cacheKey, true, now()->addMinutes(10));

            Log::info("تم إرسال إشعار تسجيل دخول للمشرف: {$event->supervisor->name} بنجاح");

        } catch (\Exception $e) {
            Log::error("خطأ في إرسال إشعار تسجيل دخول المشرف: " . $e->getMessage());
            Log::error("تفاصيل الخطأ: " . $e->getTraceAsString());
        }
    }

    /**
     * إرسال رسالة واتساب عبر API
     */
    private function sendWhatsAppMessage(WhatsAppMessage $message): void
    {
        try {
            $apiUrl = WhatsAppSetting::getValue('api_url');
            $apiToken = WhatsAppSetting::getValue('api_token');

            if (empty($apiUrl) || empty($apiToken)) {
                Log::error("إعدادات API الواتساب غير مكتملة");
                $message->update(['status' => 'failed', 'error_message' => 'إعدادات API غير مكتملة']);
                return;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, [
                    'phone' => $message->phone_number,
                    'message' => $message->content,
                ]);

            if ($response->successful()) {
                $message->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'api_response' => $response->json()
                ]);
                Log::info("تم إرسال رسالة واتساب بنجاح للمشرف - Message ID: {$message->id}");
            } else {
                $message->update([
                    'status' => 'failed',
                    'error_message' => $response->body()
                ]);
                Log::error("فشل في إرسال رسالة واتساب للمشرف - Message ID: {$message->id}, Error: " . $response->body());
            }

        } catch (\Exception $e) {
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            Log::error("خطأ في إرسال رسالة واتساب للمشرف: " . $e->getMessage());
        }
    }

    /**
     * تنسيق رقم الهاتف
     */
    private function formatPhoneNumber(string $phone): string
    {
        // إزالة جميع الأحرف غير الرقمية
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // إضافة رمز الدولة السعودية إذا لم يكن موجوداً
        if (!str_starts_with($phone, '966')) {
            if (str_starts_with($phone, '0')) {
                $phone = '966' . substr($phone, 1);
            } else {
                $phone = '966' . $phone;
            }
        }
        
        return $phone;
    }
}
