<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Curriculum;
use App\Models\StudentCurriculum;
use App\Models\CurriculumPlan;
use App\Models\StudentCurriculumProgress;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\DB;

class AnalyzeCurriculumWorkflow extends Command
{
    protected $signature = 'curriculum:analyze-workflow {--student=1 : Student ID to analyze}';
    protected $description = 'تحليل سير العمل في نظام المناهج والعلاقة بين التسميع والتقدم';

    public function handle()
    {
        $studentId = $this->option('student');
        
        $this->info("🔍 تحليل سير العمل لنظام المناهج - الطالب ID: {$studentId}");
        $this->line(str_repeat('=', 80));

        try {
            // 1. تحليل بيانات الطالب
            $this->analyzeStudentData($studentId);
            
            // 2. تحليل المنهج المخصص
            $this->analyzeStudentCurriculum($studentId);
            
            // 3. تحليل الخطط اليومية
            $this->analyzeDailyPlans($studentId);
            
            // 4. تحليل جلسات التسميع
            $this->analyzeRecitationSessions($studentId);
            
            // 5. تحليل التقدم
            $this->analyzeProgressTracking($studentId);
            
            // 6. تحليل العلاقات والسير
            $this->analyzeWorkflowRelations($studentId);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في التحليل: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function analyzeStudentData($studentId)
    {
        $this->info("\n📋 1. تحليل بيانات الطالب");
        $this->line(str_repeat('-', 50));

        $student = Student::find($studentId);
        if (!$student) {
            $this->error("الطالب غير موجود!");
            return;
        }

        $this->table(
            ['الحقل', 'القيمة'],
            [
                ['الاسم', $student->name],
                ['الإيميل', $student->email],
                ['تاريخ الإنشاء', $student->created_at],
                ['آخر تحديث', $student->updated_at],
            ]
        );
    }

    private function analyzeStudentCurriculum($studentId)
    {
        $this->info("\n📚 2. تحليل المنهج المخصص للطالب");
        $this->line(str_repeat('-', 50));

        $studentCurricula = StudentCurriculum::where('student_id', $studentId)
            ->with(['curriculum', 'student'])
            ->get();

        if ($studentCurricula->isEmpty()) {
            $this->warn("⚠️ لا يوجد منهج مخصص لهذا الطالب");
            return;
        }

        foreach ($studentCurricula as $sc) {
            $this->table(
                ['الحقل', 'القيمة'],
                [
                    ['اسم المنهج', $sc->curriculum->name ?? 'غير محدد'],
                    ['نوع المنهج', $sc->curriculum->type ?? 'غير محدد'],
                    ['حالة التخصيص', $sc->status ?? 'غير محدد'],
                    ['تاريخ البدء', $sc->start_date ?? 'غير محدد'],
                    ['تاريخ الانتهاء المتوقع', $sc->expected_end_date ?? 'غير محدد'],
                    ['تاريخ التخصيص', $sc->created_at],
                ]
            );
        }
    }    private function analyzeDailyPlans($studentId)
    {
        $this->info("\n📅 3. تحليل الخطط اليومية");
        $this->line(str_repeat('-', 50));

        $plans = DB::table('curriculum_plans')
            ->join('student_curricula', 'curriculum_plans.curriculum_id', '=', 'student_curricula.curriculum_id')
            ->where('student_curricula.student_id', $studentId)
            ->select('curriculum_plans.*')
            ->orderBy('curriculum_plans.id')
            ->get();

        if ($plans->isEmpty()) {
            $this->warn("⚠️ لا توجد خطط يومية لهذا الطالب");
            return;
        }

        $this->info("📊 إجمالي الخطط اليومية: " . $plans->count());

        // عرض أول 5 خطط كعينة
        $samplePlans = $plans->take(5);
        $tableData = [];
        
        foreach ($samplePlans as $plan) {
            $tableData[] = [
                $plan->id ?? 'غير محدد',
                $plan->name ?? 'غير محدد',
                $plan->content ?? 'غير محدد',
                $plan->plan_type ?? 'غير محدد',
                $plan->expected_days ?? 'غير محدد',
            ];
        }

        $this->table(
            ['المعرف', 'الاسم', 'المحتوى', 'النوع', 'الأيام المتوقعة'],
            $tableData
        );

        if ($plans->count() > 5) {
            $this->info("... وهناك " . ($plans->count() - 5) . " خطة إضافية");
        }
    }

    private function analyzeRecitationSessions($studentId)
    {
        $this->info("\n🎤 4. تحليل جلسات التسميع");
        $this->line(str_repeat('-', 50));

        $sessions = RecitationSession::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        if ($sessions->isEmpty()) {
            $this->warn("⚠️ لا توجد جلسات تسميع لهذا الطالب");
            return;
        }

        $this->info("📊 إجمالي جلسات التسميع: " . RecitationSession::where('student_id', $studentId)->count());
        
        // تحليل الإحصائيات
        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'completed')->count();
        $pendingSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'pending')->count();

        $this->table(
            ['النوع', 'العدد'],
            [
                ['إجمالي الجلسات', $totalSessions],
                ['الجلسات المكتملة', $completedSessions],
                ['الجلسات المعلقة', $pendingSessions],
                ['معدل الإنجاز', $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) . '%' : '0%'],
            ]
        );        // عرض آخر الجلسات
        $this->info("\n📋 آخر 5 جلسات:");
        $recentTableData = [];
        
        foreach ($sessions->take(5) as $session) {
            $content = 'غير محدد';
            if ($session->start_surah_number && $session->start_verse) {
                $content = "سورة {$session->start_surah_number} آية {$session->start_verse}";
                if ($session->end_surah_number && $session->end_verse) {
                    $content .= " إلى سورة {$session->end_surah_number} آية {$session->end_verse}";
                }
            }
            
            $recentTableData[] = [
                $session->recitation_type ?? 'غير محدد',
                $content,
                $session->status ?? 'غير محدد',
                $session->grade ?? 'غير محدد',
                $session->created_at->format('Y-m-d H:i'),
            ];
        }

        $this->table(
            ['النوع', 'المحتوى', 'الحالة', 'النتيجة', 'التاريخ'],
            $recentTableData
        );
    }    private function analyzeProgressTracking($studentId)
    {
        $this->info("\n📈 5. تحليل تتبع التقدم");
        $this->line(str_repeat('-', 50));

        // الحصول على student_curriculum_id أولاً
        $studentCurriculumIds = StudentCurriculum::where('student_id', $studentId)
            ->pluck('id')
            ->toArray();

        if (empty($studentCurriculumIds)) {
            $this->warn("⚠️ لا يوجد منهج مخصص للطالب");
            return;
        }

        $progress = StudentCurriculumProgress::whereIn('student_curriculum_id', $studentCurriculumIds)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($progress->isEmpty()) {
            $this->warn("⚠️ لا يوجد تتبع للتقدم لهذا الطالب");
            return;
        }

        foreach ($progress as $p) {
            $this->table(
                ['الحقل', 'القيمة'],
                [
                    ['معرف منهج الطالب', $p->student_curriculum_id ?? 'غير محدد'],
                    ['معرف خطة المنهج', $p->curriculum_plan_id ?? 'غير محدد'],
                    ['تاريخ البدء', $p->start_date ?? 'غير محدد'],
                    ['تاريخ الإكمال', $p->completion_date ?? 'غير محدد'],
                    ['الحالة', $p->status ?? 'غير محدد'],
                    ['نسبة الإنجاز', $p->completion_percentage ?? 'غير محدد'],
                    ['ملاحظات المعلم', $p->teacher_notes ?? 'غير محدد'],
                    ['آخر تحديث', $p->updated_at],
                ]
            );
        }
    }    private function analyzeWorkflowRelations($studentId)
    {
        $this->info("\n🔗 6. تحليل العلاقات وسير العمل");
        $this->line(str_repeat('-', 50));

        // تحليل العلاقة بين الجلسات والتقدم
        $this->info("🔍 تحليل العلاقة بين جلسات التسميع وتقدم المنهج:");

        // آخر جلسة تسميع
        $lastSession = RecitationSession::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();

        // آخر تقدم
        $studentCurriculumIds = StudentCurriculum::where('student_id', $studentId)
            ->pluck('id')
            ->toArray();

        $lastProgress = null;
        if (!empty($studentCurriculumIds)) {
            $lastProgress = StudentCurriculumProgress::whereIn('student_curriculum_id', $studentCurriculumIds)
                ->orderBy('updated_at', 'desc')
                ->first();
        }

        if ($lastSession && $lastProgress) {
            $this->table(
                ['المؤشر', 'آخر جلسة تسميع', 'آخر تقدم مسجل'],
                [
                    ['التاريخ', $lastSession->created_at->format('Y-m-d H:i'), $lastProgress->updated_at->format('Y-m-d H:i')],                    ['المحتوى/الحالة', $lastSession->recitation_type ?? 'غير محدد', $lastProgress->status ?? 'غير محدد'],
                    ['النسبة/النتيجة', $lastSession->status ?? 'غير محدد', ($lastProgress->completion_percentage ?? 'غير محدد') . '%'],
                ]
            );

            // تحديد ما إذا كان هناك تزامن
            $timeDiff = abs($lastSession->created_at->diffInMinutes($lastProgress->updated_at));
            
            if ($timeDiff <= 5) {
                $this->info("✅ يبدو أن التقدم يتم تحديثه تلقائياً عند التسميع (فرق الوقت: {$timeDiff} دقيقة)");
            } else {
                $this->warn("⚠️ قد لا يكون هناك تزامن تلقائي (فرق الوقت: {$timeDiff} دقيقة)");
            }
        } elseif ($lastSession && !$lastProgress) {
            $this->warn("⚠️ يوجد جلسات تسميع ولكن لا يوجد تتبع للتقدم");
        } elseif (!$lastSession && $lastProgress) {
            $this->warn("⚠️ يوجد تتبع للتقدم ولكن لا توجد جلسات تسميع");
        } else {
            $this->warn("⚠️ لا يوجد جلسات تسميع ولا تتبع للتقدم");
        }        // تحليل توزيع أنواع الجلسات
        $sessionTypes = RecitationSession::where('student_id', $studentId)
            ->select('recitation_type', DB::raw('count(*) as count'))
            ->groupBy('recitation_type')
            ->get();

        if ($sessionTypes->isNotEmpty()) {
            $this->info("\n📊 توزيع أنواع جلسات التسميع:");
            $typeTableData = [];
            foreach ($sessionTypes as $type) {
                $typeTableData[] = [$type->recitation_type ?? 'غير محدد', $type->count];
            }
            $this->table(['نوع الجلسة', 'العدد'], $typeTableData);
        }

        // تحليل حالات الجلسات
        $sessionStatuses = RecitationSession::where('student_id', $studentId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        if ($sessionStatuses->isNotEmpty()) {
            $this->info("\n📊 توزيع حالات جلسات التسميع:");
            $statusTableData = [];
            foreach ($sessionStatuses as $status) {
                $statusTableData[] = [$status->status ?? 'غير محدد', $status->count];
            }
            $this->table(['حالة الجلسة', 'العدد'], $statusTableData);
        }

        // اقتراحات للتحسين
        $this->info("\n💡 اقتراحات وملاحظات:");
        $this->line("• تحقق من وجود آلية تلقائية لتحديث التقدم عند إكمال التسميع");
        $this->line("• تأكد من وجود جدولة مهام لتحديث الخطط اليومية");
        $this->line("• راجع الربط بين curriculum_plans وجلسات التسميع");
        $this->line("• تحقق من وجود إشعارات للطلاب عند تحديث المنهج");
        $this->line("• لاحظ أن جميع الجلسات في حالة 'جارية' - قد تحتاج آلية لإكمالها");
    }
}
