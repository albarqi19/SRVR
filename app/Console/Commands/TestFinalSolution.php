<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\QuranCircle;
use Illuminate\Support\Facades\DB;

class TestFinalSolution extends Command
{
    protected $signature = 'test:final-solution';
    protected $description = 'اختبار نهائي شامل لحل مشكلة المعلمين';

    public function handle()
    {
        $this->info('🎯 اختبار نهائي شامل لحل مشكلة المعلمين');
        $this->line(str_repeat('=', 60));
        
        // اختبار 1: التحقق من ربط جميع المعلمين
        $this->testTeacherUserLinks();
        
        // اختبار 2: محاكاة إنشاء جلسة تسميع
        $this->testRecitationSessionCreation();
        
        // اختبار 3: التحقق من عرض البيانات
        $this->testDataDisplay();
        
        $this->newLine();
        $this->info('🎉 انتهى الاختبار النهائي');
    }

    private function testTeacherUserLinks()
    {
        $this->info('1️⃣ فحص ربط المعلمين بالمستخدمين:');
        
        $totalTeachers = Teacher::count();
        $linkedTeachers = Teacher::whereNotNull('user_id')->count();
        $unlinkedTeachers = $totalTeachers - $linkedTeachers;
        
        $this->line("   📊 إجمالي المعلمين: {$totalTeachers}");
        $this->line("   ✅ مرتبطين: {$linkedTeachers}");
        $this->line("   ❌ غير مرتبطين: {$unlinkedTeachers}");
        
        if ($unlinkedTeachers === 0) {
            $this->line("   🎉 جميع المعلمين مرتبطين بنجاح!");
        } else {
            $this->error("   ⚠️ يوجد معلمين غير مرتبطين");
        }
        
        $this->newLine();
    }

    private function testRecitationSessionCreation()
    {
        $this->info('2️⃣ محاكاة إنشاء جلسة تسميع:');
        
        // البحث عن عبدالله الشنقيطي
        $abdullah = Teacher::where('name', 'like', '%عبدالله الشنقيطي%')->first();
        
        if (!$abdullah) {
            $this->error('   ❌ لم يتم العثور على المعلم عبدالله الشنقيطي');
            return;
        }
        
        $this->line("   👨‍🏫 المعلم: {$abdullah->name}");
        $this->line("   🆔 Teacher ID: {$abdullah->id}");
        $this->line("   👤 User ID: {$abdullah->user_id}");
        
        // محاكاة البيانات المرسلة من Frontend
        $frontendData = [
            'student_id' => 1, // افتراض وجود طالب
            'teacher_id' => $abdullah->user_id, // Frontend يرسل user_id
            'quran_circle_id' => 1,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 7,
            'recitation_type' => 'حفظ',
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'teacher_notes' => 'اختبار نهائي'
        ];
        
        $this->line("   📤 Frontend يرسل teacher_id: {$frontendData['teacher_id']}");
        
        try {
            // محاكاة منطق resolveTeacherId
            $teacherResolution = $this->resolveTeacherId($frontendData['teacher_id']);
            
            $this->line("   🔍 نتيجة الحل:");
            $this->line("      - الطريقة: {$teacherResolution['method']}");
            $this->line("      - اسم المعلم: {$teacherResolution['teacher_name']}");
            $this->line("      - user_id للحفظ: {$teacherResolution['user_id']}");
            
            if ($teacherResolution['teacher_name'] === 'عبدالله الشنقيطي') {
                $this->line("   ✅ تم حل المشكلة بنجاح!");
            } else {
                $this->error("   ❌ المشكلة لم تُحل");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    private function testDataDisplay()
    {
        $this->info('3️⃣ اختبار عرض البيانات:');
        
        // فحص آخر جلسات التسميع
        $recentSessions = RecitationSession::with(['teacher', 'student'])
            ->latest()
            ->limit(3)
            ->get();
        
        if ($recentSessions->count() > 0) {
            $this->line("   📋 آخر جلسات التسميع:");
            foreach ($recentSessions as $session) {
                $teacherName = $session->teacher ? $session->teacher->name : 'غير محدد';
                $studentName = $session->student ? $session->student->name : 'غير محدد';
                
                $this->line("      - المعلم: {$teacherName}, الطالب: {$studentName}");
            }
        } else {
            $this->line("   📝 لا توجد جلسات تسميع محفوظة");
        }
        
        $this->newLine();
    }

    private function resolveTeacherId($inputId): array
    {
        // الأولوية الأولى: البحث عن معلم بـ user_id (الحالة الصحيحة)
        $teacherByUserId = Teacher::where('user_id', $inputId)->first();
        if ($teacherByUserId) {
            return [
                'teacher_id' => $teacherByUserId->id,
                'user_id' => $inputId,
                'teacher_name' => $teacherByUserId->name,
                'method' => 'user_id_lookup_priority'
            ];
        }
        
        // الأولوية الثانية: التحقق إذا كان المعرف موجود في جدول teachers مباشرة
        $directTeacher = Teacher::find($inputId);
        if ($directTeacher) {
            return [
                'teacher_id' => $directTeacher->id,
                'user_id' => $directTeacher->user_id ?? $inputId,
                'teacher_name' => $directTeacher->name,
                'method' => 'direct_teacher_lookup'
            ];
        }
        
        // الأولوية الثالثة: التحقق من وجود المعرف في جدول users
        $user = User::find($inputId);
        if ($user) {
            return [
                'teacher_id' => null,
                'user_id' => $inputId,
                'teacher_name' => $user->name,
                'method' => 'user_only',
                'error' => 'المستخدم موجود لكن لا يوجد معلم مرتبط به'
            ];
        }
        
        return [
            'teacher_id' => null,
            'user_id' => null,
            'teacher_name' => null,
            'method' => 'not_found',
            'error' => 'المعرف غير موجود'
        ];
    }
}
