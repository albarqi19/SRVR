<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\User;
use App\Models\QuranCircle;
use App\Models\Curriculum;
use App\Services\DailyCurriculumTrackingService;
use App\Services\FlexibleProgressionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestRecitationSessionIntegration extends Command
{
    /**
     * اسم الأمر وتوقيعه
     */
    protected $signature = 'test:recitation-integration {--create-data : إنشاء بيانات اختبارية جديدة}';

    /**
     * وصف الأمر
     */
    protected $description = 'اختبار متكامل لوظائف حالة جلسات التسميع وربطها بتتبع المنهج';

    protected $curriculumService;
    protected $progressionService;

    public function __construct(
        DailyCurriculumTrackingService $curriculumService,
        FlexibleProgressionService $progressionService
    ) {
        parent::__construct();
        $this->curriculumService = $curriculumService;
        $this->progressionService = $progressionService;
    }

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        $this->info('🚀 بدء الاختبار المتكامل لوظائف جلسات التسميع...');
        $this->line('===============================================');

        try {
            // 1. التحقق من البنية الأساسية
            $this->testDatabaseStructure();

            // 2. إنشاء أو استخدام بيانات اختبارية
            if ($this->option('create-data')) {
                $testData = $this->createTestData();
            } else {
                $testData = $this->getExistingTestData();
            }

            if (!$testData) {
                $this->error('❌ لا توجد بيانات اختبارية مناسبة. استخدم --create-data لإنشاء بيانات جديدة');
                return 1;
            }

            // 3. اختبار إنشاء جلسة تسميع
            $this->testSessionCreation($testData);

            // 4. اختبار تحديث حالة الجلسة
            $this->testStatusUpdates($testData);

            // 5. اختبار ربط المنهج
            $this->testCurriculumIntegration($testData);

            // 6. اختبار Observer
            $this->testObserverFunctionality($testData);

            // 7. اختبار خدمات التقدم
            $this->testProgressServices($testData);

            // 8. عرض إحصائيات نهائية
            $this->showFinalStatistics();

            $this->info('✅ تم إكمال جميع الاختبارات بنجاح!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ حدث خطأ أثناء الاختبار: ' . $e->getMessage());
            $this->error('📍 التفاصيل: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * اختبار بنية قاعدة البيانات
     */
    private function testDatabaseStructure()
    {
        $this->info('📋 1. اختبار بنية قاعدة البيانات...');

        // التحقق من وجود الجداول
        $tables = ['recitation_sessions', 'students', 'student_progress', 'curriculums'];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->line("   ✓ الجدول $table موجود");
            } else {
                throw new \Exception("الجدول $table غير موجود");
            }
        }

        // التحقق من الحقول الجديدة في recitation_sessions
        $columns = DB::getSchemaBuilder()->getColumnListing('recitation_sessions');
        $requiredColumns = ['status', 'curriculum_id'];
        
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                $this->line("   ✓ الحقل $column موجود في جدول recitation_sessions");
            } else {
                throw new \Exception("الحقل $column غير موجود في جدول recitation_sessions");
            }
        }

        $this->info('   ✅ بنية قاعدة البيانات صحيحة');
    }

    /**
     * إنشاء بيانات اختبارية
     */
    private function createTestData()
    {
        $this->info('📊 2. إنشاء بيانات اختبارية...');

        DB::beginTransaction();
        try {
            // إنشاء طالب اختباري
            $student = Student::firstOrCreate([
                'identity_number' => 'TEST123456'
            ], [
                'name' => 'طالب اختباري',
                'date_of_birth' => '2005-01-01',
                'phone' => '0500000000',
                'address' => 'عنوان اختباري'
            ]);

            // إنشاء معلم اختباري
            $teacher = User::firstOrCreate([
                'email' => 'test.teacher@garb.test'
            ], [
                'name' => 'معلم اختباري',
                'password' => bcrypt('password'),
                'role' => 'teacher'
            ]);

            // إنشاء حلقة اختبارية
            $circle = QuranCircle::firstOrCreate([
                'name' => 'حلقة اختبارية'
            ], [
                'description' => 'حلقة للاختبار',
                'teacher_id' => $teacher->id,
                'max_students' => 10
            ]);

            // إنشاء منهج اختباري
            $curriculum = Curriculum::firstOrCreate([
                'name' => 'منهج اختباري'
            ], [
                'description' => 'منهج لأغراض الاختبار',
                'level' => 'مبتدئ',
                'start_surah' => 1,
                'end_surah' => 5
            ]);

            // إنشاء تقدم طالب
            $progress = StudentProgress::updateOrCreate([
                'student_id' => $student->id,
                'curriculum_id' => $curriculum->id
            ], [
                'is_active' => true,
                'completion_percentage' => 0,
                'notes' => 'تقدم اختباري'
            ]);

            DB::commit();

            $this->line('   ✓ تم إنشاء الطالب: ' . $student->name);
            $this->line('   ✓ تم إنشاء المعلم: ' . $teacher->name);
            $this->line('   ✓ تم إنشاء الحلقة: ' . $circle->name);
            $this->line('   ✓ تم إنشاء المنهج: ' . $curriculum->name);

            return [
                'student' => $student,
                'teacher' => $teacher,
                'circle' => $circle,
                'curriculum' => $curriculum,
                'progress' => $progress
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * الحصول على بيانات اختبارية موجودة
     */
    private function getExistingTestData()
    {
        $this->info('📊 2. الحصول على بيانات اختبارية موجودة...');

        $student = Student::first();
        $teacher = User::where('role', 'teacher')->first() ?? User::first();
        $circle = QuranCircle::first();
        $curriculum = Curriculum::first();

        if (!$student || !$teacher || !$circle) {
            return null;
        }

        // إنشاء أو جلب تقدم الطالب
        $progress = StudentProgress::firstOrCreate([
            'student_id' => $student->id,
            'curriculum_id' => $curriculum->id ?? 1
        ], [
            'is_active' => true,
            'completion_percentage' => 0
        ]);

        $this->line('   ✓ استخدام الطالب: ' . $student->name);
        $this->line('   ✓ استخدام المعلم: ' . $teacher->name);
        $this->line('   ✓ استخدام الحلقة: ' . $circle->name);

        return [
            'student' => $student,
            'teacher' => $teacher,
            'circle' => $circle,
            'curriculum' => $curriculum,
            'progress' => $progress
        ];
    }

    /**
     * اختبار إنشاء جلسة تسميع
     */
    private function testSessionCreation($testData)
    {
        $this->info('🎯 3. اختبار إنشاء جلسة تسميع...');

        $sessionData = [
            'student_id' => $testData['student']->id,
            'teacher_id' => $testData['teacher']->id,
            'quran_circle_id' => $testData['circle']->id,
            'curriculum_id' => $testData['curriculum']->id ?? null,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 10,
            'recitation_type' => 'حفظ',
            'grade' => 8.5,
            'evaluation' => 'جيد جداً',
            'status' => 'جارية',
            'teacher_notes' => 'جلسة اختبارية'
        ];

        $session = RecitationSession::create($sessionData);

        $this->line('   ✓ تم إنشاء الجلسة بـ ID: ' . $session->session_id);
        $this->line('   ✓ حالة الجلسة: ' . $session->status);
        $this->line('   ✓ منهج الجلسة: ' . ($session->curriculum_id ?? 'غير محدد'));

        return $session;
    }

    /**
     * اختبار تحديث حالات الجلسة
     */
    private function testStatusUpdates($testData)
    {
        $this->info('🔄 4. اختبار تحديث حالات الجلسة...');

        // إنشاء جلسة للاختبار
        $session = $this->testSessionCreation($testData);

        $statuses = ['جارية', 'غير مكتملة', 'مكتملة'];

        foreach ($statuses as $status) {
            $oldStatus = $session->status;
            $session->update(['status' => $status]);
            $session->refresh();

            $this->line("   ✓ تم تحديث الحالة من '$oldStatus' إلى '$status'");
            
            // تسجيل الوقت للمراقبة
            sleep(1);
        }

        return $session;
    }

    /**
     * اختبار ربط المنهج
     */
    private function testCurriculumIntegration($testData)
    {
        $this->info('📚 5. اختبار ربط المنهج...');

        $session = RecitationSession::where('student_id', $testData['student']->id)->first();
        
        if (!$session) {
            $session = $this->testSessionCreation($testData);
        }

        // اختبار ربط الجلسة بالمنهج
        if ($session->curriculum_id) {
            $this->line('   ✓ الجلسة مربوطة بالمنهج رقم: ' . $session->curriculum_id);
        } else {
            $this->warn('   ⚠ الجلسة غير مربوطة بأي منهج');
        }

        // اختبار الحصول على المحتوى التالي
        try {
            $nextContent = $this->curriculumService->getNextDayRecitationContent($testData['student']->id);
            if ($nextContent) {
                $this->line('   ✓ تم الحصول على المحتوى التالي للتسميع');
            } else {
                $this->warn('   ⚠ لم يتم العثور على محتوى تالي');
            }
        } catch (\Exception $e) {
            $this->warn('   ⚠ خطأ في الحصول على المحتوى التالي: ' . $e->getMessage());
        }
    }

    /**
     * اختبار وظائف Observer
     */
    private function testObserverFunctionality($testData)
    {
        $this->info('👁 6. اختبار وظائف Observer...');

        // إنشاء جلسة جديدة لاختبار Observer
        $sessionData = [
            'student_id' => $testData['student']->id,
            'teacher_id' => $testData['teacher']->id,
            'quran_circle_id' => $testData['circle']->id,
            'curriculum_id' => $testData['curriculum']->id ?? null,
            'start_surah_number' => 2,
            'start_verse' => 1,
            'end_surah_number' => 2,
            'end_verse' => 5,
            'recitation_type' => 'مراجعة صغرى',
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'status' => 'جارية',
            'teacher_notes' => 'اختبار Observer'
        ];

        $session = RecitationSession::create($sessionData);
        $this->line('   ✓ تم إنشاء جلسة جديدة للاختبار');

        // تحديث الحالة إلى مكتملة لاختبار Observer
        $progressBefore = StudentProgress::where('student_id', $testData['student']->id)->first();
        $completionBefore = $progressBefore ? $progressBefore->completion_percentage : 0;

        $session->update(['status' => 'مكتملة']);
        $this->line('   ✓ تم تحديث حالة الجلسة إلى "مكتملة"');

        // التحقق من تحديث التقدم
        $progressAfter = StudentProgress::where('student_id', $testData['student']->id)->first();
        
        if ($progressAfter) {
            $this->line('   ✓ تم العثور على سجل تقدم الطالب');
            $this->line('   ✓ نسبة الإكمال قبل: ' . $completionBefore . '%');
            $this->line('   ✓ نسبة الإكمال بعد: ' . $progressAfter->completion_percentage . '%');
        } else {
            $this->warn('   ⚠ لم يتم العثور على سجل تقدم للطالب');
        }
    }

    /**
     * اختبار خدمات التقدم
     */
    private function testProgressServices($testData)
    {
        $this->info('⚙️ 7. اختبار خدمات التقدم...');

        try {
            // اختبار تقييم استعداد الطالب
            $evaluation = $this->progressionService->evaluateProgressionReadiness($testData['student']);
            
            $this->line('   ✓ نتيجة تقييم الاستعداد:');
            $this->line('     - جاهز للانتقال: ' . ($evaluation['ready'] ? 'نعم' : 'لا'));
            $this->line('     - النقاط: ' . ($evaluation['score'] ?? 'غير محدد'));
            $this->line('     - السبب: ' . ($evaluation['reason'] ?? 'غير محدد'));

        } catch (\Exception $e) {
            $this->warn('   ⚠ خطأ في تقييم التقدم: ' . $e->getMessage());
        }
    }

    /**
     * عرض إحصائيات نهائية
     */
    private function showFinalStatistics()
    {
        $this->info('📊 8. الإحصائيات النهائية...');

        $totalSessions = RecitationSession::count();
        $completedSessions = RecitationSession::where('status', 'مكتملة')->count();
        $ongoingSessions = RecitationSession::where('status', 'جارية')->count();
        $incompleteSessions = RecitationSession::where('status', 'غير مكتملة')->count();

        $this->table([
            'الإحصائية', 'العدد'
        ], [
            ['إجمالي الجلسات', $totalSessions],
            ['الجلسات المكتملة', $completedSessions],
            ['الجلسات الجارية', $ongoingSessions],
            ['الجلسات غير المكتملة', $incompleteSessions],
        ]);

        // إحصائيات StudentProgress
        $activeProgress = StudentProgress::where('is_active', true)->count();
        $this->line('📈 عدد سجلات التقدم النشطة: ' . $activeProgress);

        // متوسط الدرجات
        $avgGrade = RecitationSession::whereNotNull('grade')->avg('grade');
        $this->line('📊 متوسط الدرجات: ' . round($avgGrade ?? 0, 2));
    }
}
