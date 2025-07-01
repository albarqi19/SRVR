<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use App\Rules\ValidTeacherId;

class TestTeacherUserIntegration extends Command
{
    protected $signature = 'test:teacher-user-integration';
    protected $description = 'اختبار تكامل المعلمين مع المستخدمين';

    public function handle()
    {
        $this->info('🧪 اختبار تكامل المعلمين مع المستخدمين');
        $this->newLine();

        // اختبار 1: إنشاء معلم جديد
        $this->testCreateNewTeacher();
        
        // اختبار 2: اختبار ValidTeacherId rule
        $this->testValidTeacherIdRule();
        
        // اختبار 3: اختبار API endpoint
        $this->testApiEndpoint();
    }

    private function testCreateNewTeacher()
    {
        $this->info('1️⃣ اختبار إنشاء معلم جديد:');
        
        try {
            $teacher = Teacher::create([
                'name' => 'معلم تجريبي للاختبار',
                'identity_number' => '9999999999',
                'phone' => '0501234567',
                'mosque_id' => 1, // افتراض وجود مسجد بـ ID 1
            ]);
            
            $this->line("   ✅ تم إنشاء المعلم - ID: {$teacher->id}");
            
            // التحقق من إنشاء حساب مستخدم
            if ($teacher->user_id) {
                $user = User::find($teacher->user_id);
                $this->line("   ✅ تم إنشاء حساب مستخدم - ID: {$user->id}, Email: {$user->email}");
            } else {
                $this->error("   ❌ لم يتم إنشاء حساب مستخدم للمعلم");
            }
            
            // حذف المعلم التجريبي
            $teacher->user()->delete();
            $teacher->delete();
            $this->line("   🗑️ تم حذف المعلم التجريبي");
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في إنشاء المعلم: {$e->getMessage()}");
        }
        
        $this->newLine();
    }

    private function testValidTeacherIdRule()
    {
        $this->info('2️⃣ اختبار ValidTeacherId rule:');
        
        $rule = new ValidTeacherId();
        
        // اختبار معرف مستخدم صحيح
        $user = User::first();
        if ($user) {
            $passes = $rule->passes('teacher_id', $user->id);
            $this->line("   🧪 اختبار user_id {$user->id}: " . ($passes ? '✅ نجح' : '❌ فشل'));
            if (!$passes) {
                $this->line("      رسالة الخطأ: " . $rule->message());
            }
        }
        
        // اختبار معرف غير موجود
        $rule2 = new ValidTeacherId();
        $passes2 = $rule2->passes('teacher_id', 99999);
        $this->line("   🧪 اختبار معرف غير موجود: " . ($passes2 ? '✅ نجح' : '❌ فشل (متوقع)'));
        
        $this->newLine();
    }

    private function testApiEndpoint()
    {
        $this->info('3️⃣ اختبار API endpoint:');
        
        try {
            // محاولة إنشاء جلسة تسميع تجريبية
            $user = User::first();
            if (!$user) {
                $this->error("   ❌ لا يوجد مستخدمين في النظام");
                return;
            }
            
            $this->line("   📡 محاولة إنشاء جلسة تسميع مع teacher_id: {$user->id}");
            
            // هنا يمكن إضافة اختبار HTTP request فعلي
            $this->line("   💡 لاختبار API كاملاً، استخدم:");
            $this->line("      curl -X POST /api/recitation-sessions \\");
            $this->line("           -H 'Content-Type: application/json' \\");
            $this->line("           -d '{\"teacher_id\": {$user->id}, ...}'");
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في اختبار API: {$e->getMessage()}");
        }
        
        $this->newLine();
    }
}
