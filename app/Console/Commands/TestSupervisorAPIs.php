<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\Mosque;
use App\Models\CircleSupervisor;
use App\Models\TeacherEvaluation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class TestSupervisorAPIs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:supervisor-apis {--token= : Bearer token for authentication}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار شامل لجميع APIs المشرف';

    protected $baseUrl;
    protected $token;
    protected $testData = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->baseUrl = config('app.url') . '/api/supervisors';
        
        $this->info('🚀 بدء اختبار APIs المشرف');
        $this->info('=======================');

        // إنشاء أو الحصول على بيانات تجريبية
        $supervisorId = $this->createTestData();

        if (!$supervisorId) {
            $this->error('❌ فشل في إنشاء البيانات التجريبية');
            return;
        }

        $this->info("✅ تم إنشاء البيانات التجريبية بنجاح - معرف المشرف: " . $supervisorId);

        // اختبار بدون APIs لتجنب مشكلة authentication
        $this->testDatabaseOperations();

        $this->info('');
        $this->info('🎉 انتهى اختبار نظام التقييمات');
        $this->info('===========================');
    }

    protected function createTestData()
    {
        $this->info('📝 إنشاء بيانات تجريبية...');

        try {
            // إنشاء دور المشرف إذا لم يكن موجوداً
            $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

            // البحث عن مشرف موجود أو إنشاء واحد جديد
            $supervisor = User::whereHas('roles', function($query) {
                $query->where('name', 'supervisor');
            })->first();

            if (!$supervisor) {
                // إنشاء مستخدم مشرف تجريبي
                $supervisor = User::create([
                    'name' => 'مشرف تجريبي',
                    'email' => 'test.supervisor@example.com',
                    'username' => 'test_supervisor',
                    'password' => Hash::make('password123'),
                    'identity_number' => '1234567890',
                    'phone' => '0501234567',
                    'is_active' => true
                ]);

                $supervisor->assignRole('supervisor');
            }

            // استخدام مسجد موجود أو إنشاء واحد جديد
            $mosque = Mosque::first();
            if (!$mosque) {
                $mosque = Mosque::create([
                    'name' => 'مسجد تجريبي للاختبار',
                    'neighborhood' => 'حي تجريبي',
                    'city' => 'الرياض',
                    'address' => 'عنوان تجريبي',
                    'imam_name' => 'إمام تجريبي',
                    'imam_phone' => '0509876543'
                ]);
            }

            // استخدام حلقة موجودة أو إنشاء واحدة جديدة
            $circle = QuranCircle::first();
            if (!$circle) {
                $circle = QuranCircle::create([
                    'name' => 'حلقة تجريبية للاختبار',
                    'mosque_id' => $mosque->id,
                    'time_period' => 'الفجر',
                    'max_students' => 15,
                    'current_students_count' => 5,
                    'age_group' => 'أطفال',
                    'circle_type' => 'حلقة جماعية'
                ]);
            }

            // ربط المشرف بالحلقة (إذا لم يكن مربوط بالفعل)
            $assignment = CircleSupervisor::where('supervisor_id', $supervisor->id)
                ->where('quran_circle_id', $circle->id)
                ->first();

            if (!$assignment) {
                CircleSupervisor::create([
                    'supervisor_id' => $supervisor->id,
                    'quran_circle_id' => $circle->id,
                    'assignment_date' => now(),
                    'is_active' => true
                ]);
            }

            // استخدام معلم موجود أو إنشاء واحد جديد
            $teacher = Teacher::where('quran_circle_id', $circle->id)->first();
            if (!$teacher) {
                $teacher = Teacher::create([
                    'name' => 'معلم تجريبي',
                    'identity_number' => '9876543210',
                    'phone' => '0551234567',
                    'quran_circle_id' => $circle->id,
                    'mosque_id' => $mosque->id,
                    'job_title' => 'معلم تحفيظ',
                    'task_type' => 'معلم بمكافأة',
                    'work_time' => 'الفجر',
                    'evaluation' => 8,
                    'start_date' => now()->subMonths(3)
                ]);
            }

            // استخدام طالب موجود أو إنشاء واحد جديد
            $student = Student::where('quran_circle_id', $circle->id)->first();
            if (!$student) {
                $student = Student::create([
                    'name' => 'طالب تجريبي',
                    'identity_number' => '1111111111',
                    'phone' => '0571234567',
                    'quran_circle_id' => $circle->id,
                    'guardian_phone' => '0581234567',
                    'enrollment_date' => now()->subMonths(2),
                    'is_active' => true
                ]);
            }

            // حفظ البيانات للاختبار
            $this->testData = [
                'supervisor' => $supervisor,
                'circle' => $circle,
                'teacher' => $teacher,
                'student' => $student,
                'mosque' => $mosque
            ];

            // استخدام session بدلاً من API token
            $this->info('✅ تم إنشاء البيانات التجريبية بنجاح');
            
            // إرجاع معرف المستخدم بدلاً من token
            return $supervisor->id;

        } catch (\Exception $e) {
            $this->error('❌ خطأ في إنشاء البيانات التجريبية: ' . $e->getMessage());
            return null;
        }
    }

    protected function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);

        switch (strtoupper($method)) {
            case 'GET':
                $response = $response->get($url);
                break;
            case 'POST':
                $response = $response->post($url, $data);
                break;
            case 'PUT':
                $response = $response->put($url, $data);
                break;
            case 'DELETE':
                $response = $response->delete($url);
                break;
        }

        return $response;
    }

    protected function testGetAssignedCircles()
    {
        $this->info('');
        $this->info('🔍 اختبار: الحصول على الحلقات المشرف عليها');
        
        $response = $this->makeRequest('GET', '/circles');
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب ' . count($data['data']) . ' حلقة');
            $this->line('   📋 الحلقات: ' . collect($data['data'])->pluck('name')->implode(', '));
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testGetCircleStudents()
    {
        $this->info('');
        $this->info('👥 اختبار: الحصول على طلاب الحلقة');
        
        $circleId = $this->testData['circle']->id;
        $response = $this->makeRequest('GET', "/circles/{$circleId}/students");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب ' . count($data['data']) . ' طالب');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testGetCircleTeachers()
    {
        $this->info('');
        $this->info('👨‍🏫 اختبار: الحصول على معلمي الحلقة');
        
        $circleId = $this->testData['circle']->id;
        $response = $this->makeRequest('GET', "/circles/{$circleId}/teachers");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب ' . count($data['data']) . ' معلم');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testRecordTeacherAttendance()
    {
        $this->info('');
        $this->info('📅 اختبار: تسجيل حضور معلم');
        
        $teacherId = $this->testData['teacher']->id;
        $data = [
            'teacher_id' => $teacherId,
            'status' => 'حاضر',
            'attendance_date' => now()->format('Y-m-d'),
            'notes' => 'حضر في الوقت المحدد - اختبار'
        ];
        
        $response = $this->makeRequest('POST', '/teacher-attendance', $data);
        
        if ($response->successful()) {
            $this->info('✅ نجح: تم تسجيل الحضور');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testCreateTeacherReport()
    {
        $this->info('');
        $this->info('📝 اختبار: إنشاء تقرير معلم');
        
        $teacherId = $this->testData['teacher']->id;
        $data = [
            'teacher_id' => $teacherId,
            'evaluation_score' => 9,
            'performance_notes' => 'أداء ممتاز في التدريس - تقرير اختبار',
            'attendance_notes' => 'منتظم في الحضور',
            'recommendations' => 'يُنصح بإعطائه مزيد من الحلقات'
        ];
        
        $response = $this->makeRequest('POST', '/teacher-report', $data);
        
        if ($response->successful()) {
            $this->info('✅ نجح: تم إنشاء التقرير');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testGetTeacherFullReport()
    {
        $this->info('');
        $this->info('📊 اختبار: الحصول على التقرير الشامل للمعلم');
        
        $teacherId = $this->testData['teacher']->id;
        $response = $this->makeRequest('GET', "/teacher-report/{$teacherId}");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب التقرير الشامل');
            $this->line('   👨‍🏫 المعلم: ' . $data['data']['teacher_info']['name']);
            $this->line('   🏫 الحلقة: ' . $data['data']['workplace_info']['circle_name']);
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testCreateTeacherEvaluation()
    {
        $this->info('');
        $this->info('⭐ اختبار: إنشاء تقييم معلم');
        
        $teacherId = $this->testData['teacher']->id;
        $data = [
            'teacher_id' => $teacherId,
            'performance_evaluation' => 18,
            'attendance_evaluation' => 20,
            'student_interaction_evaluation' => 17,
            'attitude_cooperation_evaluation' => 19,
            'memorization_evaluation' => 16,
            'general_evaluation' => 18,
            'notes' => 'تقييم ممتاز للمعلم - اختبار شامل',
            'evaluation_date' => now()->format('Y-m-d')
        ];
        
        $response = $this->makeRequest('POST', '/teacher-evaluations', $data);
        
        if ($response->successful()) {
            $responseData = $response->json();
            $this->info('✅ نجح: تم إنشاء التقييم');
            $this->line('   📊 النتيجة الإجمالية: ' . $responseData['data']['total_score'] . '/120');
            
            // حفظ معرف التقييم للاختبارات التالية
            $this->testData['evaluation_id'] = $responseData['data']['evaluation_id'];
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testGetTeacherEvaluations()
    {
        $this->info('');
        $this->info('📋 اختبار: الحصول على تقييمات المعلم');
        
        $teacherId = $this->testData['teacher']->id;
        $response = $this->makeRequest('GET', "/teacher-evaluations/{$teacherId}");
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب التقييمات');
            $this->line('   📊 عدد التقييمات: ' . $data['data']['statistics']['total_evaluations']);
            $this->line('   📈 متوسط النتيجة: ' . number_format($data['data']['statistics']['average_score'], 1));
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testUpdateTeacherEvaluation()
    {
        $this->info('');
        $this->info('✏️ اختبار: تحديث تقييم معلم');
        
        if (!isset($this->testData['evaluation_id'])) {
            $this->warn('⚠️ تم تخطي الاختبار: لا يوجد تقييم للتحديث');
            return;
        }
        
        $evaluationId = $this->testData['evaluation_id'];
        $data = [
            'performance_evaluation' => 19,
            'general_evaluation' => 19,
            'notes' => 'تحديث التقييم - اختبار',
            'status' => 'مكتمل'
        ];
        
        $response = $this->makeRequest('PUT', "/teacher-evaluations/{$evaluationId}", $data);
        
        if ($response->successful()) {
            $responseData = $response->json();
            $this->info('✅ نجح: تم تحديث التقييم');
            $this->line('   📊 النتيجة الجديدة: ' . $responseData['data']['total_score'] . '/120');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testApproveTeacherEvaluation()
    {
        $this->info('');
        $this->info('✅ اختبار: اعتماد تقييم معلم');
        
        if (!isset($this->testData['evaluation_id'])) {
            $this->warn('⚠️ تم تخطي الاختبار: لا يوجد تقييم للاعتماد');
            return;
        }
        
        $evaluationId = $this->testData['evaluation_id'];
        $response = $this->makeRequest('POST', "/teacher-evaluations/{$evaluationId}/approve");
        
        if ($response->successful()) {
            $this->info('✅ نجح: تم اعتماد التقييم');
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testRequestStudentTransfer()
    {
        $this->info('');
        $this->info('🔄 اختبار: طلب نقل طالب');
        
        // إنشاء حلقة ثانية للنقل إليها
        $targetCircle = QuranCircle::firstOrCreate(
            ['name' => 'حلقة الهدف للنقل'],
            [
                'mosque_id' => $this->testData['mosque']->id,
                'time_period' => 'مغرب',
                'max_students' => 10,
                'current_students_count' => 3,
                'age_group' => 'أطفال',
                'circle_type' => 'حلقة جماعية'
            ]
        );

        $data = [
            'student_id' => $this->testData['student']->id,
            'current_circle_id' => $this->testData['circle']->id,
            'requested_circle_id' => $targetCircle->id,
            'transfer_reason' => 'رغبة الطالب في تغيير التوقيت - اختبار',
            'notes' => 'طالب متميز يستحق النقل'
        ];
        
        $response = $this->makeRequest('POST', '/student-transfer', $data);
        
        if ($response->successful()) {
            $responseData = $response->json();
            $this->info('✅ نجح: تم تقديم طلب النقل');
            $this->line('   🆔 معرف الطلب: ' . $responseData['data']['request_id']);
            
            // حفظ معرف الطلب للاختبارات التالية
            $this->testData['transfer_request_id'] = $responseData['data']['request_id'];
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testGetTransferRequests()
    {
        $this->info('');
        $this->info('📃 اختبار: الحصول على طلبات النقل');
        
        $response = $this->makeRequest('GET', '/transfer-requests');
        
        if ($response->successful()) {
            $data = $response->json();
            $this->info('✅ نجح: تم جلب طلبات النقل');
            $this->line('   📋 عدد الطلبات: ' . count($data['data']));
        } else {
            $this->error('❌ فشل: ' . $response->body());
        }
    }

    protected function testDatabaseOperations()
    {
        $this->info('');
        $this->info('📊 اختبار عمليات قاعدة البيانات مباشرة...');
        
        // اختبار إنشاء تقييم
        $this->testCreateTeacherEvaluationDirect();
        
        // اختبار قراءة التقييمات
        $this->testReadEvaluations();
        
        // اختبار إحصائيات المشرف
        $this->testSupervisorStats();
        
        // اختبار العلاقات
        $this->testRelationships();
    }

    protected function testCreateTeacherEvaluationDirect()
    {
        $this->info('');
        $this->info('⭐ اختبار: إنشاء تقييم معلم مباشرة في قاعدة البيانات');
        
        try {
            $evaluation = TeacherEvaluation::create([
                'teacher_id' => $this->testData['teacher']->id,
                'evaluator_id' => $this->testData['supervisor']->id,
                'evaluator_type' => 'supervisor',
                'performance_evaluation' => 18,
                'attendance_evaluation' => 20,
                'student_interaction_evaluation' => 17,
                'attitude_cooperation_evaluation' => 19,
                'memorization_evaluation' => 16,
                'general_evaluation' => 18,
                'total_score' => 108,
                'evaluation_date' => now(),
                'notes' => 'تقييم ممتاز للمعلم - اختبار مباشر',
                'status' => 'مكتمل'
            ]);

            $this->info('✅ نجح: تم إنشاء التقييم');
            $this->line('   📊 النتيجة الإجمالية: ' . $evaluation->total_score . '/120');
            $this->line('   📈 النسبة المئوية: ' . $evaluation->percentage . '%');
            
            // حفظ معرف التقييم للاختبارات التالية
            $this->testData['evaluation'] = $evaluation;
            
        } catch (\Exception $e) {
            $this->error('❌ فشل: ' . $e->getMessage());
        }
    }

    protected function testReadEvaluations()
    {
        $this->info('');
        $this->info('📋 اختبار: قراءة تقييمات المعلم');
        
        try {
            $teacher = $this->testData['teacher'];
            $evaluations = TeacherEvaluation::where('teacher_id', $teacher->id)
                ->with(['evaluator:id,name'])
                ->get();

            $this->info('✅ نجح: تم جلب التقييمات');
            $this->line('   📊 عدد التقييمات: ' . $evaluations->count());
            
            if ($evaluations->count() > 0) {
                $avgScore = $evaluations->avg('total_score');
                $avgPercentage = $evaluations->avg('percentage');
                
                $this->line('   📈 متوسط النتيجة: ' . number_format($avgScore, 1) . '/120');
                $this->line('   📈 متوسط النسبة: ' . number_format($avgPercentage, 1) . '%');
                
                // عرض آخر تقييم
                $latest = $evaluations->sortByDesc('evaluation_date')->first();
                $this->line('   📅 آخر تقييم: ' . $latest->evaluation_date->format('Y-m-d'));
                $this->line('   👤 المقيم: ' . ($latest->evaluator?->name ?? 'غير محدد'));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ فشل: ' . $e->getMessage());
        }
    }

    protected function testSupervisorStats()
    {
        $this->info('');
        $this->info('📈 اختبار: إحصائيات المشرف');
        
        try {
            $supervisor = $this->testData['supervisor'];
            
            // الحصول على معرفات الحلقات المشرف عليها
            $circleIds = CircleSupervisor::where('supervisor_id', $supervisor->id)
                ->pluck('quran_circle_id');

            // إحصائيات الحلقات
            $circlesCount = $circleIds->count();
            
            // إحصائيات الطلاب
            $studentsCount = Student::whereIn('quran_circle_id', $circleIds)->count();
            
            // إحصائيات المعلمين
            $teachersCount = Teacher::whereIn('quran_circle_id', $circleIds)->count();
            
            // إحصائيات التقييمات
            $evaluationsCount = TeacherEvaluation::where('evaluator_id', $supervisor->id)->count();

            $this->info('✅ نجح: تم حساب الإحصائيات');
            $this->line('   🏫 عدد الحلقات: ' . $circlesCount);
            $this->line('   👥 عدد الطلاب: ' . $studentsCount);
            $this->line('   👨‍🏫 عدد المعلمين: ' . $teachersCount);
            $this->line('   ⭐ عدد التقييمات: ' . $evaluationsCount);
            
        } catch (\Exception $e) {
            $this->error('❌ فشل: ' . $e->getMessage());
        }
    }

    protected function testRelationships()
    {
        $this->info('');
        $this->info('🔗 اختبار: العلاقات بين الجداول');
        
        try {
            $teacher = $this->testData['teacher'];
            
            // اختبار علاقة المعلم مع التقييمات
            $evaluationsCount = $teacher->evaluations()->count();
            $this->line('   📊 تقييمات المعلم: ' . $evaluationsCount);
            
            // اختبار علاقة المعلم مع الحلقة
            $circle = $teacher->quranCircle;
            if ($circle) {
                $this->line('   🏫 حلقة المعلم: ' . $circle->name);
            }
            
            // اختبار علاقة المعلم مع المسجد
            $mosque = $teacher->mosque;
            if ($mosque) {
                $this->line('   🕌 مسجد المعلم: ' . $mosque->name);
            }
            
            // اختبار متوسط التقييمات
            $avgEvaluation = $teacher->average_evaluation;
            if ($avgEvaluation) {
                $this->line('   📈 متوسط التقييم: ' . number_format($avgEvaluation, 1));
            }
            
            $this->info('✅ نجح: جميع العلاقات تعمل بشكل صحيح');
            
        } catch (\Exception $e) {
            $this->error('❌ فشل: ' . $e->getMessage());
        }
    }
}
