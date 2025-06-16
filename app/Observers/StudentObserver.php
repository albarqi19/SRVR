<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        // توليد كلمة مرور عشوائية للطالب الجديد
        if (empty($student->password)) {
            $randomPassword = $student->generateRandomPassword();
            $student->password = Hash::make($randomPassword);
            $student->must_change_password = true;
            $student->is_active_user = true;
            $student->saveQuietly(); // حفظ بدون إطلاق events إضافية
            
            // تسجيل في اللوج
            Log::info("تم إنشاء كلمة مرور للطالب: {$student->name} - ID: {$student->id}");
        }

        // إرسال إشعار WhatsApp للطالب وولي الأمر
        $this->sendWelcomeNotification($student);
    }    /**
     * إرسال إشعار ترحيب للطالب الجديد
     */
    private function sendWelcomeNotification(Student $student): void
    {
        try {
            Log::info("بدء عملية إرسال إشعار الترحيب للطالب: {$student->name} - ID: {$student->id}");
            
            // التحقق من تفعيل الإشعارات العامة
            if (!\App\Models\WhatsAppSetting::notificationsEnabled()) {
                Log::info("إشعارات WhatsApp غير مفعلة");
                return;
            }

            // التحقق من تفعيل إشعارات الطلاب الجدد
            if (!\App\Models\WhatsAppSetting::isNotificationEnabled('notify_student_added')) {
                Log::info("إشعارات الطلاب الجدد غير مفعلة");
                return;
            }

            // الحصول على إعدادات API
            $apiUrl = \App\Models\WhatsAppSetting::get('api_url');
            $apiToken = \App\Models\WhatsAppSetting::get('api_token');
            
            if (empty($apiUrl) || empty($apiToken)) {
                Log::warning('إعدادات WhatsApp API غير مكتملة');
                return;
            }

            // إرسال إشعار للطالب إذا كان لديه رقم هاتف
            if (!empty($student->phone)) {
                $this->sendDirectStudentMessage($student, $apiUrl);
            }
            
            // إرسال إشعار لولي الأمر إذا كان لديه رقم هاتف
            if (!empty($student->guardian_phone)) {
                $this->sendDirectParentMessage($student, $apiUrl);
            }
            
        } catch (\Exception $e) {
            Log::error("خطأ في إرسال إشعار WhatsApp للطالب {$student->name}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        //
    }

    /**
     * إرسال رسالة مباشرة للطالب
     */
    private function sendDirectStudentMessage(Student $student, string $apiUrl): void
    {
        try {
            // الحصول على اسم الحلقة
            $circleName = $student->quranCircle?->name ?? 'غير محدد';
            
            // إنشاء محتوى الرسالة
            $message = \App\Services\WhatsAppTemplateService::studentWelcomeMessage(
                $student->name,
                $circleName
            );

            Log::info("محتوى رسالة الطالب المُولدة: " . substr($message, 0, 100) . "...");

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($student->phone);

            // إنشاء رسالة في قاعدة البيانات
            $whatsAppMessage = \App\Models\WhatsAppMessage::create([
                'user_type' => 'student',
                'user_id' => $student->id,
                'phone_number' => $phoneNumber,
                'content' => $message,
                'message_type' => 'notification',
                'status' => 'pending',
                'metadata' => json_encode([
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'circle_name' => $circleName,
                    'template_type' => 'student_welcome'
                ])
            ]);

            Log::info("تم إنشاء رسالة WhatsApp للطالب في قاعدة البيانات - ID: {$whatsAppMessage->id}");

            // الإرسال المباشر
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post($apiUrl, [
                    'action' => 'send_message',
                    'phone' => str_replace('+', '', $phoneNumber),
                    'message' => $message
                ]);

            if ($response->successful()) {
                $whatsAppMessage->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_data' => $response->json()
                ]);
                
                Log::info("تم إرسال إشعار الترحيب للطالب: {$student->name}");
            } else {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()
                ]);
                
                Log::error("فشل إرسال إشعار الترحيب للطالب: {$student->name} - HTTP {$response->status()}");
            }

        } catch (\Exception $e) {
            Log::error("خطأ في إرسال رسالة الطالب: " . $e->getMessage());
            if (isset($whatsAppMessage)) {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * إرسال رسالة مباشرة لولي الأمر
     */
    private function sendDirectParentMessage(Student $student, string $apiUrl): void
    {
        try {
            // الحصول على اسم الحلقة
            $circleName = $student->quranCircle?->name ?? 'غير محدد';
            
            // إنشاء رسالة إشعار لولي الأمر
            $message = "تم تسجيل ابنك/ابنتك {$student->name} بنجاح في حلقة {$circleName}. نسأل الله أن يبارك في حفظه ويجعله من حملة كتابه الكريم.";

            // إنشاء محتوى الرسالة
            $messageContent = \App\Services\WhatsAppTemplateService::parentNotificationMessage(
                $student->name,
                $message,
                $student->guardian_name ?? ''
            );

            Log::info("محتوى رسالة ولي الأمر المُولدة: " . substr($messageContent, 0, 100) . "...");

            // تنسيق رقم الهاتف
            $phoneNumber = $this->formatPhoneNumber($student->guardian_phone);

            // إنشاء رسالة في قاعدة البيانات
            $whatsAppMessage = \App\Models\WhatsAppMessage::create([
                'user_type' => 'parent',
                'user_id' => $student->id,
                'phone_number' => $phoneNumber,
                'content' => $messageContent,
                'message_type' => 'notification',
                'status' => 'pending',
                'metadata' => json_encode([
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'guardian_name' => $student->guardian_name,
                    'circle_name' => $circleName,
                    'template_type' => 'parent_notification'
                ])
            ]);

            Log::info("تم إنشاء رسالة WhatsApp لولي الأمر في قاعدة البيانات - ID: {$whatsAppMessage->id}");

            // الإرسال المباشر
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post($apiUrl, [
                    'action' => 'send_message',
                    'phone' => str_replace('+', '', $phoneNumber),
                    'message' => $messageContent
                ]);

            if ($response->successful()) {
                $whatsAppMessage->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_data' => $response->json()
                ]);
                
                Log::info("تم إرسال إشعار لولي أمر الطالب: {$student->name}");
            } else {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => 'HTTP Error: ' . $response->status() . ' - ' . $response->body()
                ]);
                
                Log::error("فشل إرسال إشعار لولي أمر الطالب: {$student->name} - HTTP {$response->status()}");
            }

        } catch (\Exception $e) {
            Log::error("خطأ في إرسال رسالة ولي الأمر: " . $e->getMessage());
            if (isset($whatsAppMessage)) {
                $whatsAppMessage->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * تنسيق رقم الهاتف
     */
    private function formatPhoneNumber(string $phone): string
    {
        // إزالة المسافات والأحرف غير المرغوب فيها
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // إذا كان الرقم يبدأ بـ 05، استبدله بـ +9665
        if (preg_match('/^05/', $phone)) {
            $phone = '+9665' . substr($phone, 2);
        }
        // إذا كان الرقم يبدأ بـ 9665، أضف +
        elseif (preg_match('/^9665/', $phone)) {
            $phone = '+' . $phone;
        }
        // إذا لم يبدأ بـ +، أضف +966
        elseif (!preg_match('/^\+/', $phone)) {
            $phone = '+966' . $phone;
        }
        
        return $phone;
    }
}
