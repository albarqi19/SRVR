<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    protected $signature = 'reset:password {user_id} {password=demo123}';
    protected $description = 'إعادة تعيين كلمة مرور المستخدم';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $newPassword = $this->argument('password');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ المستخدم غير موجود!");
            return 1;
        }

        $this->info("🔄 إعادة تعيين كلمة المرور...");
        $this->line("📋 الاسم: {$user->name}");
        $this->line("📧 الإيميل: {$user->email}");
        
        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        $this->info("✅ تم تحديث كلمة المرور بنجاح!");
        $this->newLine();
        
        $this->line("🚀 بيانات تسجيل الدخول:");
        $this->info("📧 الإيميل: {$user->email}");
        $this->info("🔐 كلمة المرور: {$newPassword}");
        
        $this->newLine();
        $this->comment("🔗 API تسجيل الدخول:");
        $this->comment("POST /api/supervisor/login");
        $this->comment("Body:");
        $this->comment('{');
        $this->comment('  "email": "' . $user->email . '",');
        $this->comment('  "password": "' . $newPassword . '"');
        $this->comment('}');

        return 0;
    }
}
