<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangeUserPassword extends Command
{
    protected $signature = 'user:change-password {email} {password}';
    protected $description = 'تغيير كلمة مرور مستخدم معين';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('password');

        $this->info("🔧 تغيير كلمة مرور المستخدم: {$email}");
        $this->newLine();

        // البحث عن المستخدم
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('❌ لم يتم العثور على مستخدم بهذا البريد الإلكتروني');
            return 1;
        }

        // تغيير كلمة المرور
        try {
            $user->password = Hash::make($newPassword);
            $user->save();

            $this->info('✅ تم تغيير كلمة المرور بنجاح!');
            $this->newLine();
            
            $this->info('📋 تفاصيل المستخدم:');
            $this->info("   🆔 ID: {$user->id}");
            $this->info("   👤 الاسم: {$user->name}");
            $this->info("   📧 البريد: {$user->email}");
            $this->info("   🔑 كلمة المرور الجديدة: {$newPassword}");
            
            $this->newLine();
            $this->info('🎯 يمكنك الآن تسجيل الدخول بهذه البيانات:');
            $this->info("   البريد الإلكتروني: {$user->email}");
            $this->info("   كلمة المرور: {$newPassword}");

            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ خطأ في تغيير كلمة المرور: ' . $e->getMessage());
            return 1;
        }
    }
}
