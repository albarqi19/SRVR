<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUserPassword extends Command
{
    protected $signature = 'check:user-password {user_id}';
    protected $description = 'فحص وإعادة تعيين كلمة مرور المستخدم';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ المستخدم غير موجود!");
            return 1;
        }

        $this->info("🔍 بيانات المستخدم:");
        $this->line("📋 الاسم: {$user->name}");
        $this->line("📧 الإيميل: {$user->email}");
        $this->line("🆔 المعرف: {$user->id}");
        
        $this->newLine();
        $this->line("🔐 كلمة المرور المشفرة:");
        $this->comment(substr($user->password, 0, 60) . "...");

        $this->newLine();
        $this->warn("⚠️ كلمة المرور مشفرة ولا يمكن إظهارها");
        
        // محاولة تجربة كلمات مرور شائعة
        $this->line("🧪 تجربة كلمات مرور شائعة:");
        $commonPasswords = [
            'password',
            '123456',
            'admin',
            'demo',
            'test',
            'garb',
            'quran',
            'supervisor',
            'demo123',
            'admin123'
        ];

        $foundPassword = null;
        foreach ($commonPasswords as $password) {
            if (Hash::check($password, $user->password)) {
                $foundPassword = $password;
                break;
            }
        }

        if ($foundPassword) {
            $this->success("✅ تم العثور على كلمة المرور!");
            $this->info("🔑 كلمة المرور: {$foundPassword}");
        } else {
            $this->error("❌ لم يتم العثور على كلمة المرور من الكلمات الشائعة");
            
            if ($this->confirm('🔄 هل تريد إعادة تعيين كلمة مرور جديدة؟')) {
                $newPassword = $this->ask('🔐 ادخل كلمة المرور الجديدة', 'demo123');
                
                $user->update([
                    'password' => Hash::make($newPassword)
                ]);
                
                $this->success("✅ تم تحديث كلمة المرور بنجاح!");
                $this->info("🔑 كلمة المرور الجديدة: {$newPassword}");
            }
        }

        $this->newLine();
        $this->line("🚀 يمكنك الآن استخدام:");
        $this->info("📧 الإيميل: {$user->email}");
        $this->info("🔐 كلمة المرور: " . ($foundPassword ?: 'الكلمة الجديدة التي أدخلتها'));

        return 0;
    }
}
