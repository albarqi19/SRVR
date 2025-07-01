<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;

class LinkAllTeachersToUsers extends Command
{
    protected $signature = 'link:all-teachers-users';
    protected $description = 'ربط جميع المعلمين بحسابات المستخدمين';

    public function handle()
    {
        $this->info('🔗 بدء عملية ربط جميع المعلمين بحسابات المستخدمين...');
        $this->newLine();

        // الحصول على جميع المعلمين
        $allTeachers = Teacher::all();
        $linkedCount = 0;
        $createdCount = 0;
        $errorCount = 0;

        $this->info("📊 إجمالي المعلمين: {$allTeachers->count()}");
        $this->newLine();

        foreach ($allTeachers as $teacher) {
            try {
                // التحقق إذا كان المعلم مرتبط بمستخدم
                if ($teacher->user_id) {
                    $this->line("   ✅ {$teacher->name} - مرتبط مسبقاً (User ID: {$teacher->user_id})");
                    $linkedCount++;
                    continue;
                }

                // البحث عن مستخدم موجود بنفس رقم الهوية
                $existingUser = User::where('identity_number', $teacher->identity_number)->first();
                
                if ($existingUser) {
                    // ربط بالمستخدم الموجود
                    $teacher->user_id = $existingUser->id;
                    $teacher->save();
                    $this->line("   🔗 {$teacher->name} - تم الربط بالمستخدم الموجود (ID: {$existingUser->id})");
                    $linkedCount++;
                } else {
                    // إنشاء مستخدم جديد
                    $user = $this->createUserForTeacher($teacher);
                    $teacher->user_id = $user->id;
                    $teacher->save();
                    $this->line("   ➕ {$teacher->name} - تم إنشاء مستخدم جديد (ID: {$user->id})");
                    $createdCount++;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ {$teacher->name} - خطأ: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("📈 النتائج النهائية:");
        $this->info("   🔗 تم الربط بمستخدمين موجودين: {$linkedCount}");
        $this->info("   ➕ تم إنشاء مستخدمين جدد: {$createdCount}");
        $this->info("   ❌ أخطاء: {$errorCount}");
        
        $this->newLine();
        $this->info('🎉 تم إنجاز عملية الربط بنجاح!');
    }

    /**
     * إنشاء مستخدم للمعلم
     */
    private function createUserForTeacher(Teacher $teacher): User
    {
        $email = 'teacher_' . $teacher->id . '@garb.com';
        $username = 'teacher_' . $teacher->id;
        
        // التحقق من عدم وجود البريد مسبقاً
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return $existingUser;
        }

        return User::create([
            'name' => $teacher->name,
            'username' => $username,
            'email' => $email,
            'password' => bcrypt('123456'), // كلمة مرور افتراضية
            'identity_number' => $teacher->identity_number ?? '0000000000',
            'phone' => $teacher->phone ?? '',
            'is_active' => true
        ]);
    }
}
