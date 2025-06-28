<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        // إرسال إشعارات WhatsApp للحضور/الغياب
        $this->sendAttendanceNotification($attendance);
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        // إرسال إشعار فقط إذا تغيرت حالة الحضور
        if ($attendance->wasChanged('status')) {
            $this->sendAttendanceNotification($attendance);
        }
    }    /**
     * إرسال إشعار الحضور عبر WhatsApp
     */
    protected function sendAttendanceNotification(Attendance $attendance): void
    {
        try {
            // التحقق من تفعيل الإشعارات
            if (!WhatsAppSetting::notificationsEnabled()) {
                Log::info("إشعارات WhatsApp غير مفعلة");
                return;
            }

            // التحقق من تفعيل إشعارات الحضور تحديداً
            if (!WhatsAppSetting::isNotificationEnabled('notify_attendance')) {
                Log::info("إشعارات الحضور/الغياب غير مفعلة");
                return;
            }

            // التحقق من أن المسجل هو طالب
            if ($attendance->attendable_type !== Student::class) {
                return; // إشعارات الحضور فقط للطلاب
            }

            $student = $attendance->attendable;
            if (!$student) {
                Log::warning("طالب غير موجود للحضور ID: {$attendance->id}");
                return;
            }

            // إرسال إشعار للطالب إذا كان لديه رقم هاتف
            if ($student->phone) {
                $this->sendDirectMessage(
                    'student',
                    $student->id,
                    $student->phone,
                    $student->name,
                    $attendance->status,
                    $attendance->date->format('Y-m-d')
                );
            }

            // إرسال إشعار لولي الأمر إذا كان الطالب غائباً
            if ($student->guardian_phone && $attendance->status === 'غائب') {
                $this->sendDirectMessage(
                    'parent',
                    $student->id,
                    $student->guardian_phone,
                    $student->name,
                    $attendance->status,
                    $attendance->date->format('Y-m-d'),
                    $student->guardian_name
                );
            }

            Log::info("تم إرسال إشعار حضور WhatsApp للطالب: {$student->name}، الحالة: {$attendance->status}");

        } catch (\Exception $e) {
            Log::error("خطأ في إرسال إشعار حضور WhatsApp: " . $e->getMessage(), [
                'attendance_id' => $attendance->id,
                'student_id' => $attendance->attendable_id ?? null,
                'status' => $attendance->status
            ]);
        }
    }

    /**
     * إرسال رسالة مباشرة عبر API (مثل تسجيل الدخول)
     */
    protected function sendDirectMessage(
        string $userType,
        int $userId,
        string $phoneNumber,
        string $studentName,
        string $status,
        string $date,
        string $guardianName = null
    ): void {
        try {
            // إنشاء محتوى الرسالة
            $message = $this->createAttendanceMessage($studentName, $status, $date, $userType, $guardianName);
            
            // تنسيق رقم الهاتف
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            // حفظ الرسالة في قاعدة البيانات
            $whatsAppMessage = WhatsAppMessage::create([
                "user_type" => $userType,
                "user_id" => $userId,
                "phone_number" => $formattedPhone,
                "content" => $message,
                "message_type" => "attendance",
                "status" => "pending",
                "metadata" => json_encode([
                    "student_id" => $userId,
                    "student_name" => $studentName,
                    "status" => $status,
                    "date" => $date
                ])
            ]);            // إرسال الرسالة مباشرة عبر API (نفس طريقة تسجيل الدخول)
            $apiUrl = WhatsAppSetting::get("api_url");
            if ($apiUrl) {
                $response = Http::timeout(10)->post($apiUrl, [
                    "action" => "send_message",
                    "phone" => str_replace("+", "", $formattedPhone),
                    "message" => $message
                ]);

                if ($response->successful()) {
                    $whatsAppMessage->update([
                        "status" => "sent",
                        "sent_at" => now(),
                        "response_data" => $response->json()
                    ]);
                    Log::info("تم إرسال رسالة الحضور مباشرة للرقم: {$formattedPhone}");                } else {
                    $whatsAppMessage->update([
                        "status" => "failed",
                        "error_message" => "HTTP Error: " . $response->status() . " - " . $response->body()
                    ]);
                    Log::error("فشل إرسال رسالة الحضور للرقم: {$formattedPhone}");
                }
            } else {
                $whatsAppMessage->update([
                    "status" => "failed",
                    "error_message" => "API URL غير مُعرَّف"
                ]);
                Log::error("رابط API غير مُعرَّف لإرسال رسالة الحضور");
            }

        } catch (\Exception $e) {
            Log::error("خطأ في الإرسال المباشر للرسالة: " . $e->getMessage());
        }
    }

    /**
     * إنشاء محتوى رسالة الحضور
     */
    protected function createAttendanceMessage(
        string $studentName,
        string $status,
        string $date,
        string $userType,
        string $guardianName = null
    ): string {
        if ($userType === 'parent') {
            $greeting = $guardianName ? "حفظكم الله أ/ {$guardianName}" : "حفظكم الله";
            return "{$greeting}\n\n" .
                   "🔔 إشعار غياب الطالب\n\n" .
                   "👤 الطالب: {$studentName}\n" .
                   "📅 التاريخ: {$date}\n" .
                   "❌ الحالة: {$status}\n\n" .
                   "نرجو المتابعة مع إدارة الحلقة 🤲";
        }

        // رسالة للطالب
        switch ($status) {
            case 'غائب':
                return "تنبيه غياب ⚠️\n\n" .
                       "الطالب: {$studentName}\n" .
                       "📅 التاريخ: {$date}\n" .
                       "🕌 الحلقة: الحلقة\n\n" .
                       "نتطلع لحضورك في المرة القادمة بإذن الله 🤲";
            case 'حاضر':
                return "تأكيد حضور ✅\n\n" .
                       "الطالب: {$studentName}\n" .
                       "📅 التاريخ: {$date}\n" .
                       "🕌 الحلقة: الحلقة\n\n" .
                       "بارك الله فيك وحفظك الله 🤲";
            case 'متأخر':
                return "تنبيه تأخير ⏰\n\n" .
                       "الطالب: {$studentName}\n" .
                       "📅 التاريخ: {$date}\n" .
                       "⏰ الحالة: متأخر\n\n" .
                       "نرجو الحرص على الحضور في الوقت المناسب 🤲";
            case 'مأذون':
                return "إذن غياب 📝\n\n" .
                       "الطالب: {$studentName}\n" .
                       "📅 التاريخ: {$date}\n" .
                       "📝 الحالة: مأذون\n\n" .
                       "نتطلع لحضورك في المرة القادمة بإذن الله 🤲";
            default:
                return "إشعار حضور 📋\n\n" .
                       "الطالب: {$studentName}\n" .
                       "📅 التاريخ: {$date}\n" .
                       "📋 الحالة: {$status}\n\n" .
                       "جزاك الله خيراً 🤲";
        }
    }

    /**
     * تنسيق رقم الهاتف
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        $phone = preg_replace("/[^\d+]/", "", $phoneNumber);
        
        if (!str_starts_with($phone, "+") && !str_starts_with($phone, "966")) {
            if (str_starts_with($phone, "05")) {
                $phone = "+966" . substr($phone, 1);
            } else {
                $phone = "+966" . $phone;
            }
        }        return $phone;
    }

    /**
     * Handle the Attendance "deleted" event.
     */
    public function deleted(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "restored" event.
     */
    public function restored(Attendance $attendance): void
    {        //
    }

    /**
     * Handle the Attendance "force deleted" event.
     */
    public function forceDeleted(Attendance $attendance): void
    {
        //
    }
}
