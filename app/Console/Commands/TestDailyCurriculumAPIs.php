<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\StudentCurriculum;
use App\Models\Curriculum;
use App\Models\CurriculumPlan;
use App\Models\StudentCurriculumProgress;
use App\Models\RecitationSession;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\RecitationSessionController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TestDailyCurriculumAPIs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:daily-curriculum-apis {--student-id=14 : معرف الطالب للاختبار} {--create-data : إنشاء بيانات تجريبية}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار شامل ومفصل لـ APIs المنهج اليومي';

    private $studentId;
    private $verbose;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->studentId = $this->option('student-id');
        $this->verbose = $this->output->isVerbose();
        
        $this->info('🚀 بدء الاختبار الشامل لـ APIs المنهج اليومي');
        $this->info('=' . str_repeat('=', 60));
        $this->newLine();

        // 1. فحص قاعدة البيانات
        $this->step1_checkDatabase();
        
        // 2. فحص الطالب
        $this->step2_checkStudent();
        
        // 3. فحص المناهج
        $this->step3_checkCurricula();
        
        // 4. إنشاء بيانات تجريبية إذا لزم الأمر
        if ($this->option('create-data')) {
            $this->step4_createTestData();
        }
        
        // 5. اختبار API المنهج اليومي
        $this->step5_testDailyCurriculumAPI();
        
        // 6. اختبار API المحتوى التالي
        $this->step6_testNextContentAPI();
        
        // 7. اختبار APIs إضافية
        $this->step7_testAdditionalAPIs();
        
        // 8. تقرير النتائج النهائي
        $this->step8_finalReport();
    }

    private function step1_checkDatabase()
    {
        $this->info('📊 الخطوة 1: فحص قاعدة البيانات');
        $this->line('─' . str_repeat('─', 50));

        $tables = [
            'students' => 'جدول الطلاب',
            'curricula' => 'جدول المناهج',
            'student_curricula' => 'جدول مناهج الطلاب',
            'curriculum_plans' => 'جدول خطط المناهج',
            'student_curriculum_progress' => 'جدول تقدم الطالب',
            'recitation_sessions' => 'جدول جلسات التسميع'
        ];

        foreach ($tables as $table => $description) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->info("✅ {$description}: موجود ({$count} سجل)");
                
                if ($this->verbose) {
                    $columns = Schema::getColumnListing($table);
                    $this->line("   الأعمدة: " . implode(', ', $columns));
                }
            } else {
                $this->error("❌ {$description}: غير موجود");
            }
        }
        $this->newLine();
    }

    private function step2_checkStudent()
    {
        $this->info('👤 الخطوة 2: فحص بيانات الطالب');
        $this->line('─' . str_repeat('─', 50));

        try {
            $student = Student::with(['mosque', 'quranCircle'])->find($this->studentId);
            
            if (!$student) {
                $this->error("❌ الطالب رقم {$this->studentId} غير موجود");
                
                // عرض الطلاب المتاحين
                $availableStudents = Student::select('id', 'name')->limit(10)->get();
                if ($availableStudents->count() > 0) {
                    $this->warn("الطلاب المتاحين:");
                    foreach ($availableStudents as $s) {
                        $this->line("  - ID: {$s->id}, الاسم: {$s->name}");
                    }
                }
                return false;
            }

            $this->info("✅ الطالب موجود:");
            $this->line("   الاسم: {$student->name}");
            $this->line("   المسجد: " . ($student->mosque->name ?? 'غير محدد'));
            $this->line("   الحلقة: " . ($student->quranCircle->name ?? 'غير محدد'));
            $this->line("   الحالة: " . ($student->is_active ? 'نشط' : 'غير نشط'));
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في فحص الطالب: " . $e->getMessage());
            return false;
        }
        
        $this->newLine();
        return true;
    }

    private function step3_checkCurricula()
    {
        $this->info('📚 الخطوة 3: فحص المناهج والخطط');
        $this->line('─' . str_repeat('─', 50));

        try {
            // فحص المناهج المتاحة
            $curricula = Curriculum::count();
            $this->info("📖 إجمالي المناهج: {$curricula}");

            // فحص مناهج الطالب
            $studentCurricula = StudentCurriculum::where('student_id', $this->studentId)
                ->with(['curriculum', 'level'])
                ->get();

            $this->info("👨‍🎓 مناهج الطالب: " . $studentCurricula->count());
            
            foreach ($studentCurricula as $sc) {
                $this->line("   - المنهج: " . ($sc->curriculum->name ?? 'غير محدد'));
                $this->line("     المستوى: " . ($sc->level->name ?? 'غير محدد'));
                $this->line("     الحالة: {$sc->status}");
                $this->line("     نسبة الإنجاز: {$sc->completion_percentage}%");
                
                // فحص خطط المنهج
                if ($sc->curriculum_id) {
                    $plans = CurriculumPlan::where('curriculum_id', $sc->curriculum_id)->count();
                    $this->line("     عدد الخطط: {$plans}");
                }
                $this->newLine();
            }

            // فحص تقدم الطالب
            $progress = StudentCurriculumProgress::where('student_curriculum_id', 
                $studentCurricula->first()->id ?? 0
            )->count();
            $this->info("📈 سجلات التقدم: {$progress}");

        } catch (\Exception $e) {
            $this->error("❌ خطأ في فحص المناهج: " . $e->getMessage());
            if ($this->verbose) {
                $this->line("التفاصيل: " . $e->getTraceAsString());
            }
        }
        
        $this->newLine();
    }

    private function step4_createTestData()
    {
        $this->info('🔧 الخطوة 4: إنشاء بيانات تجريبية');
        $this->line('─' . str_repeat('─', 50));

        try {
            // إنشاء منهج تجريبي إذا لم يكن موجوداً
            $curriculum = Curriculum::firstOrCreate([
                'name' => 'منهج تجريبي للاختبار'
            ], [
                'description' => 'منهج تم إنشاؤه للاختبار',
                'type' => 'منهج طالب',
                'is_active' => true
            ]);

            $this->info("✅ تم إنشاء/العثور على المنهج: {$curriculum->name}");

            // إنشاء خطط المنهج
            $plans = [
                ['content' => 'سورة الفاتحة', 'plan_type' => 'حفظ', 'expected_days' => 1],
                ['content' => 'سورة البقرة 1-10', 'plan_type' => 'حفظ', 'expected_days' => 2],
                ['content' => 'سورة البقرة 11-20', 'plan_type' => 'حفظ', 'expected_days' => 2],
                ['content' => 'مراجعة سورة الفاتحة', 'plan_type' => 'مراجعة', 'expected_days' => 1],
                ['content' => 'مراجعة سورة البقرة 1-10', 'plan_type' => 'مراجعة', 'expected_days' => 1],
            ];

            foreach ($plans as $index => $planData) {
                CurriculumPlan::firstOrCreate([
                    'curriculum_id' => $curriculum->id,
                    'content' => $planData['content']
                ], array_merge($planData, [
                    'order_number' => $index + 1,
                    'is_active' => true
                ]));
            }

            $this->info("✅ تم إنشاء خطط المنهج");

            // ربط الطالب بالمنهج
            $studentCurriculum = StudentCurriculum::firstOrCreate([
                'student_id' => $this->studentId,
                'curriculum_id' => $curriculum->id
            ], [
                'status' => 'قيد التنفيذ',
                'start_date' => Carbon::now(),
                'completion_percentage' => 0
            ]);

            $this->info("✅ تم ربط الطالب بالمنهج");

        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء البيانات التجريبية: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function step5_testDailyCurriculumAPI()
    {
        $this->info('🔍 الخطوة 5: اختبار API المنهج اليومي');
        $this->line('─' . str_repeat('─', 50));

        try {
            $controller = new StudentController();
            $response = $controller->getDailyCurriculum($this->studentId);
            
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);

            $this->info("📡 استدعاء API: GET /api/students/{$this->studentId}/daily-curriculum");
            $this->info("📊 كود الاستجابة: {$statusCode}");

            if ($statusCode === 200) {
                $this->info("✅ نجح الاستدعاء");
                
                if ($this->verbose) {
                    $this->line("📄 محتوى الاستجابة:");
                    $this->line(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                } else {
                    // عرض ملخص
                    if (isset($content['data'])) {
                        $data = $content['data'];
                        $this->line("👤 الطالب: " . ($data['student']['name'] ?? 'غير محدد'));
                        $this->line("🏢 المسجد: " . ($data['student']['mosque'] ?? 'غير محدد'));
                        
                        if (isset($data['current_curriculum'])) {
                            $curr = $data['current_curriculum'];
                            $this->line("📚 المنهج: " . ($curr['name'] ?? 'غير محدد'));
                            $this->line("📊 نسبة الإنجاز: " . ($curr['completion_percentage'] ?? 0) . "%");
                        }
                        
                        if (isset($data['daily_curriculum'])) {
                            $daily = $data['daily_curriculum'];
                            $this->line("📖 حفظ اليوم: " . ($daily['memorization']['content'] ?? 'لا يوجد'));
                            $this->line("🔄 مراجعة صغرى: " . ($daily['minor_review']['content'] ?? 'لا يوجد'));
                            $this->line("🔄 مراجعة كبرى: " . ($daily['major_review']['content'] ?? 'لا يوجد'));
                        }
                    }
                }
            } else {
                $this->error("❌ فشل الاستدعاء");
                $this->line("رسالة الخطأ: " . ($content['message'] ?? 'غير محدد'));
                
                if ($this->verbose && isset($content['error'])) {
                    $this->line("تفاصيل الخطأ: " . $content['error']);
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ خطأ في استدعاء API: " . $e->getMessage());
            
            if ($this->verbose) {
                $this->line("Stack trace: " . $e->getTraceAsString());
            }
        }
        
        $this->newLine();
    }

    private function step6_testNextContentAPI()
    {
        $this->info('🔮 الخطوة 6: اختبار API المحتوى التالي');
        $this->line('─' . str_repeat('─', 50));

        try {
            // استخدام App container لحل dependencies
            $controller = app(RecitationSessionController::class);
            $response = $controller->getNextRecitationContent($this->studentId);
            
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);

            $this->info("📡 استدعاء API: GET /api/recitation/sessions/next-content/{$this->studentId}");
            $this->info("📊 كود الاستجابة: {$statusCode}");

            if ($statusCode === 200) {
                $this->info("✅ نجح الاستدعاء");
                
                if ($this->verbose) {
                    $this->line("📄 محتوى الاستجابة:");
                    $this->line(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                } else {
                    if (isset($content['data'])) {
                        $this->line("🔮 المحتوى التالي متاح");
                        // عرض ملخص المحتوى
                    }
                }
            } else {
                $this->error("❌ فشل الاستدعاء");
                $this->line("رسالة الخطأ: " . ($content['message'] ?? 'غير محدد'));
            }

        } catch (\Exception $e) {
            $this->error("❌ خطأ في استدعاء API: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function step7_testAdditionalAPIs()
    {
        $this->info('🧪 الخطوة 7: اختبار APIs إضافية');
        $this->line('─' . str_repeat('─', 50));

        $apis = [
            'studentCurriculum' => 'منهج الطالب',
            'studentStats' => 'إحصائيات الطالب'
        ];

        foreach ($apis as $method => $description) {
            try {
                $this->line("🔍 اختبار {$description}...");
                
                $controller = app(StudentController::class);
                $response = $controller->$method($this->studentId);
                
                $statusCode = $response->getStatusCode();
                
                if ($statusCode === 200) {
                    $this->info("   ✅ {$description}: نجح");
                } else {
                    $this->warn("   ⚠️ {$description}: كود {$statusCode}");
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ {$description}: " . $e->getMessage());
            }
        }

        // اختبار جلسات التسميع منفصلاً مع Request object
        try {
            $this->line("🔍 اختبار جلسات تسميع الطالب...");
            
            $controller = app(StudentController::class);
            $request = new Request();
            $response = $controller->studentRecitationSessions($this->studentId, $request);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 200) {
                $this->info("   ✅ جلسات تسميع الطالب: نجح");
            } else {
                $this->warn("   ⚠️ جلسات تسميع الطالب: كود {$statusCode}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ جلسات تسميع الطالب: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function step8_finalReport()
    {
        $this->info('📋 الخطوة 8: التقرير النهائي');
        $this->line('═' . str_repeat('═', 60));

        // إحصائيات سريعة
        $studentExists = Student::find($this->studentId) ? '✅' : '❌';
        $curriculaCount = StudentCurriculum::where('student_id', $this->studentId)->count();
        $sessionsCount = RecitationSession::where('student_id', $this->studentId)->count();

        $this->info("🏁 ملخص نتائج الاختبار:");
        $this->line("   👤 الطالب موجود: {$studentExists}");
        $this->line("   📚 عدد المناهج: {$curriculaCount}");
        $this->line("   🎯 عدد جلسات التسميع: {$sessionsCount}");

        $this->newLine();
        $this->info("💡 توصيات:");
        
        if ($curriculaCount === 0) {
            $this->warn("   - يُنصح بإنشاء منهج للطالب باستخدام --create-data");
        }
        
        if ($sessionsCount === 0) {
            $this->warn("   - لا توجد جلسات تسميع مسجلة للطالب");
        }
        
        $this->info("   - لعرض تفاصيل أكثر، استخدم --verbose");
        $this->info("   - لإنشاء بيانات تجريبية، استخدم --create-data");

        $this->newLine();
        $this->info("🎉 انتهى الاختبار الشامل!");
    }
}
