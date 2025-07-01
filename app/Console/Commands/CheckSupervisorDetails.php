<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckSupervisorDetails extends Command
{
    protected $signature = 'check:supervisor-details {user_id}';
    protected $description = 'فحص تفاصيل المشرف مع رقم الهوية';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ المستخدم غير موجود!");
            return 1;
        }

        $this->info("🔍 تفاصيل المشرف:");
        $this->line("📋 الاسم: {$user->name}");
        $this->line("📧 الإيميل: {$user->email}");
        $this->line("🆔 المعرف: {$user->id}");
        $this->line("🆔 رقم الهوية: " . ($user->identity_number ?? 'غير محدد'));
        $this->line("📱 الهاتف: " . ($user->phone ?? 'غير محدد'));
        $this->line("✅ نشط: " . ($user->is_active ? 'نعم' : 'لا'));
        
        // عرض الأدوار
        $roles = $user->getRoleNames();
        if ($roles->count() > 0) {
            $this->line("🎭 الأدوار: " . $roles->implode(', '));
        }

        $this->newLine();
        
        if (!$user->identity_number) {
            $this->warn("⚠️ رقم الهوية غير موجود!");
            
            if ($this->confirm('🆕 هل تريد إضافة رقم هوية للمشرف؟')) {
                $identityNumber = $this->ask('🆔 ادخل رقم الهوية', '1234567890');
                
                $user->update([
                    'identity_number' => $identityNumber
                ]);
                
                $this->info("✅ تم إضافة رقم الهوية: {$identityNumber}");
            }
        }

        $this->newLine();
        $this->line("🚀 بيانات تسجيل الدخول:");
        $this->info("🔗 API: POST /api/auth/supervisor/login");
        $this->info("🆔 رقم الهوية: " . ($user->identity_number ?? 'يجب إضافته أولاً'));
        $this->info("🔐 كلمة المرور: [استخدم الكلمة التي حددتها سابقاً]");
        
        $this->newLine();
        $this->comment("📝 مثال على JSON:");
        $this->comment('{');
        $this->comment('  "identity_number": "' . ($user->identity_number ?? 'رقم_الهوية') . '",');
        $this->comment('  "password": "demo123"');
        $this->comment('}');

        return 0;
    }
}
