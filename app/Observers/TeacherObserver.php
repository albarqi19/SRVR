<?php

namespace App\Observers;

use App\Models\Teacher;
use App\Services\WhatsAppService;
use App\Services\WhatsAppTemplateService;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Hash;

class TeacherObserver
{
    /**
     * Handle the Teacher "created" event.
     */
    public function created(Teacher $teacher): void
    {
        // إنشاء كلمة مرور عشوائية للمعلم الجديد
        if (empty($teacher->password)) {
            $randomPassword = $teacher->generateRandomPassword();
            $teacher->password = Hash::make($randomPassword);
            $teacher->must_change_password = true;
            $teacher->is_active_user = true;
            $teacher->saveQuietly(); // حفظ بدون إطلاق events إضافية
            
            // تسجيل في اللوج
            \Log::info("تم إنشاء كلمة مرور للمعلم: {$teacher->name} - ID: {$teacher->id}");
        }

        // إرسال إشعار الواتساب عند إضافة معلم جديد
        $this->sendWelcomeNotification($teacher);
    }

    /**
     * Handle the Teacher "updated" event.
     */
    public function updated(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "deleted" event.
     */
    public function deleted(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "restored" event.
     */
    public function restored(Teacher $teacher): void
    {
        //
    }

    /**
     * Handle the Teacher "force deleted" event.
     */
    public function forceDeleted(Teacher $teacher): void
    {
        //
    }

    /**
     * Send welcome notification to new teacher via WhatsApp.
     */
    private function sendWelcomeNotification(Teacher $teacher): void
    {
        try {
            \Log::info("بدء عملية إرسال إشعار الترحيب للمعلم: {$teacher->name} - ID: {$teacher->id}");
            
            // التحقق من تفعيل الإشعارات العامة
            if (!WhatsAppSetting::notificationsEnabled()) {
                \Log::info("إشعارات WhatsApp غير مفعلة");
                return;
            }

            // التحقق من تفعيل إشعارات المعلمين الجدد
            if (!WhatsAppSetting::isNotificationEnabled('notify_teacher_added')) {
                \Log::info("إشعارات المعلمين الجدد غير مفعلة");
                return;
            }

            // التحقق من وجود رقم هاتف
            if (empty($teacher->phone)) {
                \Log::info("لا يوجد رقم هاتف للمعلم: {$teacher->name}");
                return;
            }

            \Log::info("جميع الشروط مستوفاة، بدء إنشاء الرسالة...");

            // الحصول على إعدادات API
            $apiUrl = WhatsAppSetting::get('api_url');
            $apiToken = WhatsAppSetting::get('api_token');
            
            if (empty($apiUrl) || empty($apiToken)) {
                \Log::warning('إعدادات WhatsApp API غير مكتملة');
                return;
            }

            // إنشاء رسالة الترحيب مع كلمة المرور
            $mosqueName = $teacher->mosque ? $teacher->mosque->name : 'غير محدد';
            
            // التحقق من وجود كلمة مرور واضحة
            $plainPassword = $teacher->plain_password;
            if (!$plainPassword) {
                // إذا لم تكن كلمة المرور موجودة، نولد واحدة جديدة
                $plainPassword = Teacher::generateRandomPassword();
                $teacher->plain_password = $plainPassword;
                $teacher->password = Hash::make($plainPassword);
                $teacher->saveQuietly();
                \Log::info("تم توليد كلمة مرور جديدة للمعلم: {$teacher->name}");
            }
            
            $message = WhatsAppTemplateService::teacherWelcomeWithPasswordMessage(
                $teacher->name,
                $mosqueName,
                $plainPassword,
                $teacher->identity_number
            );

            \Log::info("محتوى الرسالة المُولدة: " . substr($message, 0, 100) . "...");

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($teacher->phone);

            // إنشاء رسالة في قاعدة البيانات
            $whatsAppMessage = \App\Models\WhatsAppMessage::create([
                'user_type' => 'teacher',
                'user_id' => $teacher->id,
                'phone_number' => $phoneNumber,
                'content' => $message,
                'message_type' => 'notification',
                'status' => 'pending',
                'metadata' => json_encode([
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->name,
                    'mosque_name' => $mosqueName,
                    'has_password' => !empty($plainPassword),
                    'template_type' => 'teacher_welcome_with_password'
                ])
            ]);

            \Log::info("تم إنشاء رسالة WhatsApp في قاعدة البيانات - ID: {$whatsAppMessage->id}");

            // الإرسال المباشر بالتنسيق الصحيح
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post($apiUrl, [
                    'action' => 'send_message',
                    'phone' => str_replace('+', '', $phoneNumber), // إزالة + من بداية الرقم
                    'message' => $message
                ]);

            if ($response->successful()) {
                $whatsAppMessage->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_data' => $response->json()
                ]);
                
                \Log::info("تم إرسال إشعار الترحيب للمعلم: {$teacher->name}");
            } else {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()
                ]);
                
                \Log::error("فشل إرسال إشعار الترحيب للمعلم: {$teacher->name} - HTTP {$response->status()}");
            }

        } catch (\Exception $e) {
            // تحديث حالة الرسالة في حالة الخطأ
            if (isset($whatsAppMessage)) {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            \Log::error("خطأ في إرسال إشعار الترحيب للمعلم: {$teacher->name} - {$e->getMessage()}");
        }
    }

    /**
     * Format phone number for WhatsApp.
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // إزالة المسافات والرموز غير المرغوبة
        $phone = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // إضافة رمز الدولة للسعودية إذا لم يكن موجوداً
        if (!str_starts_with($phone, '+') && !str_starts_with($phone, '966')) {
            if (str_starts_with($phone, '05')) {
                $phone = '+966' . substr($phone, 1);
            } else {
                $phone = '+966' . $phone;
            }
        }
        
        return $phone;
    }
}
