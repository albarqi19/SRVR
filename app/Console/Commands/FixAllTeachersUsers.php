<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Teacher;

class FixAllTeachersUsers extends Command
{
    protected $signature = 'fix:all-teachers-users';
    protected $description = 'إنشاء حسابات مستخدمين لجميع المعلمين الذين ليس لديهم حسابات';

    public function handle()
    {
        $this->info('🚀 بدء عملية إصلاح حسابات المعلمين...');
        $this->newLine();

        // الحصول على المعلمين الذين ليس لديهم حساب مستخدم
        $teachersWithoutUsers = $this->getTeachersWithoutUsers();
        
        if ($teachersWithoutUsers->isEmpty()) {
            $this->info('✅ جميع المعلمين لديهم حسابات مستخدمين');
            return;
        }

        $this->info("📊 تم العثور على {$teachersWithoutUsers->count()} معلم بدون حساب مستخدم");
        $this->newLine();

        $this->table(
            ['معرف المعلم', 'الاسم', 'رقم الهوية', 'الهاتف'],
            $teachersWithoutUsers->map(function ($teacher) {
                return [
                    $teacher->id,
                    $teacher->name,
                    $teacher->identity_number ?? 'غير محدد',
                    $teacher->phone ?? 'غير محدد'
                ];
            })
        );

        if (!$this->confirm('هل تريد إنشاء حسابات مستخدمين لهؤلاء المعلمين؟')) {
            $this->info('تم إلغاء العملية');
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($teachersWithoutUsers as $teacher) {
            try {
                $user = $this->createUserForTeacher($teacher);
                
                // ربط المعلم بالمستخدم
                $teacher->user_id = $user->id;
                $teacher->save();
                
                $this->line("✅ {$teacher->name} - تم إنشاء المستخدم (ID: {$user->id})");
                $successCount++;
                
            } catch (\Exception $e) {
                $this->error("❌ {$teacher->name} - خطأ: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("📈 النتائج:");
        $this->info("   ✅ نجح: {$successCount}");
        $this->info("   ❌ فشل: {$errorCount}");
        
        if ($successCount > 0) {
            $this->newLine();
            $this->info('🎉 تم إصلاح مشكلة تسجيل المعلمين بنجاح!');
            $this->info('الآن سيتم إنشاء حساب مستخدم تلقائياً لأي معلم جديد');
        }
    }

    /**
     * الحصول على المعلمين الذين ليس لديهم حساب مستخدم
     */
    private function getTeachersWithoutUsers()
    {
        return Teacher::leftJoin('users', function($join) {
            $join->on('teachers.identity_number', '=', 'users.identity_number')
                 ->orWhere(function($query) {
                     $query->whereRaw("users.email LIKE CONCAT('teacher_', teachers.id, '@garb.com')");
                 });
        })
        ->whereNull('users.id')
        ->select('teachers.*')
        ->get();
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
