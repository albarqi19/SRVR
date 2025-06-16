<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\QuranCircle;
use App\Http\Controllers\Api\RecitationSessionController;
use Illuminate\Http\Request;

class TestRecitationSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:recitation-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Recitation Session API and Model functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 بدء اختبار جلسات التسميع...');
        $this->newLine();

        // 1. فحص البيانات الأساسية
        $this->checkBasicData();
        
        // 2. اختبار إنشاء جلسة تسميع مباشرة
        $this->testDirectSessionCreation();
        
        // 3. اختبار Controller
        $this->testController();
        
        // 4. عرض الجلسات الموجودة
        $this->showExistingSessions();
        
        $this->newLine();
        $this->info('✅ انتهى الاختبار!');
    }

    private function checkBasicData()
    {
        $this->info('=== 1. فحص البيانات الأساسية ===');
        
        $studentCount = Student::count();
        $userCount = User::count();
        $circleCount = QuranCircle::count();
        
        $this->line("عدد الطلاب: {$studentCount}");
        $this->line("عدد المستخدمين: {$userCount}");
        $this->line("عدد الحلقات: {$circleCount}");
        
        // عرض أول عنصر من كل نوع
        $student = Student::first();
        if ($student) {
            $this->line("✅ أول طالب - ID: {$student->id}, الاسم: {$student->name}");
        } else {
            $this->error("❌ لا يوجد طلاب");
        }
        
        $user = User::first();
        if ($user) {
            $this->line("✅ أول مستخدم - ID: {$user->id}, الاسم: {$user->name}");
        } else {
            $this->error("❌ لا يوجد مستخدمين");
        }
        
        $circle = QuranCircle::first();
        if ($circle) {
            $this->line("✅ أول حلقة - ID: {$circle->id}, الاسم: {$circle->name}");
        } else {
            $this->error("❌ لا يوجد حلقات");
        }
        
        $this->newLine();
    }

    private function testDirectSessionCreation()
    {
        $this->info('=== 2. اختبار إنشاء جلسة تسميع مباشرة ===');
        
        try {
            $session = RecitationSession::create([
                'student_id' => 1,
                'teacher_id' => 1,
                'quran_circle_id' => 1,
                'start_surah_number' => 2,
                'start_verse' => 1,
                'end_surah_number' => 2,
                'end_verse' => 50,
                'recitation_type' => 'حفظ',
                'duration_minutes' => 15,
                'grade' => 8.5,
                'evaluation' => 'جيد جداً',
                'teacher_notes' => 'أداء جيد مع بعض الأخطاء البسيطة'
            ]);
            
            $this->info("✅ تم إنشاء الجلسة بنجاح!");
            $this->line("Session ID: {$session->session_id}");
            $this->line("Database ID: {$session->id}");
            $this->line("الدرجة: {$session->grade}");
            $this->line("التقييم: {$session->evaluation}");
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء الجلسة: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function testController()
    {
        $this->info('=== 3. اختبار Controller ===');
        
        try {
            $request = new Request([
                'student_id' => 1,
                'teacher_id' => 1,
                'quran_circle_id' => 1,
                'start_surah_number' => 3,
                'start_verse' => 1,
                'end_surah_number' => 3,
                'end_verse' => 20,
                'recitation_type' => 'مراجعة صغرى',
                'duration_minutes' => 10,
                'grade' => 9.0,
                'evaluation' => 'ممتاز',
                'teacher_notes' => 'أداء رائع'
            ]);
            
            $controller = new RecitationSessionController();
            $response = $controller->store($request);
            
            $this->info("✅ Controller Response:");
            $responseData = json_decode($response->getContent(), true);
            
            if ($responseData['success']) {
                $this->line("✅ نجح إنشاء الجلسة عبر Controller");
                $this->line("Session ID: " . $responseData['session_id']);
                $this->line("Message: " . $responseData['message']);
            } else {
                $this->error("❌ فشل Controller: " . $responseData['message']);
                if (isset($responseData['errors'])) {
                    foreach ($responseData['errors'] as $field => $errors) {
                        $this->error("- {$field}: " . implode(', ', $errors));
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Controller: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function showExistingSessions()
    {
        $this->info('=== 4. عرض جميع الجلسات الموجودة ===');
        
        $sessions = RecitationSession::with(['student', 'teacher', 'circle'])->latest()->get();
        
        $this->line("عدد الجلسات الإجمالي: " . $sessions->count());
        
        if ($sessions->count() > 0) {
            $this->table(
                ['Session ID', 'طالب', 'معلم', 'حلقة', 'نوع التسميع', 'الدرجة', 'التقييم', 'التاريخ'],
                $sessions->map(function ($session) {
                    return [
                        $session->session_id,
                        $session->student ? $session->student->name : 'غير محدد',
                        $session->teacher ? $session->teacher->name : 'غير محدد',
                        $session->circle ? $session->circle->name : 'غير محدد',
                        $session->recitation_type,
                        $session->grade,
                        $session->evaluation,
                        $session->created_at->format('Y-m-d H:i')
                    ];
                })->toArray()
            );
        } else {
            $this->warn("لا توجد جلسات تسميع حالياً");
        }
    }
}
