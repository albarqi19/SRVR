<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CreateUsersForAllTeachers extends Command
{
    protected $signature = 'create:users-for-all-teachers {--force : فرض إنشاء المستخدمين حتى لو كانوا موجودين}';
    protected $description = 'إنشاء مستخدمين لجميع المعلمين في النظام - حل نهائي شامل';

    public function handle()
    {
        $this->info('🚀 إنشاء مستخدمين لجميع المعلمين...');
        $this->newLine();

        // 1. جلب جميع المعلمين
        $teachers = DB::table('teachers')->get();
        
        if ($teachers->isEmpty()) {
            $this->error('❌ لا يوجد معلمين في النظام');
            return;
        }

        $this->info("📊 تم العثور على {$teachers->count()} معلم");
        $this->newLine();

        $createdCount = 0;
        $existingCount = 0;
        $errorCount = 0;

        foreach ($teachers as $teacher) {
            $this->info("🔄 معالجة المعلم: {$teacher->name} (ID: {$teacher->id})");
            
            try {
                // التحقق من وجود مستخدم مرتبط
                $email = 'teacher_' . $teacher->id . '@garb.com';
                $username = 'teacher_' . $teacher->id;
                
                $existingUser = User::where('email', $email)
                                   ->orWhere('username', $username)
                                   ->orWhere('name', $teacher->name)
                                   ->first();

                if ($existingUser && !$this->option('force')) {
                    $this->line("   ✅ موجود مسبقاً: {$existingUser->email} (User ID: {$existingUser->id})");
                    $existingCount++;
                    continue;
                }

                if ($existingUser && $this->option('force')) {
                    $this->line("   🔄 تحديث المستخدم الموجود...");
                    $existingUser->update([
                        'name' => $teacher->name,
                        'email' => $email,
                        'username' => $username,
                        'national_id' => $teacher->identity_number ?? '0000000000',
                        'phone' => $teacher->phone ?? ''
                    ]);
                    $this->line("   ✅ تم التحديث: {$existingUser->email} (User ID: {$existingUser->id})");
                    $existingCount++;
                    continue;
                }

                // إنشاء مستخدم جديد
                $user = User::create([
                    'name' => $teacher->name,
                    'username' => $username,
                    'email' => $email,
                    'password' => bcrypt('123456'), // كلمة مرور افتراضية
                    'national_id' => $teacher->identity_number ?? '0000000000',
                    'phone' => $teacher->phone ?? ''
                ]);

                $this->line("   🎉 تم الإنشاء: {$user->email} (User ID: {$user->id})");
                $createdCount++;

            } catch (\Exception $e) {
                $this->line("   ❌ خطأ: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info('📈 ملخص النتائج:');
        $this->info("   ✅ تم إنشاء: {$createdCount} مستخدم");
        $this->info("   📋 موجود مسبقاً: {$existingCount} مستخدم");
        $this->info("   ❌ أخطاء: {$errorCount} مستخدم");
        
        $this->newLine();
        $this->info('🎯 الآن يمكن استخدام أي من الحلول التالية:');
        $this->info('1. استخدام teacher_id الأصلي (سيتم التحويل التلقائي)');
        $this->info('2. استخدام API للحصول على user_id الصحيح');
        $this->info('3. استخدام user_id مباشرة');
        
        if ($createdCount > 0 || $existingCount > 0) {
            $this->newLine();
            $this->info('🚀 تشغيل اختبار شامل...');
            $this->call('test:all-teachers-mapping');
        }
    }
}
