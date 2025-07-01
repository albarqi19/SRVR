<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FindSupervisorsSimple extends Command
{
    protected $signature = 'find:supervisors-simple';
    protected $description = 'البحث عن المشرفين وبياناتهم للدخول';

    public function handle()
    {
        $this->info('🔍 البحث عن المشرفين في النظام...');
        $this->newLine();

        // البحث عن المشرفين
        $supervisors = User::whereHas('roles', function($query) {
            $query->where('name', 'supervisor');
        })->get();

        $this->line('👨‍💼 المشرفين المسجلين في النظام:');
        $this->line(str_repeat('=', 60));

        if ($supervisors->count() > 0) {
            foreach ($supervisors as $supervisor) {
                $this->info("📋 اسم المشرف: {$supervisor->name}");
                $this->comment("📧 الإيميل: {$supervisor->email}");
                $this->comment("🆔 المعرف: {$supervisor->id}");
                
                // عرض الأدوار
                $roles = $supervisor->getRoleNames();
                if ($roles->count() > 0) {
                    $this->line("🎭 الأدوار: " . $roles->implode(', '));
                }
                
                $this->line(str_repeat('-', 40));
            }
        } else {
            $this->warn('❌ لم يتم العثور على مشرفين!');
        }

        $this->newLine();
        $this->line('📱 معلومات تسجيل الدخول:');
        $this->line(str_repeat('=', 60));
        $this->info('🔗 API تسجيل الدخول: POST /api/supervisor/login');
        $this->info('📋 البيانات المطلوبة:');
        $this->comment('   {');
        $this->comment('     "email": "الإيميل",');
        $this->comment('     "password": "كلمة المرور"');
        $this->comment('   }');

        $this->newLine();
        $this->line('🎯 المشرفين المتاحين للاختبار:');
        $this->info('• demo_1749270301@quran-center.com (مستخدم العرض التوضيحي)');
        $this->info('• supervisor@test.com (مشرف تجريبي)');
        $this->info('• admin@system.com (مدير النظام)');

        $this->newLine();
        $this->warn('⚠️ ملاحظة: كلمات المرور مشفرة في قاعدة البيانات');
        $this->comment('💡 إذا لم تعرف كلمة المرور، يمكنك إنشاء مشرف جديد أو إعادة تعيين كلمة المرور');

        // عرض إنشاء مشرف تجريبي
        $this->newLine();
        if ($this->confirm('🆕 هل تريد إنشاء مشرف تجريبي جديد بكلمة مرور معروفة؟')) {
            $this->createTestSupervisor();
        }

        return 0;
    }

    private function createTestSupervisor()
    {
        try {
            // التحقق من وجود المشرف
            $email = 'test.supervisor@garb.com';
            $existingSupervisor = User::where('email', $email)->first();
            
            if ($existingSupervisor) {
                $this->warning("⚠️ المشرف موجود مسبقاً: {$existingSupervisor->name}");
                $this->info("📧 الإيميل: {$email}");
                return;
            }

            // إنشاء مشرف جديد
            $supervisor = User::create([
                'name' => 'مشرف اختبار جديد',
                'email' => $email,
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);

            // إضافة دور المشرف
            $supervisorRole = Role::where('name', 'supervisor')->first();
            if ($supervisorRole) {
                $supervisor->assignRole($supervisorRole);
            }

            $this->success('✅ تم إنشاء المشرف التجريبي بنجاح!');
            $this->info("📧 الإيميل: {$email}");
            $this->info("🔐 كلمة المرور: password123");
            $this->comment("🚀 يمكنك الآن استخدام هذه البيانات لتسجيل الدخول");

        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء المشرف: " . $e->getMessage());
        }
    }
}
