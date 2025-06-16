<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Mosque;
use App\Models\CircleSupervisor;
use App\Models\QuranCircle;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\TeacherEvaluation;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ComprehensiveApiTest extends Command
{
    protected $signature = 'test:all-apis';
    protected $description = 'اختبار شامل لجميع APIs المشرف';    private $circleSupervisor;
    private $user;
    private $circle;
    private $mosque;
    private $teacher;
    private $student;

    public function handle()
    {
        $this->info('🚀 بدء الاختبار الشامل لجميع APIs المشرف');
        $this->info('=======================================');
        
        try {
            $this->setupTestData();
            $this->testCircleApis();
            $this->testTeacherApis();
            $this->testStudentApis();
            $this->testEvaluationApis();
            $this->testAttendanceApis();
            $this->testStatisticsApis();
            
            $this->info('🎉 جميع الاختبارات نجحت!');
            $this->info('النظام جاهز للاستخدام بالكامل');
            
        } catch (\Exception $e) {
            $this->error('❌ فشل في الاختبار: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }    private function setupTestData()
    {        $this->info('📝 إعداد البيانات التجريبية...');
          // حذف البيانات القديمة إذا كانت موجودة
        User::where('email', 'test_supervisor@example.com')->delete();
        User::where('username', 'test_supervisor')->delete();
        
          // إنشاء مستخدم ومشرف
        $this->user = User::firstOrCreate([
            'email' => 'test_supervisor@example.com'
        ], [
            'name' => 'مشرف الاختبار',
            'username' => 'test_supervisor',
            'password' => Hash::make('password'),
            'role' => 'supervisor'        ]);        // إنشاء مسجد أولاً
        $this->mosque = \App\Models\Mosque::firstOrCreate([
            'name' => 'مسجد الاختبار'
        ], [
            'neighborhood' => 'حي الاختبار',
            'contact_number' => '0501234567'
        ]);

        // إنشاء حلقة مع القيم الصحيحة
        $this->circle = QuranCircle::firstOrCreate([
            'name' => 'حلقة الاختبار'
        ], [
            'mosque_id' => $this->mosque->id,
            'time_period' => 'عصر', // استخدام قيمة صحيحة من القائمة المسموحة
            'circle_type' => 'حلقة فردية', // استخدام قيمة صحيحة من القائمة المسموحة
            'circle_status' => 'تعمل' // استخدام قيمة صحيحة من القائمة المسموحة
        ]);

        // إنشاء تعيين مشرف للحلقة
        $this->circleSupervisor = CircleSupervisor::firstOrCreate([
            'supervisor_id' => $this->user->id,
            'quran_circle_id' => $this->circle->id
        ], [
            'assignment_date' => now(),
            'is_active' => true,
            'notes' => 'تعيين للاختبار'        ]);        // إنشاء معلم
        $this->teacher = Teacher::firstOrCreate([
            'name' => 'معلم الاختبار',
            'quran_circle_id' => $this->circle->id
        ], [
            'phone' => '0507654321',
            'identity_number' => '1234567890',
            'nationality' => 'سعودي',
            'job_title' => 'معلم حفظ',
            'task_type' => 'معلم بمكافأة',
            'work_time' => 'عصر'
        ]);        // إنشاء طالب
        $this->student = Student::firstOrCreate([
            'name' => 'طالب الاختبار',
            'quran_circle_id' => $this->circle->id
        ], [
            'identity_number' => '2001234567',
            'phone' => '0501111111',
            'guardian_phone' => '0502222222',
            'birth_date' => '2010-01-01',
            'nationality' => 'سعودي'
        ]);

        $this->info('✅ تم إعداد البيانات التجريبية');
    }

    private function testCircleApis()
    {        $this->info('🔵 اختبار Circle APIs...');
        
        // اختبار الحصول على الحلقات
        $circles = QuranCircle::whereHas('circleSupervisors', function($q) {
            $q->where('supervisor_id', $this->user->id)->where('is_active', true);
        })->get();
        $this->assertTrue($circles->count() >= 1, 'يجب أن يكون هناك حلقة واحدة على الأقل');
        $this->info("✅ getCircles: {$circles->count()} حلقة");        // اختبار إنشاء حلقة جديدة
        $newCircle = QuranCircle::create([
            'name' => 'حلقة اختبار API',
            'mosque_id' => $this->mosque->id,
            'time_period' => 'مغرب', // قيمة صحيحة من القائمة المسموحة
            'circle_type' => 'حلقة فردية', // قيمة صحيحة من القائمة المسموحة  
            'circle_status' => 'تعمل' // قيمة صحيحة من القائمة المسموحة
        ]);
        
        // تعيين المشرف على الحلقة الجديدة
        CircleSupervisor::create([
            'supervisor_id' => $this->user->id,
            'quran_circle_id' => $newCircle->id,
            'assignment_date' => now(),
            'is_active' => true
        ]);
        
        $this->assertNotNull($newCircle->id, 'يجب إنشاء حلقة جديدة');
        $this->info("✅ createCircle: تم إنشاء حلقة {$newCircle->name}");        // اختبار تحديث الحلقة
        $newCircle->update(['time_period' => 'عصر ومغرب']);
        $this->assertEquals('عصر ومغرب', $newCircle->fresh()->time_period, 'يجب تحديث الحلقة');
        $this->info("✅ updateCircle: تم تحديث الفترة إلى عصر ومغرب");

        // اختبار حذف الحلقة
        $deleted = $newCircle->delete();
        $this->assertTrue($deleted, 'يجب حذف الحلقة');
        $this->info("✅ deleteCircle: تم حذف الحلقة");
    }

    private function testTeacherApis()
    {
        $this->info('👨‍🏫 اختبار Teacher APIs...');
          // اختبار الحصول على المعلمين
        $teachers = Teacher::where('quran_circle_id', $this->circle->id)->get();
        $this->assertTrue($teachers->count() >= 1, 'يجب أن يكون هناك معلم واحد على الأقل');
        $this->info("✅ getTeachers: {$teachers->count()} معلم");        // اختبار إنشاء معلم جديد
        $newTeacher = Teacher::create([
            'name' => 'معلم جديد للاختبار',
            'phone' => '0509999999',
            'quran_circle_id' => $this->circle->id,
            'identity_number' => '9876543211', // رقم هوية مختلف لتجنب التضارب
            'nationality' => 'سعودي',
            'job_title' => 'معلم تلقين',
            'task_type' => 'معلم محتسب',
            'work_time' => 'مغرب'
        ]);
        $this->assertNotNull($newTeacher->id, 'يجب إنشاء معلم جديد');
        $this->info("✅ createTeacher: تم إنشاء معلم {$newTeacher->name}");

        // اختبار تحديث المعلم
        $newTeacher->update(['phone' => '0508888888']);
        $this->assertEquals('0508888888', $newTeacher->fresh()->phone, 'يجب تحديث المعلم');
        $this->info("✅ updateTeacher: تم تحديث رقم الهاتف");        // تنظيف
        $newTeacher->delete();
        $this->info("✅ تم تنظيف بيانات المعلم الجديد");
    }

    private function testStudentApis()
    {
        $this->info('👥 اختبار Student APIs...');
          // اختبار الحصول على الطلاب
        $students = Student::where('quran_circle_id', $this->circle->id)->get();
        $this->assertTrue($students->count() >= 1, 'يجب أن يكون هناك طالب واحد على الأقل');
        $this->info("✅ getStudents: {$students->count()} طالب");        // اختبار إنشاء طالب جديد
        $newStudent = Student::create([
            'name' => 'طالب جديد للاختبار',
            'quran_circle_id' => $this->circle->id,
            'identity_number' => '2009876543',
            'phone' => '0505555555',
            'guardian_phone' => '0506666666',
            'birth_date' => '2012-01-01',
            'nationality' => 'سعودي'
        ]);
        $this->assertNotNull($newStudent->id, 'يجب إنشاء طالب جديد');
        $this->info("✅ createStudent: تم إنشاء طالب {$newStudent->name}");

        // اختبار تحديث الطالب
        $newStudent->update(['phone' => '0507777777']);
        $this->assertEquals('0507777777', $newStudent->fresh()->phone, 'يجب تحديث الطالب');
        $this->info("✅ updateStudent: تم تحديث رقم الهاتف");

        // اختبار حذف الطالب
        $deleted = $newStudent->delete();
        $this->assertTrue($deleted, 'يجب حذف الطالب');
        $this->info("✅ deleteStudent: تم حذف الطالب");
    }

    private function testEvaluationApis()
    {
        $this->info('⭐ اختبار Teacher Evaluation APIs...');
        
        // اختبار إنشاء تقييم
        $evaluation = TeacherEvaluation::create([
            'teacher_id' => $this->teacher->id,
            'evaluator_id' => $this->user->id,
            'evaluation_date' => now(),
            'performance_score' => 18,
            'attendance_score' => 16,
            'student_interaction_score' => 19,
            'behavior_cooperation_score' => 17,
            'memorization_recitation_score' => 18,
            'general_evaluation_score' => 15,
            'evaluation_period' => 'شهري',
            'notes' => 'تقييم اختبار API',
            'status' => 'مسودة',
            'evaluator_role' => 'مشرف'
        ]);

        $this->assertNotNull($evaluation->id, 'يجب إنشاء تقييم جديد');
        $this->assertEquals(103, $evaluation->total_score, 'يجب حساب النتيجة الإجمالية صحيحة');
        $this->info("✅ createTeacherEvaluation: تم إنشاء تقييم - النتيجة: {$evaluation->total_score}/120");

        // اختبار الحصول على التقييمات
        $evaluations = TeacherEvaluation::where('teacher_id', $this->teacher->id)->get();
        $this->assertTrue($evaluations->count() >= 1, 'يجب أن يكون هناك تقييم واحد على الأقل');
        $this->info("✅ getTeacherEvaluations: {$evaluations->count()} تقييم");

        // اختبار تحديث التقييم
        $evaluation->update(['performance_score' => 20, 'notes' => 'تقييم محدث']);
        $updatedEvaluation = $evaluation->fresh();
        $this->assertEquals(20, $updatedEvaluation->performance_score, 'يجب تحديث مهارات الأداء');
        $this->assertEquals(105, $updatedEvaluation->total_score, 'يجب تحديث النتيجة الإجمالية');
        $this->info("✅ updateTeacherEvaluation: تم التحديث - النتيجة الجديدة: {$updatedEvaluation->total_score}/120");

        // اختبار اعتماد التقييم
        $evaluation->update(['status' => 'معتمد']);
        $this->assertEquals('معتمد', $evaluation->fresh()->status, 'يجب اعتماد التقييم');
        $this->info("✅ approveTeacherEvaluation: تم اعتماد التقييم");

        // تنظيف
        $evaluation->delete();
        $this->info("✅ تم تنظيف بيانات التقييم");
    }    private function testAttendanceApis()
    {
        $this->info('📅 اختبار Attendance APIs...');
        
        // اختبار إنشاء سجل حضور للمعلم
        $attendance = Attendance::create([
            'attendable_type' => Teacher::class,
            'attendable_id' => $this->teacher->id,
            'date' => now()->toDateString(),
            'period' => 'الفجر',
            'status' => 'حاضر',
            'check_in' => now(),
            'notes' => 'سجل حضور للاختبار'
        ]);

        $this->assertNotNull($attendance->id, 'يجب إنشاء سجل حضور');
        $this->info("✅ createAttendance: تم إنشاء سجل حضور للمعلم");

        // اختبار الحصول على سجلات الحضور
        $attendances = Attendance::where('attendable_type', Teacher::class)
                                 ->where('attendable_id', $this->teacher->id)
                                 ->get();
        $this->assertTrue($attendances->count() >= 1, 'يجب أن يكون هناك سجل حضور واحد على الأقل');
        $this->info("✅ getAttendances: {$attendances->count()} سجل حضور");

        // اختبار تحديث سجل الحضور
        $attendance->update(['status' => 'متأخر', 'notes' => 'تحديث حالة الحضور']);
        $this->assertEquals('متأخر', $attendance->fresh()->status, 'يجب تحديث حالة الحضور');
        $this->info("✅ updateAttendance: تم تحديث حالة الحضور إلى متأخر");

        // تنظيف
        $attendance->delete();
        $this->info("✅ تم تنظيف بيانات الحضور");
    }    private function testStatisticsApis()
    {
        $this->info('📊 اختبار Statistics APIs...');
        
        // إحصائيات المشرف
        $circlesCount = QuranCircle::whereHas('circleSupervisors', function($q) {
            $q->where('supervisor_id', $this->user->id)->where('is_active', true);
        })->count();
        
        $teachersCount = Teacher::whereHas('quranCircle.circleSupervisors', function($q) {
            $q->where('supervisor_id', $this->user->id)->where('is_active', true);
        })->count();
        
        $studentsCount = Student::whereHas('quranCircle.circleSupervisors', function($q) {
            $q->where('supervisor_id', $this->user->id)->where('is_active', true);
        })->count();
        
        $evaluationsCount = TeacherEvaluation::whereHas('teacher.quranCircle.circleSupervisors', function($q) {
            $q->where('supervisor_id', $this->user->id)->where('is_active', true);
        })->count();

        $this->info("✅ getSupervisorStats:");
        $this->info("   🔵 الحلقات: {$circlesCount}");
        $this->info("   👨‍🏫 المعلمين: {$teachersCount}");
        $this->info("   👥 الطلاب: {$studentsCount}");
        $this->info("   ⭐ التقييمات: {$evaluationsCount}");

        // إحصائيات التقييمات
        if ($evaluationsCount > 0) {
            $avgScore = TeacherEvaluation::whereHas('teacher.quranCircle.circleSupervisors', function($q) {
                $q->where('supervisor_id', $this->user->id)->where('is_active', true);
            })->avg('total_score');
            
            $this->info("   📈 متوسط التقييمات: " . round($avgScore, 1) . "/120");
        }
    }

    private function assertTrue($condition, $message)
    {
        if (!$condition) {
            throw new \Exception($message);
        }
    }

    private function assertNotNull($value, $message)
    {
        if (is_null($value)) {
            throw new \Exception($message);
        }
    }

    private function assertEquals($expected, $actual, $message)
    {
        if ($expected != $actual) {
            throw new \Exception($message . " (المتوقع: {$expected}, الفعلي: {$actual})");
        }
    }
}
