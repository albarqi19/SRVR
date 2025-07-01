<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;

class FixTeacherUserConnection extends Command
{
    protected $signature = 'fix:teacher-user {teacher_id}';
    protected $description = 'ربط المعلم بمستخدم في النظام لحل مشكلة validation.exists';

    public function handle()
    {
        $teacherId = $this->argument('teacher_id');
        
        $this->info("🔧 إصلاح ربط المعلم ID: {$teacherId} بمستخدم...");
        $this->newLine();

        // 1. التحقق من وجود المعلم
        $teacher = DB::table('teachers')->where('id', $teacherId)->first();
        
        if (!$teacher) {
            $this->error('❌ المعلم غير موجود');
            return;
        }
        
        $this->info("✅ المعلم موجود: {$teacher->name}");
        
        // 2. التحقق من وجود user_id
        if (isset($teacher->user_id) && $teacher->user_id) {
            $this->info("✅ المعلم مرتبط بالمستخدم ID: {$teacher->user_id}");
            
            $user = DB::table('users')->where('id', $teacher->user_id)->first();
            if ($user) {
                $this->info("✅ المستخدم موجود: {$user->name} ({$user->email})");
                $this->info('🎉 لا توجد مشكلة في الربط');
                return;
            } else {
                $this->warn('⚠️ المستخدم المرتبط غير موجود، سيتم إنشاء مستخدم جديد');
            }
        } else {
            $this->info('📝 المعلم غير مرتبط بأي مستخدم، سيتم إنشاء مستخدم جديد');
        }
        
        $this->newLine();

        // 3. إنشاء مستخدم جديد للمعلم
        $this->info('3️⃣ إنشاء مستخدم جديد للمعلم...');
        
        $email = 'teacher_' . $teacherId . '@garb.com';
        $username = 'teacher_' . $teacherId;
        
        // التحقق من عدم وجود البريد مسبقاً
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->info("✅ المستخدم موجود مسبقاً: {$existingUser->email}");
            $user = $existingUser;
        } else {
            $user = User::create([
                'name' => $teacher->name,
                'username' => $username,
                'email' => $email,
                'password' => bcrypt('123456'), // كلمة مرور افتراضية
                'national_id' => $teacher->identity_number ?? '0000000000',
                'role' => 'teacher',
                'phone' => $teacher->phone ?? ''
            ]);
            
            $this->info("✅ تم إنشاء المستخدم: {$user->email}");
            $this->info('🔑 كلمة المرور الافتراضية: 123456');
        }
        
        $this->newLine();

        // 4. ربط المعلم بالمستخدم
        $this->info('4️⃣ ربط المعلم بالمستخدم...');
        
        DB::table('teachers')
            ->where('id', $teacherId)
            ->update(['user_id' => $user->id]);
            
        $this->info('✅ تم ربط المعلم بالمستخدم بنجاح');
        
        $this->newLine();

        // 5. التحقق من النتيجة
        $this->info('5️⃣ التحقق من النتيجة...');
        
        $updatedTeacher = DB::table('teachers')->where('id', $teacherId)->first();
        $this->info("✅ user_id للمعلم: {$updatedTeacher->user_id}");
        
        $this->newLine();
        
        // 6. معلومات تسجيل الدخول
        $this->info('🎯 معلومات تسجيل الدخول للـ API:');
        $this->info("   البريد الإلكتروني: {$user->email}");
        $this->info("   كلمة المرور: 123456");
        $this->info("   teacher_id: {$teacherId}");
        
        $this->newLine();
        $this->info('🎉 تم إصلاح المشكلة بنجاح!');
        $this->info('🔄 يمكنك الآن إعادة اختبار API إنشاء جلسة التسميع');
    }
}
