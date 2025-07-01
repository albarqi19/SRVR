<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CreateUserForTeacher extends Command
{
    protected $signature = 'create:user-for-teacher {teacher_id}';
    protected $description = 'إنشاء مستخدم مرتبط بمعلم موجود لحل مشكلة API validation';

    public function handle()
    {
        $teacherId = $this->argument('teacher_id');
        
        $this->info("🔧 إنشاء مستخدم للمعلم ID: {$teacherId}...");
        $this->newLine();

        // 1. التحقق من وجود المعلم
        $teacher = DB::table('teachers')->where('id', $teacherId)->first();
        
        if (!$teacher) {
            $this->error('❌ المعلم غير موجود');
            return;
        }
        
        $this->info("✅ المعلم موجود: {$teacher->name}");

        // 2. إنشاء مستخدم جديد
        $email = 'teacher_' . $teacherId . '@garb.com';
        $username = 'teacher_' . $teacherId;
        
        // التحقق من عدم وجود البريد مسبقاً
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->info("✅ المستخدم موجود مسبقاً: {$existingUser->email}");
            $this->info("🔑 teacher_id في users: {$existingUser->id}");
            $this->info('🎯 استخدم teacher_id: ' . $existingUser->id . ' في API');
            return;
        }

        $user = User::create([
            'name' => $teacher->name,
            'username' => $username,
            'email' => $email,
            'password' => bcrypt('123456'), // كلمة مرور افتراضية
            'national_id' => $teacher->identity_number ?? '0000000000',
            'phone' => $teacher->phone ?? ''
        ]);
        
        $this->info("✅ تم إنشاء المستخدم: {$user->email}");
        $this->info("🔑 كلمة المرور الافتراضية: 123456");
        $this->info("🆔 User ID الجديد: {$user->id}");
        
        $this->newLine();
        
        // 3. معلومات تسجيل الدخول
        $this->info('🎯 معلومات تسجيل الدخول للـ API:');
        $this->info("   البريد الإلكتروني: {$user->email}");
        $this->info("   كلمة المرور: 123456");
        $this->info("   teacher_id للاستخدام في API: {$user->id}");
        $this->info("   teacher_id في جدول teachers: {$teacherId}");
        
        $this->newLine();
        $this->info('🎉 تم إنشاء المستخدم بنجاح!');
        $this->info('💡 استخدم teacher_id: ' . $user->id . ' في API بدلاً من ' . $teacherId);
        $this->info('🔄 يمكنك الآن إعادة اختبار API إنشاء جلسة التسميع');
    }
}
