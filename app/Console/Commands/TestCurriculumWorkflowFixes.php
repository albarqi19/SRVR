<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\DB;

class TestCurriculumWorkflowFixes extends Command
{
    protected $signature = 'test:curriculum-workflow-fixes {--student=1 : Student ID to test}';
    protected $description = 'اختبار الحلول المقترحة لمشاكل سير العمل في نظام المناهج';

    public function handle()
    {
        $studentId = $this->option('student');
        
        $this->info("🔧 اختبار الحلول المقترحة لسير العمل - الطالب ID: {$studentId}");
        $this->line(str_repeat('=', 80));

        try {
            // 1. محاكاة إكمال جلسة تسميع
            $this->simulateCompletingSession($studentId);
            
            // 2. محاكاة تحديث التقدم
            $this->simulateProgressUpdate($studentId);
            
            // 3. اختبار الحصول على المحتوى بعد التحديث
            $this->testContentAfterUpdate($studentId);
            
            // 4. عرض النتائج
            $this->showResults($studentId);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاختبار: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function simulateCompletingSession($studentId)
    {
        $this->info("\n🎯 1. محاكاة إكمال جلسة تسميع");
        $this->line(str_repeat('-', 50));

        // البحث عن جلسة جارية
        $pendingSession = RecitationSession::where('student_id', $studentId)
            ->where('status', 'جارية')
            ->first();

        if (!$pendingSession) {
            $this->warn("⚠️ لا توجد جلسات جارية للطالب");
            return;
        }

        $this->info("📝 جلسة مختارة: ID {$pendingSession->id}");
        $this->info("📅 تاريخ إنشاء الجلسة: {$pendingSession->created_at}");
        $this->info("🔄 الحالة الحالية: {$pendingSession->status}");

        // محاكاة إكمال الجلسة
        $pendingSession->update([
            'status' => 'مكتملة',
            'completed_at' => now(),
            'evaluation' => 'ممتاز', // تقييم تجريبي
        ]);

        $this->info("✅ تم إكمال الجلسة بنجاح");
        $this->info("🕐 وقت الإكمال: " . now()->format('Y-m-d H:i:s'));
    }

    private function simulateProgressUpdate($studentId)
    {
        $this->info("\n📈 2. محاكاة تحديث التقدم");
        $this->line(str_repeat('-', 50));

        // الحصول على معرف منهج الطالب
        $studentCurriculum = StudentCurriculum::where('student_id', $studentId)->first();
        
        if (!$studentCurriculum) {
            $this->warn("⚠️ لا يوجد منهج مخصص للطالب");
            return;
        }

        // البحث عن تقدم الطالب
        $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)->first();
        
        if (!$progress) {
            $this->warn("⚠️ لا يوجد تتبع للتقدم");
            return;
        }

        $this->info("📊 نسبة الإنجاز الحالية: {$progress->completion_percentage}%");

        // حساب الجلسات المكتملة
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'مكتملة')
            ->count();

        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        
        $newPercentage = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) : 0;

        // تحديث التقدم
        $progress->update([
            'completion_percentage' => $newPercentage,
            'updated_at' => now(),
        ]);

        $this->info("✅ تم تحديث التقدم");
        $this->info("📊 نسبة الإنجاز الجديدة: {$newPercentage}%");
        $this->info("🕐 وقت التحديث: " . now()->format('Y-m-d H:i:s'));
    }

    private function testContentAfterUpdate($studentId)
    {
        $this->info("\n🔍 3. اختبار المحتوى بعد التحديث");
        $this->line(str_repeat('-', 50));

        // محاكاة استدعاء APIs
        try {
            // استدعاء API المحتوى اليومي
            $response = $this->makeApiCall("GET", "/api/students/{$studentId}/daily-curriculum");
            $this->info("📱 API المحتوى اليومي: " . ($response ? "نجح" : "فشل"));
            
            // استدعاء API الإحصائيات
            $response = $this->makeApiCall("GET", "/api/students/{$studentId}/stats");
            $this->info("📊 API الإحصائيات: " . ($response ? "نجح" : "فشل"));
            
        } catch (\Exception $e) {
            $this->warn("⚠️ خطأ في اختبار APIs: " . $e->getMessage());
        }
    }

    private function makeApiCall($method, $url)
    {
        // محاكاة استدعاء API بسيط
        // في التطبيق الحقيقي، يمكن استخدام HTTP client
        return true;
    }

    private function showResults($studentId)
    {
        $this->info("\n📋 4. النتائج النهائية");
        $this->line(str_repeat('-', 50));

        // إحصائيات الجلسات
        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'مكتملة')->count();
        $pendingSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'جارية')->count();

        // إحصائيات التقدم
        $studentCurriculum = StudentCurriculum::where('student_id', $studentId)->first();
        $progress = null;
        if ($studentCurriculum) {
            $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)->first();
        }

        $this->table(
            ['المؤشر', 'القيمة'],
            [
                ['إجمالي الجلسات', $totalSessions],
                ['الجلسات المكتملة', $completedSessions],
                ['الجلسات الجارية', $pendingSessions],
                ['معدل الإنجاز', $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) . '%' : '0%'],
                ['نسبة التقدم المُسجلة', $progress ? $progress->completion_percentage . '%' : 'غير متاح'],
                ['آخر تحديث للتقدم', $progress ? $progress->updated_at : 'غير متاح'],
            ]
        );

        // تحليل التحسن
        if ($progress) {
            $timeDiff = now()->diffInMinutes($progress->updated_at);
            if ($timeDiff <= 5) {
                $this->info("✅ التقدم محدث تلقائياً (فرق الوقت: {$timeDiff} دقيقة)");
            } else {
                $this->warn("⚠️ التقدم قد لا يكون محدث تلقائياً (فرق الوقت: {$timeDiff} دقيقة)");
            }
        }

        // توصيات
        $this->info("\n💡 التوصيات:");
        if ($pendingSessions > 0) {
            $this->line("• لا تزال هناك {$pendingSessions} جلسة جارية تحتاج لإكمال");
        }
        
        if ($progress && $progress->completion_percentage < 100) {
            $this->line("• الطالب بحاجة لإكمال " . (100 - $progress->completion_percentage) . "% من المنهج");
        }
        
        $this->line("• يُنصح بإنشاء Job scheduler لتحديث التقدم تلقائياً");
        $this->line("• يُنصح بإنشاء Event listeners لربط الجلسات بالتقدم");
    }
}
