<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\Curriculum;
use App\Models\CurriculumLevel;
use App\Models\CurriculumPlan;
use App\Models\StudentCurriculum;
use App\Models\RecitationSession;

class TestCurriculumSystemFixed extends Command
{
    protected $signature = 'test:curriculum-system-fixed 
                           {--create-data : إنشاء بيانات تجريبية}
                           {--test-api : اختبار APIs}
                           {--test-progression : اختبار نظام التقدم التلقائي}
                           {--cleanup : تنظيف البيانات التجريبية}
                           {--full : تشغيل جميع الاختبارات}';

    protected $description = 'اختبار شامل ومُصحح لنظام المناهج اليومية';

    private $testMosque;
    private $testTeacher;
    private $testStudent;
    private $testCurriculum;
    private $testCurriculumLevel;
    private $testStudentCurriculum;

    public function handle()
    {
        $this->info('🚀 بدء اختبار نظام المناهج اليومية المُصحح');
        $this->newLine();

        if ($this->option('full')) {
            $this->call('test:curriculum-system-fixed', ['--create-data' => true]);
            $this->call('test:curriculum-system-fixed', ['--test-api' => true]);
            $this->call('test:curriculum-system-fixed', ['--test-progression' => true]);
            $this->info('📋 تشغيل جميع الاختبارات اكتمل. استخدم --cleanup لحذف البيانات التجريبية.');
            return 0;
        }

        if ($this->option('create-data')) {
            $this->createTestData();
        }

        if ($this->option('test-api')) {
            $this->testAPIEndpoints();
        }

        if ($this->option('test-progression')) {
            $this->testProgressionSystem();
        }

        if ($this->option('cleanup')) {
            $this->cleanupTestData();
        }

        return 0;
    }

    private function createTestData()
    {
        $this->info('📝 إنشاء بيانات تجريبية...');

        DB::beginTransaction();
        
        try {
            // إنشاء مسجد تجريبي
            $this->testMosque = Mosque::create([
                'name' => 'مسجد التجارب المُصحح',
                'address' => 'عنوان تجريبي',
                'phone' => '0123456789',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // إنشاء معلم تجريبي
            $teacherUser = User::create([
                'name' => 'المعلم التجريبي المُصحح',
                'email' => 'teacher_fixed@test.com',
                'password' => bcrypt('password'),
                'phone' => '0123456789',
                'user_type' => 'teacher'
            ]);

            $this->testTeacher = Teacher::create([
                'user_id' => $teacherUser->id,
                'mosque_id' => $this->testMosque->id,
                'specialization' => 'التحفيظ',
                'hire_date' => now()
            ]);

            // إنشاء طالب تجريبي
            $studentUser = User::create([
                'name' => 'الطالب التجريبي المُصحح',
                'email' => 'student_fixed@test.com',
                'password' => bcrypt('password'),
                'phone' => '0123456788',
                'user_type' => 'student'
            ]);

            $this->testStudent = Student::create([
                'user_id' => $studentUser->id,
                'mosque_id' => $this->testMosque->id,
                'enrollment_date' => now(),
                'grade_level' => 'الابتدائية',
                'guardian_name' => 'ولي الأمر التجريبي',
                'guardian_phone' => '0123456787'
            ]);

            // إنشاء منهج تجريبي
            $this->testCurriculum = Curriculum::create([
                'name' => 'منهج القرآن المُصحح',
                'type' => 'منهج طالب',
                'description' => 'منهج تجريبي للاختبار المُصحح'
            ]);

            // إنشاء مستوى منهج
            $this->testCurriculumLevel = CurriculumLevel::create([
                'curriculum_id' => $this->testCurriculum->id,
                'name' => 'المستوى الأول المُصحح',
                'level_order' => 1,
                'description' => 'مستوى تجريبي'
            ]);

            // إنشاء خطط يومية متنوعة
            $plans = [
                [
                    'curriculum_id' => $this->testCurriculum->id,
                    'plan_type' => 'الدرس',
                    'content' => 'سورة الفاتحة آية 1-4',
                    'expected_days' => 1
                ],
                [
                    'curriculum_id' => $this->testCurriculum->id,
                    'plan_type' => 'المراجعة الصغرى',
                    'content' => 'مراجعة سورة البقرة آية 1-5',
                    'expected_days' => 1
                ],
                [
                    'curriculum_id' => $this->testCurriculum->id,
                    'plan_type' => 'المراجعة الكبرى',
                    'content' => 'مراجعة جزء عم',
                    'expected_days' => 1
                ],
                [
                    'curriculum_id' => $this->testCurriculum->id,
                    'plan_type' => 'الدرس',
                    'content' => 'سورة الفاتحة آية 5-7',
                    'expected_days' => 1
                ],
                [
                    'curriculum_id' => $this->testCurriculum->id,
                    'plan_type' => 'المراجعة الصغرى',
                    'content' => 'مراجعة سورة الفاتحة كاملة',
                    'expected_days' => 1
                ]
            ];

            foreach ($plans as $plan) {
                CurriculumPlan::create($plan);
            }

            // ربط الطالب بالمنهج
            $this->testStudentCurriculum = StudentCurriculum::create([
                'student_id' => $this->testStudent->id,
                'curriculum_id' => $this->testCurriculum->id,
                'curriculum_level_id' => $this->testCurriculumLevel->id,
                'assigned_date' => now(),
                'status' => 'قيد التنفيذ',
                'completion_percentage' => 0
            ]);

            DB::commit();
            
            $this->info('✅ تم إنشاء البيانات التجريبية بنجاح:');
            $this->line('   📍 المسجد: ' . $this->testMosque->name);
            $this->line('   👨‍🏫 المعلم: ' . $teacherUser->name . ' (' . $teacherUser->email . ')');
            $this->line('   👨‍🎓 الطالب: ' . $studentUser->name . ' (' . $studentUser->email . ')');
            $this->line('   📚 المنهج: ' . $this->testCurriculum->name);
            $this->line('   📋 عدد الخطط: ' . count($plans));
            $this->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ خطأ في إنشاء البيانات: ' . $e->getMessage());
        }
    }

    private function testAPIEndpoints()
    {
        $this->info('🌐 اختبار APIs...');

        if (!$this->testStudent) {
            $this->loadTestData();
        }

        if (!$this->testStudent) {
            $this->error('❌ لا توجد بيانات تجريبية. استخدم --create-data أولاً');
            return;
        }

        try {
            // اختبار API جلب المنهج اليومي
            $controller = new \App\Http\Controllers\Api\StudentController();
            $response = $controller->getDailyCurriculum($this->testStudent->id);
            
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $this->info('✅ تم جلب المنهج اليومي بنجاح');
                $this->displayDailyCurriculum($data['data']);
            } else {
                $this->error('❌ فشل في جلب المنهج اليومي: ' . $data['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ خطأ في اختبار API: ' . $e->getMessage());
        }
    }

    private function displayDailyCurriculum($data)
    {
        $this->info('📊 تفاصيل المنهج اليومي:');
        $this->line('   الطالب: ' . ($data['student']['name'] ?? 'غير محدد'));
        $this->line('   المسجد: ' . ($data['student']['mosque'] ?? 'غير محدد'));
        $this->line('   المنهج: ' . ($data['current_curriculum']['name'] ?? 'غير محدد'));
        $this->line('   المستوى: ' . ($data['current_curriculum']['level'] ?? 'غير محدد'));
        $this->line('   نسبة الإكمال: ' . ($data['current_curriculum']['completion_percentage'] ?? 0) . '%');
        $this->newLine();

        $dailyCurriculum = $data['daily_curriculum'];
        
        if (isset($dailyCurriculum['memorization']) && $dailyCurriculum['memorization']) {
            $mem = $dailyCurriculum['memorization'];
            $this->info('🧠 الحفظ الجديد:');
            $this->line('   النوع: ' . $mem['type']);
            $this->line('   المحتوى: ' . $mem['content']);
            $this->line('   الأيام المتوقعة: ' . $mem['expected_days']);
            $this->newLine();
        }

        if (isset($dailyCurriculum['minor_review']) && $dailyCurriculum['minor_review']) {
            $review = $dailyCurriculum['minor_review'];
            $this->info('🔄 المراجعة الصغرى:');
            $this->line('   النوع: ' . $review['type']);
            $this->line('   المحتوى: ' . $review['content']);
            $this->line('   الأيام المتوقعة: ' . $review['expected_days']);
            $this->newLine();
        }

        if (isset($dailyCurriculum['major_review']) && $dailyCurriculum['major_review']) {
            $review = $dailyCurriculum['major_review'];
            $this->info('🔄 المراجعة الكبرى:');
            $this->line('   النوع: ' . $review['type']);
            $this->line('   المحتوى: ' . $review['content']);
            $this->line('   الأيام المتوقعة: ' . $review['expected_days']);
            $this->newLine();
        }

        // عرض جلسات التسميع اليوم إن وجدت
        if (isset($data['today_recitations'])) {
            $this->info('📝 تسميع اليوم:');
            foreach ($data['today_recitations'] as $type => $recitation) {
                if ($recitation) {
                    $this->line('   ' . $type . ': درجة ' . $recitation['grade'] . ' - ' . $recitation['evaluation']);
                }
            }
            $this->newLine();
        }
    }

    private function testProgressionSystem()
    {
        $this->info('⚡ اختبار نظام التقدم التلقائي...');
        $this->newLine();

        if (!$this->testStudent) {
            $this->loadTestData();
        }

        if (!$this->testStudent) {
            $this->error('❌ لا توجد بيانات تجريبية. استخدم --create-data أولاً');
            return;
        }

        try {
            $controller = new \App\Http\Controllers\Api\StudentController();
            
            // السيناريو الأول: إكمال حفظ بدرجة عالية (8/10)
            $this->info('📝 السيناريو الأول: إكمال الحفظ بدرجة عالية (8/10)');
            
            $request = new \Illuminate\Http\Request([
                'teacher_id' => $this->testTeacher->id,
                'recitation_type' => 'حفظ',
                'start_surah_number' => 1,
                'start_verse' => 1,
                'end_surah_number' => 1,
                'end_verse' => 4,
                'grade' => 8.0,
                'evaluation' => 'جيد جداً',
                'notes' => 'أداء ممتاز'
            ]);

            $response = $controller->completeRecitation($request, $this->testStudent->id);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                $this->info('✅ تم تسجيل التسميع بنجاح');
                $this->line('   📊 الدرجة: ' . $request['grade']);
                $this->line('   📈 انتقل لليوم التالي: ' . ($data['data']['moved_to_next_day'] ? 'نعم' : 'لا'));
                
                if ($data['data']['moved_to_next_day']) {
                    $this->info('🎯 تم الانتقال تلقائياً للخطة التالية!');
                    
                    // التحقق من المنهج الجديد
                    $newResponse = $controller->getDailyCurriculum($this->testStudent->id);
                    $newData = json_decode($newResponse->getContent(), true);
                    
                    if ($newData['success']) {
                        $this->info('📋 المنهج الجديد بعد التقدم:');
                        $this->displayDailyCurriculum($newData['data']);
                    }
                }
            } else {
                $this->error('❌ فشل في تسجيل التسميع: ' . $data['message']);
            }

            $this->newLine();

            // السيناريو الثاني: إكمال حفظ بدرجة منخفضة (5/10)
            $this->info('📝 السيناريو الثاني: إكمال الحفظ بدرجة منخفضة (5/10)');
            
            $lowGradeRequest = new \Illuminate\Http\Request([
                'teacher_id' => $this->testTeacher->id,
                'recitation_type' => 'حفظ',
                'start_surah_number' => 1,
                'start_verse' => 5,
                'end_surah_number' => 1,
                'end_verse' => 7,
                'grade' => 5.0,
                'evaluation' => 'مقبول',
                'notes' => 'يحتاج مراجعة إضافية'
            ]);

            $lowResponse = $controller->completeRecitation($lowGradeRequest, $this->testStudent->id);
            $lowData = json_decode($lowResponse->getContent(), true);

            if ($lowData['success']) {
                $this->info('✅ تم تسجيل التسميع بدرجة منخفضة');
                $this->line('   📊 الدرجة: ' . $lowGradeRequest['grade']);
                $this->line('   📈 انتقل لليوم التالي: ' . ($lowData['data']['moved_to_next_day'] ? 'نعم' : 'لا'));
                
                if (!$lowData['data']['moved_to_next_day']) {
                    $this->info('⏸️ لم يتم الانتقال لليوم التالي (الدرجة أقل من 7)');
                }
            } else {
                $this->error('❌ فشل في تسجيل التسميع: ' . $lowData['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ خطأ في اختبار نظام التقدم: ' . $e->getMessage());
        }
    }

    private function loadTestData()
    {
        // محاولة تحميل البيانات التجريبية الموجودة
        $this->testMosque = Mosque::where('name', 'مسجد التجارب المُصحح')->first();
        
        if ($this->testMosque) {
            $this->testTeacher = Teacher::where('mosque_id', $this->testMosque->id)->first();
            $this->testStudent = Student::where('mosque_id', $this->testMosque->id)->first();
            $this->testCurriculum = Curriculum::where('name', 'منهج القرآن المُصحح')->first();
            
            if ($this->testStudent && $this->testCurriculum) {
                $this->testStudentCurriculum = StudentCurriculum::where('student_id', $this->testStudent->id)
                    ->where('curriculum_id', $this->testCurriculum->id)
                    ->first();
            }
        }
    }

    private function cleanupTestData()
    {
        $this->info('🧹 تنظيف البيانات التجريبية...');

        try {
            // حذف البيانات بالترتيب الصحيح لتجنب مشاكل القيود
            DB::table('student_curriculum_progress')
                ->whereIn('student_curriculum_id', function($query) {
                    $query->select('id')
                          ->from('student_curricula')
                          ->whereIn('student_id', function($subQuery) {
                              $subQuery->select('id')
                                       ->from('students')
                                       ->whereIn('user_id', function($userQuery) {
                                           $userQuery->select('id')
                                                    ->from('users')
                                                    ->where('email', 'like', '%_fixed@test.com');
                                       });
                          });
                })
                ->delete();

            DB::table('recitation_sessions')
                ->whereIn('student_id', function($query) {
                    $query->select('id')
                          ->from('students')
                          ->whereIn('user_id', function($subQuery) {
                              $subQuery->select('id')
                                       ->from('users')
                                       ->where('email', 'like', '%_fixed@test.com');
                          });
                })
                ->delete();

            DB::table('student_curricula')
                ->whereIn('student_id', function($query) {
                    $query->select('id')
                          ->from('students')
                          ->whereIn('user_id', function($subQuery) {
                              $subQuery->select('id')
                                       ->from('users')
                                       ->where('email', 'like', '%_fixed@test.com');
                          });
                })
                ->delete();

            DB::table('curriculum_plans')
                ->whereIn('curriculum_id', function($query) {
                    $query->select('id')
                          ->from('curricula')
                          ->where('name', 'منهج القرآن المُصحح');
                })
                ->delete();

            DB::table('curriculum_levels')
                ->whereIn('curriculum_id', function($query) {
                    $query->select('id')
                          ->from('curricula')
                          ->where('name', 'منهج القرآن المُصحح');
                })
                ->delete();

            DB::table('curricula')->where('name', 'منهج القرآن المُصحح')->delete();

            DB::table('teachers')
                ->whereIn('user_id', function($query) {
                    $query->select('id')
                          ->from('users')
                          ->where('email', 'like', '%_fixed@test.com');
                })
                ->delete();

            DB::table('students')
                ->whereIn('user_id', function($query) {
                    $query->select('id')
                          ->from('users')
                          ->where('email', 'like', '%_fixed@test.com');
                })
                ->delete();

            DB::table('users')->where('email', 'like', '%_fixed@test.com')->delete();
            DB::table('mosques')->where('name', 'مسجد التجارب المُصحح')->delete();

            $this->info('✅ تم تنظيف البيانات التجريبية بنجاح');

        } catch (\Exception $e) {
            $this->error('❌ خطأ في تنظيف البيانات: ' . $e->getMessage());
        }
    }
}
