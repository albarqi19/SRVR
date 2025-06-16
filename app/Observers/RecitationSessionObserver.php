<?php

namespace App\Observers;

use App\Models\RecitationSession;
use App\Services\DailyCurriculumTrackingService;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\Log;

class RecitationSessionObserver
{
    protected $dailyTrackingService;

    public function __construct(DailyCurriculumTrackingService $dailyTrackingService)
    {
        $this->dailyTrackingService = $dailyTrackingService;
    }

    /**
     * Handle the RecitationSession "created" event.
     */
    public function created(RecitationSession $session): void
    {
        Log::info('جلسة تسميع جديدة تم إنشاؤها', [
            'session_id' => $session->session_id,
            'student_id' => $session->student_id,
            'status' => $session->status
        ]);

        // إذا كانت الجلسة مكتملة، حدث التقدم
        if ($session->status === 'مكتملة') {
            $this->updateStudentProgress($session);
        }
    }

    /**
     * Handle the RecitationSession "updated" event.
     */
    public function updated(RecitationSession $session): void
    {
        // إذا تغيرت الحالة إلى مكتملة، حدث التقدم
        if ($session->isDirty('status') && $session->status === 'مكتملة') {
            Log::info('جلسة تسميع اكتملت', [
                'session_id' => $session->session_id,
                'student_id' => $session->student_id,
                'grade' => $session->grade
            ]);

            $this->updateStudentProgress($session);
        }

        // إذا تغيرت الدرجة وكانت الجلسة مكتملة
        if ($session->isDirty('grade') && $session->status === 'مكتملة') {
            $this->updateStudentProgress($session);
        }
    }

    /**
     * تحديث تقدم الطالب بناءً على جلسة التسميع
     */
    protected function updateStudentProgress(RecitationSession $session): void
    {
        try {
            // البحث عن أو إنشاء سجل التقدم
            $progress = $this->findOrCreateStudentProgress($session);
            
            if ($progress) {
                // تحديث آخر جلسة تسميع
                $progress->update([
                    'last_recitation_session_id' => $session->id,
                    'last_recitation_at' => $session->updated_at,
                    'daily_tracking_updated_at' => now()
                ]);

                // تحديث التقدم باستخدام الخدمة
                $this->dailyTrackingService->updateProgressAfterSession($session->id);

                Log::info('تم تحديث تقدم الطالب', [
                    'student_id' => $session->student_id,
                    'progress_id' => $progress->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث تقدم الطالب', [
                'session_id' => $session->session_id,
                'student_id' => $session->student_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * البحث عن أو إنشاء سجل تقدم الطالب
     */
    protected function findOrCreateStudentProgress(RecitationSession $session): ?StudentProgress
    {
        // البحث عن سجل التقدم النشط
        $progress = StudentProgress::where('student_id', $session->student_id)
            ->where('curriculum_id', $session->curriculum_id)
            ->whereIn('status', ['not_started', 'in_progress'])
            ->first();

        // إذا لم يوجد، أنشئ سجل جديد
        if (!$progress && $session->curriculum_id) {
            $progress = StudentProgress::create([
                'student_id' => $session->student_id,
                'curriculum_id' => $session->curriculum_id,
                'status' => 'in_progress',
                'recitation_status' => 'active',
                'started_at' => now(),
                'performance_score' => $session->grade ?? 0,
                'recitation_attempts' => 1,
                'last_recitation_at' => $session->updated_at,
                'last_recitation_session_id' => $session->id,
                'daily_tracking_updated_at' => now()
            ]);

            Log::info('تم إنشاء سجل تقدم جديد للطالب', [
                'student_id' => $session->student_id,
                'curriculum_id' => $session->curriculum_id,
                'progress_id' => $progress->id
            ]);
        }

        return $progress;
    }
}
