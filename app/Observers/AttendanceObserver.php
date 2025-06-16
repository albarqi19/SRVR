<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

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
    }

    /**
     * إرسال إشعار الحضور عبر WhatsApp
     */
    protected function sendAttendanceNotification(Attendance $attendance): void
    {
        try {
            // التحقق من أن المسجل هو طالب
            if ($attendance->attendable_type !== Student::class) {
                return; // إشعارات الحضور فقط للطلاب
            }

            $student = $attendance->attendable;
            if (!$student) {
                Log::warning("طالب غير موجود للحضور ID: {$attendance->id}");
                return;
            }

            $whatsAppService = app(WhatsAppService::class);
            
            // إرسال إشعار الحضور للطالب وولي الأمر
            $whatsAppService->sendAttendanceNotification(
                $student,
                $attendance->status,
                $attendance->date->format('Y-m-d')
            );

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
    {
        //
    }

    /**
     * Handle the Attendance "force deleted" event.
     */
    public function forceDeleted(Attendance $attendance): void
    {
        //
    }
}
