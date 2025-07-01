<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Admin;
use App\Models\Supervisor;
use Spatie\Permission\Models\Role;

class FindSupervisorsCommand extends Command
{
    protected $signature = 'find:supervisors';
    protected $description = 'البحث عن جميع المشرفين وعرض بياناتهم';

    public function handle()
    {
        $this->info('🔍 البحث عن المشرفين في النظام...');
        $this->newLine();

        // البحث في جدول المستخدمين
        $this->line('📋 البحث في جدول المستخدمين (users):');
        $this->line(str_repeat('-', 50));

        try {
            $users = User::all();
            $this->info("إجمالي المستخدمين: {$users->count()}");
            
            foreach ($users as $user) {
                $this->line("المعرف: {$user->id} | الاسم: {$user->name} | الإيميل: {$user->email}");
                
                // التحقق من الأدوار
                if (method_exists($user, 'roles')) {
                    $roles = $user->roles->pluck('name')->toArray();
                    if (!empty($roles)) {
                        $this->comment("  الأدوار: " . implode(', ', $roles));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("خطأ في جدول المستخدمين: " . $e->getMessage());
        }

        $this->newLine();

        // البحث في المشرفين حسب الأدوار
        $this->line('👨‍💼 المشرفين المسجلين في النظام (حسب الأدوار):');
        $this->line(str_repeat('-', 50));

        try {
            $supervisors = User::whereHas('roles', function($query) {
                $query->where('name', 'supervisor');
            })->get();

            if ($supervisors->count() > 0) {
                $this->info("إجمالي المشرفين: {$supervisors->count()}");
                foreach ($supervisors as $supervisor) {
                    $this->line("المعرف: {$supervisor->id} | الاسم: {$supervisor->name} | الإيميل: {$supervisor->email}");
                    $this->comment("  🔐 للدخول: استخدم الإيميل + كلمة المرور (مشفرة في قاعدة البيانات)");
                    
                    // عرض رقم الهوية إذا كان متوفراً
                    if (isset($supervisor->identity_number)) {
                        $this->line("  🆔 رقم الهوية: {$supervisor->identity_number}");
                    }
                }
            } else {
                $this->warn("لم يتم العثور على مشرفين مسجلين!");
            }
        } catch (\Exception $e) {
            $this->error("خطأ في البحث عن المشرفين: " . $e->getMessage());
        }

        $this->newLine();

        // البحث في جدول المشرفين
        $this->line('👨‍🏫 البحث في جدول المشرفين (supervisors):');
        $this->line(str_repeat('-', 50));

        try {
            $supervisors = Supervisor::all();
            $this->info("إجمالي المشرفين: {$supervisors->count()}");
            
            foreach ($supervisors as $supervisor) {
                $this->line("المعرف: {$supervisor->id}");
                $this->line("الاسم: {$supervisor->name}");
                if (isset($supervisor->email)) {
                    $this->line("الإيميل: {$supervisor->email}");
                }
                if (isset($supervisor->phone)) {
                    $this->line("الهاتف: {$supervisor->phone}");
                }
                if (isset($supervisor->identity_number)) {
                    $this->line("رقم الهوية: {$supervisor->identity_number}");
                }
                if (isset($supervisor->password)) {
                    $this->comment("كلمة المرور المشفرة: " . substr($supervisor->password, 0, 20) . "...");
                }
                $this->line(str_repeat('-', 30));
            }
        } catch (\Exception $e) {
            $this->error("خطأ في جدول المشرفين: " . $e->getMessage());
        }

        $this->newLine();

        // البحث عن الأدوار
        $this->line('🎭 البحث في الأدوار (roles):');
        $this->line(str_repeat('-', 50));

        try {
            $roles = Role::all();
            $this->info("إجمالي الأدوار: {$roles->count()}");
            
            foreach ($roles as $role) {
                $this->line("الدور: {$role->name} | الحارس: {$role->guard_name}");
                
                // عرض المستخدمين لكل دور
                if (method_exists($role, 'users')) {
                    $roleUsers = $role->users;
                    if ($roleUsers->count() > 0) {
                        $this->comment("  المستخدمون:");
                        foreach ($roleUsers as $user) {
                            $this->comment("    - {$user->name} ({$user->email})");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("خطأ في الأدوار: " . $e->getMessage());
        }

        $this->newLine();

        // محاولة إنشاء مشرف تجريبي
        $this->line('🆕 إنشاء مشرف تجريبي:');
        $this->line(str_repeat('-', 50));

        if ($this->confirm('هل تريد إنشاء مشرف تجريبي للاختبار؟')) {
            try {
                $demoSupervisor = User::create([
                    'name' => 'مشرف تجريبي',
                    'email' => 'supervisor@demo.com',
                    'password' => bcrypt('123456'),
                    'email_verified_at' => now(),
                ]);

                // إضافة دور المشرف إذا كان موجوداً
                $supervisorRole = Role::where('name', 'supervisor')->first();
                if ($supervisorRole) {
                    $demoSupervisor->assignRole($supervisorRole);
                    $this->success("تم إنشاء المشرف التجريبي بنجاح!");
                    $this->line("الإيميل: supervisor@demo.com");
                    $this->line("كلمة المرور: 123456");
                } else {
                    $this->warning("تم إنشاء المستخدم ولكن دور المشرف غير موجود");
                }

            } catch (\Exception $e) {
                $this->error("خطأ في إنشاء المشرف التجريبي: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('✅ انتهى البحث عن المشرفين');
    }
}
