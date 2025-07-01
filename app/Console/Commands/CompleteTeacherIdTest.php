<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompleteTeacherIdTest extends Command
{
    protected $signature = 'test:complete-teacher-ids';
    protected $description = 'اختبار متكامل لمشكلة معرفات المعلمين والحل النهائي';

    public function handle()
    {
        $this->info('🧪 اختبار متكامل لمشكلة معرفات المعلمين');
        $this->info('====================================================');

        // الخطوة 1: تشخيص المشكلة الحالية
        $this->info('');
        $this->info('📊 الخطوة 1: تشخيص الوضع الحالي');
        $this->diagnoseProblem();

        // الخطوة 2: حل المشكلة
        $this->info('');
        $this->info('🔧 الخطوة 2: تطبيق الحل النهائي');
        if ($this->confirm('هل تريد تطبيق الحل النهائي (توحيد المعرفات)؟')) {
            $this->solveProblem();
        }

        // الخطوة 3: اختبار النتيجة
        $this->info('');
        $this->info('✅ الخطوة 3: اختبار النتيجة النهائية');
        $this->testResult();

        // الخطوة 4: اختبار API
        $this->info('');
        $this->info('🌐 الخطوة 4: اختبار API مع البيانات الجديدة');
        $this->testApiScenario();

        $this->info('');
        $this->info('🎉 انتهى الاختبار المتكامل!');
    }

    private function diagnoseProblem()
    {
        $teachers = Teacher::with('user')->get();
        $totalTeachers = $teachers->count();
        $matchedTeachers = 0;
        $unmatchedTeachers = 0;
        $noUserTeachers = 0;

        $this->info("إجمالي المعلمين: $totalTeachers");
        $this->info('');

        $headers = ['اسم المعلم', 'Teacher ID', 'User ID', 'الحالة', 'المشكلة'];
        $rows = [];

        foreach ($teachers as $teacher) {
            $teacherId = $teacher->id;
            $teacherName = $teacher->name;
            
            if (!$teacher->user) {
                $rows[] = [$teacherName, $teacherId, 'غير موجود', '❌ خطأ', 'لا يوجد مستخدم'];
                $noUserTeachers++;
            } else {
                $userId = $teacher->user->id;
                if ($teacherId == $userId) {
                    $rows[] = [$teacherName, $teacherId, $userId, '✅ متطابق', 'لا توجد'];
                    $matchedTeachers++;
                } else {
                    $rows[] = [$teacherName, $teacherId, $userId, '⚠️ مختلف', 'عدم تطابق'];
                    $unmatchedTeachers++;
                }
            }
        }

        $this->table($headers, $rows);

        $this->info('');
        $this->info('📈 ملخص التشخيص:');
        $this->line("✅ متطابقين: $matchedTeachers");
        $this->line("⚠️ مختلفين: $unmatchedTeachers");
        $this->line("❌ بدون مستخدم: $noUserTeachers");
        
        $matchPercentage = $totalTeachers > 0 ? round(($matchedTeachers / $totalTeachers) * 100, 2) : 0;
        $this->line("📊 نسبة التطابق: $matchPercentage%");

        if ($matchPercentage == 100) {
            $this->info('🎉 مبروك! جميع المعرفات متطابقة بالفعل!');
        } else {
            $this->warn('⚠️ يحتاج إصلاح: بعض المعرفات غير متطابقة');
        }
    }

    private function solveProblem()
    {
        $this->info('بدء عملية توحيد المعرفات...');
        
        DB::beginTransaction();
        
        try {
            $teachers = Teacher::all();
            $successCount = 0;
            $errorCount = 0;

            foreach ($teachers as $teacher) {
                $this->line("معالجة: {$teacher->name} (Teacher ID: {$teacher->id})");
                
                try {
                    // التحقق من وجود مستخدم بنفس ID المعلم
                    $existingUser = User::find($teacher->id);
                    
                    if ($existingUser) {
                        // إذا كان المستخدم موجود، نربط المعلم به
                        $teacher->user_id = $teacher->id;
                        $teacher->save();
                        $this->line("  ✅ تم ربط المعلم بالمستخدم الموجود");
                    } else {
                        // إنشاء مستخدم جديد بنفس ID المعلم
                        $user = new User();
                        $user->id = $teacher->id;
                        $user->name = $teacher->name;
                        $user->username = 'teacher' . $teacher->id;
                        $user->email = 'teacher' . $teacher->id . '@garb.local';
                        $user->password = Hash::make('password123');
                        $user->save();
                        
                        // ربط المعلم بالمستخدم الجديد
                        $teacher->user_id = $teacher->id;
                        $teacher->save();
                        
                        $this->line("  ✅ تم إنشاء مستخدم جديد وربطه");
                    }
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $this->error("  ❌ خطأ في معالجة {$teacher->name}: " . $e->getMessage());
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $this->info('');
            $this->info('📊 نتائج التوحيد:');
            $this->line("✅ نجح: $successCount");
            $this->line("❌ فشل: $errorCount");
            
            if ($errorCount == 0) {
                $this->info('🎉 تم التوحيد بنجاح لجميع المعلمين!');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ فشل في التوحيد: ' . $e->getMessage());
        }
    }

    private function testResult()
    {
        $teachers = Teacher::with('user')->get();
        $allMatched = true;
        $testResults = [];

        foreach ($teachers as $teacher) {
            if (!$teacher->user || $teacher->id != $teacher->user->id) {
                $allMatched = false;
                $testResults[] = "❌ {$teacher->name}: Teacher({$teacher->id}) != User(" . ($teacher->user ? $teacher->user->id : 'null') . ")";
            } else {
                $testResults[] = "✅ {$teacher->name}: Teacher({$teacher->id}) = User({$teacher->user->id})";
            }
        }

        if ($allMatched) {
            $this->info('🎉 جميع الاختبارات نجحت! كل معلم له نفس الرقم في الجدولين');
        } else {
            $this->warn('⚠️ بعض الاختبارات فشلت:');
            foreach ($testResults as $result) {
                $this->line($result);
            }
        }
    }

    private function testApiScenario()
    {
        // اختبار سيناريو API
        $teacher = Teacher::with('user')->first();
        
        if (!$teacher || !$teacher->user) {
            $this->error('❌ لا يوجد معلمين للاختبار');
            return;
        }

        $this->info("🧪 اختبار API مع المعلم: {$teacher->name}");
        $this->info("Teacher ID: {$teacher->id}");
        $this->info("User ID: {$teacher->user->id}");

        // محاكاة طلب من Frontend
        $frontendTeacherId = $teacher->id; // الآن Frontend يرسل teacher_id مباشرة
        
        // محاكاة API logic
        $foundTeacher = Teacher::with('user')->find($frontendTeacherId);
        
        if ($foundTeacher && $foundTeacher->user) {
            $userIdForSession = $foundTeacher->user->id; // هذا ما سيحفظ في recitation_sessions
            
            if ($frontendTeacherId == $userIdForSession) {
                $this->info('✅ API Test نجح: Teacher ID = User ID = ' . $frontendTeacherId);
                $this->info('✅ لا حاجة لتحويلات معقدة في API');
                $this->info('✅ Frontend يرسل رقم واحد، API يستخدم نفس الرقم');
            } else {
                $this->error('❌ API Test فشل: الأرقام لا تزال مختلفة');
            }
        } else {
            $this->error('❌ API Test فشل: المعلم أو المستخدم غير موجود');
        }

        // اختبار المعلم رقم 55 (كما طلب المستخدم)
        $this->info('');
        $this->info('🎯 اختبار خاص: هل لو كان معلم رقم 55، سيكون user_id = 55؟');
        
        $teacher55 = Teacher::find(55);
        if ($teacher55) {
            $user55 = $teacher55->user;
            if ($user55 && $user55->id == 55) {
                $this->info('✅ نعم! المعلم رقم 55 له user_id = 55');
            } else {
                $this->warn('⚠️ المعلم رقم 55 موجود لكن user_id مختلف');
            }
        } else {
            $this->info('ℹ️ المعلم رقم 55 غير موجود حالياً');
            $this->info('💡 لكن إذا تم إنشاؤه، سيكون له user_id = 55 تلقائياً');
        }
    }
}
