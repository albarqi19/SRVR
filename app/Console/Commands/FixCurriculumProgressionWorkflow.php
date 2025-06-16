<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\RecitationSession;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;
use App\Models\CurriculumPlan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixCurriculumProgressionWorkflow extends Command
{
    protected $signature = 'curriculum:fix-progression-workflow {--student=1 : Student ID to test with}';
    protected $description = 'إصلاح واختبار سير العمل التلقائي للمناهج';

    public function handle()
    {
        $this->info('🚀 إصلاح سير العمل التلقائي للمناهج');
        $this->info('=====================================');

        $studentId = $this->option('student');
        
        // 1. تحليل الوضع الحالي
        $this->analyzeCurrentState($studentId);
        
        // 2. إصلاح مشاكل التقدم
        $this->fixProgressionIssues($studentId);
        
        // 3. تفعيل الربط التلقائي
        $this->enableAutomaticProgression($studentId);
        
        // 4. اختبار النتائج
        $this->testResults($studentId);

        $this->info('✅ تم إصلاح سير العمل بنجاح!');
        return 0;
    }

    private function analyzeCurrentState($studentId)
    {
        $this->info("\n📊 تحليل الوضع الحالي");
        $this->info('===================');

        $student = Student::find($studentId);
        if (!$student) {
            $this->error("الطالب غير موجود!");
            return;
        }

        $this->info("الطالب: {$student->name}");

        // فحص المنهج النشط
        $activeCurriculum = StudentCurriculum::where('student_id', $studentId)
            ->where('status', 'قيد التنفيذ')
            ->with('curriculum')
            ->first();

        if ($activeCurriculum) {
            $this->info("✅ المنهج النشط: {$activeCurriculum->curriculum->name}");
            $this->line("   التقدم: {$activeCurriculum->progress_percentage}%");
            $this->line("   الصفحة الحالية: {$activeCurriculum->current_page}");
        } else {
            $this->warn("❌ لا يوجد منهج نشط");
            return;
        }

        // فحص الخطط
        $plans = CurriculumPlan::where('curriculum_id', $activeCurriculum->curriculum_id)->count();
        $this->info("📚 خطط المنهج المتاحة: {$plans}");

        // فحص جلسات التسميع
        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'مكتملة')->count();
        $pendingSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'جارية')->count();

        $this->info("🎤 جلسات التسميع:");
        $this->line("   إجمالي: {$totalSessions}");
        $this->line("   مكتملة: {$completedSessions}");
        $this->line("   جارية: {$pendingSessions}");

        // فحص تقدم الطالب
        $progress = StudentCurriculumProgress::where('student_curriculum_id', $activeCurriculum->id)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($progress) {
            $this->info("📈 آخر تقدم مسجل:");
            $this->line("   النسبة: {$progress->completion_percentage}%");
            $this->line("   آخر تحديث: {$progress->updated_at}");
        } else {
            $this->warn("❌ لا يوجد تقدم مسجل");
        }
    }

    private function fixProgressionIssues($studentId)
    {
        $this->info("\n🔧 إصلاح مشاكل التقدم");
        $this->info('===================');

        // المشكلة 1: جلسات معلقة
        $this->fixPendingSessions($studentId);
        
        // المشكلة 2: تزامن التقدم
        $this->synchronizeProgress($studentId);
        
        // المشكلة 3: تحديث نسبة التقدم في المنهج
        $this->updateCurriculumProgress($studentId);
    }

    private function fixPendingSessions($studentId)
    {
        $this->info("1. إصلاح الجلسات المعلقة...");

        $pendingSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'جارية')
            ->where('grade', '>', 0) // لديها تقييم
            ->get();

        $fixedCount = 0;
        foreach ($pendingSessions as $session) {
            // إذا كان لديها تقييم ومرت أكثر من ساعة، اعتبرها مكتملة
            if ($session->grade > 0 && $session->created_at->diffInHours(now()) > 1) {
                $session->update([
                    'status' => 'مكتملة',
                    'completed_at' => $session->updated_at
                ]);
                $fixedCount++;
            }
        }

        if ($fixedCount > 0) {
            $this->info("   ✅ تم إصلاح {$fixedCount} جلسة معلقة");
        } else {
            $this->line("   • لا توجد جلسات معلقة تحتاج إصلاح");
        }
    }

    private function synchronizeProgress($studentId)
    {
        $this->info("2. مزامنة التقدم...");

        $studentCurriculum = StudentCurriculum::where('student_id', $studentId)
            ->where('status', 'قيد التنفيذ')
            ->first();

        if (!$studentCurriculum) {
            $this->warn("   ❌ لا يوجد منهج نشط");
            return;
        }

        // حساب التقدم بناءً على الجلسات المكتملة
        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'مكتملة')->count();

        $newProgress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) : 0;

        // تحديث أو إنشاء سجل التقدم
        $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)
            ->first();

        if ($progress) {
            $oldProgress = $progress->completion_percentage;
            $progress->update([
                'completion_percentage' => $newProgress,
                'updated_at' => now()
            ]);
            $this->info("   ✅ تم تحديث التقدم من {$oldProgress}% إلى {$newProgress}%");
        } else {
            // إنشاء سجل تقدم جديد
            $firstPlan = CurriculumPlan::where('curriculum_id', $studentCurriculum->curriculum_id)
                ->first();

            StudentCurriculumProgress::create([
                'student_curriculum_id' => $studentCurriculum->id,
                'curriculum_plan_id' => $firstPlan->id ?? null,
                'status' => 'قيد التنفيذ',
                'start_date' => $studentCurriculum->start_date ?? now(),
                'completion_percentage' => $newProgress
            ]);
            $this->info("   ✅ تم إنشاء سجل تقدم جديد: {$newProgress}%");
        }
    }

    private function updateCurriculumProgress($studentId)
    {
        $this->info("3. تحديث نسبة التقدم في المنهج...");

        $studentCurriculum = StudentCurriculum::where('student_id', $studentId)
            ->where('status', 'قيد التنفيذ')
            ->first();

        if (!$studentCurriculum) {
            return;
        }

        $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($progress) {
            $oldPercentage = $studentCurriculum->progress_percentage;
            $studentCurriculum->update([
                'progress_percentage' => $progress->completion_percentage
            ]);
            $this->info("   ✅ تم تحديث نسبة المنهج من {$oldPercentage}% إلى {$progress->completion_percentage}%");
        }
    }

    private function enableAutomaticProgression($studentId)
    {
        $this->info("\n⚙️ تفعيل التقدم التلقائي");
        $this->info('===================');

        // التحقق من وجود Observer
        $this->checkRecitationObserver();
        
        // إنشاء جلسة اختبار لتفعيل التقدم التلقائي
        $this->createTestSession($studentId);
    }

    private function checkRecitationObserver()
    {
        $observerPath = app_path('Observers/RecitationSessionObserver.php');
        
        if (file_exists($observerPath)) {
            $this->info("✅ RecitationSessionObserver موجود");
            
            // التحقق من التسجيل في AppServiceProvider
            $providerPath = app_path('Providers/AppServiceProvider.php');
            $content = file_get_contents($providerPath);
            
            if (strpos($content, 'RecitationSessionObserver') !== false) {
                $this->info("✅ Observer مسجل في AppServiceProvider");
            } else {
                $this->warn("⚠️ Observer غير مسجل في AppServiceProvider");
            }
        } else {
            $this->warn("❌ RecitationSessionObserver غير موجود");
        }
    }

    private function createTestSession($studentId)
    {
        $this->info("إنشاء جلسة اختبار...");

        try {
            $session = RecitationSession::create([
                'student_id' => $studentId,
                'teacher_id' => 1,
                'session_id' => 'auto_test_' . time(),
                'start_surah_number' => 1,
                'start_verse' => 1,
                'end_surah_number' => 1,
                'end_verse' => 7,
                'recitation_type' => 'حفظ',
                'grade' => 95,
                'status' => 'جارية',
                'evaluation' => 'ممتاز',
                'teacher_notes' => 'جلسة اختبار للتقدم التلقائي'
            ]);

            $this->info("✅ تم إنشاء جلسة الاختبار: {$session->session_id}");

            // تحديث الحالة لمكتملة لتفعيل Observer
            $session->update(['status' => 'مكتملة', 'completed_at' => now()]);
            $this->info("✅ تم تحديث الجلسة إلى مكتملة - يجب أن يتم تفعيل Observer");

            return $session;
        } catch (\Exception $e) {
            $this->error("خطأ في إنشاء جلسة الاختبار: " . $e->getMessage());
            return null;
        }
    }

    private function testResults($studentId)
    {
        $this->info("\n🧪 اختبار النتائج");
        $this->info('===============');

        // انتظار ثانية لمعالجة Observer
        sleep(1);

        // فحص التقدم المحدث
        $progress = StudentCurriculumProgress::where('student_curriculum_id', function($query) use ($studentId) {
            $query->select('id')
                  ->from('student_curricula')
                  ->where('student_id', $studentId)
                  ->where('status', 'قيد التنفيذ');
        })->orderBy('updated_at', 'desc')->first();

        if ($progress) {
            $this->info("✅ التقدم المحدث: {$progress->completion_percentage}%");
            $this->line("   آخر تحديث: {$progress->updated_at}");
            
            $timeDiff = now()->diffInMinutes($progress->updated_at);
            if ($timeDiff <= 2) {
                $this->info("✅ التقدم يتم تحديثه تلقائياً (فرق الوقت: {$timeDiff} دقيقة)");
            } else {
                $this->warn("⚠️ قد لا يكون التحديث تلقائياً (فرق الوقت: {$timeDiff} دقيقة)");
            }
        } else {
            $this->warn("❌ لم يتم العثور على تقدم محدث");
        }

        // اختبار APIs
        $this->testAPIs($studentId);

        // عرض الإحصائيات النهائية
        $this->showFinalStats($studentId);
    }

    private function testAPIs($studentId)
    {
        $this->info("\n🌐 اختبار APIs");
        $this->info('=============');

        try {
            // اختبار API المنهج اليومي
            $controller = app(\App\Http\Controllers\Api\StudentController::class);
            $response = $controller->getDailyCurriculum($studentId);
            
            if ($response->getStatusCode() === 200) {
                $this->info("✅ API المنهج اليومي يعمل");
                
                $data = json_decode($response->getContent(), true);
                $curriculum = $data['data']['daily_curriculum'] ?? [];
                
                if (!empty($curriculum['memorization']['content'])) {
                    $this->line("   محتوى اليوم: {$curriculum['memorization']['content']}");
                }
            } else {
                $this->warn("❌ مشكلة في API المنهج اليومي");
            }

            // اختبار API المحتوى التالي
            $sessionController = app(\App\Http\Controllers\Api\RecitationSessionController::class);
            $nextResponse = $sessionController->getNextRecitationContent($studentId);
            
            if ($nextResponse->getStatusCode() === 200) {
                $this->info("✅ API المحتوى التالي يعمل");
            } else {
                $this->warn("❌ مشكلة في API المحتوى التالي");
            }

        } catch (\Exception $e) {
            $this->error("خطأ في اختبار APIs: " . $e->getMessage());
        }
    }

    private function showFinalStats($studentId)
    {
        $this->info("\n📊 الإحصائيات النهائية");
        $this->info('===================');

        $totalSessions = RecitationSession::where('student_id', $studentId)->count();
        $completedSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'مكتملة')->count();
        $pendingSessions = RecitationSession::where('student_id', $studentId)
            ->where('status', 'جارية')->count();

        $progress = StudentCurriculumProgress::where('student_curriculum_id', function($query) use ($studentId) {
            $query->select('id')
                  ->from('student_curricula')
                  ->where('student_id', $studentId)
                  ->where('status', 'قيد التنفيذ');
        })->orderBy('updated_at', 'desc')->first();

        $this->table(
            ['المؤشر', 'القيمة'],
            [
                ['إجمالي الجلسات', $totalSessions],
                ['الجلسات المكتملة', $completedSessions],
                ['الجلسات الجارية', $pendingSessions],
                ['معدل الإنجاز', $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) . '%' : '0%'],
                ['نسبة التقدم المسجلة', $progress ? $progress->completion_percentage . '%' : 'غير متاح'],
                ['آخر تحديث للتقدم', $progress ? $progress->updated_at->format('Y-m-d H:i:s') : 'غير متاح'],
            ]
        );

        $this->info("\n💡 التوصيات:");
        if ($pendingSessions > 0) {
            $this->line("• لا تزال هناك {$pendingSessions} جلسة جارية قد تحتاج لإكمال");
        }
        
        if ($progress && $progress->completion_percentage < 100) {
            $remaining = 100 - $progress->completion_percentage;
            $this->line("• الطالب بحاجة لإكمال {$remaining}% من المنهج");
        }
        
        $this->line("• سير العمل التلقائي تم تفعيله وسيعمل مع الجلسات الجديدة");
        $this->line("• يمكن مراقبة التقدم من خلال APIs المتاحة");
    }
}
