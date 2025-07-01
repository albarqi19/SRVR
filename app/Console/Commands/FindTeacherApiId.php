<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindTeacherApiId extends Command
{
    protected $signature = 'find:teacher-api-id {teacher_name?}';
    protected $description = 'البحث عن المعرف الصحيح للمعلم للاستخدام في API';

    public function handle()
    {
        $teacherName = $this->argument('teacher_name');
        
        if ($teacherName) {
            // البحث عن معلم محدد
            $teacher = DB::table('teachers')->where('name', 'like', "%{$teacherName}%")->first();
            
            if (!$teacher) {
                $this->error('❌ المعلم غير موجود');
                return;
            }
            
            $user = DB::table('users')->where('email', 'teacher_' . $teacher->id . '@garb.com')->first();
            
            if ($user) {
                $this->info("✅ معلومات المعلم: {$teacher->name}");
                $this->info("   🔢 ID في جدول teachers: {$teacher->id}");
                $this->info("   🔢 ID في جدول users (للـ API): {$user->id}");
                $this->info("   📧 البريد الإلكتروني: {$user->email}");
                $this->info("   🔑 كلمة المرور: 123456");
                $this->newLine();
                $this->info("🎯 استخدم teacher_id: {$user->id} في API");
            } else {
                $this->warn("⚠️ المعلم موجود في جدول teachers لكن لا يوجد له مستخدم");
                $this->info("💡 قم بتشغيل: php artisan create:user-for-teacher {$teacher->id}");
            }
        } else {
            // عرض جميع المعلمين مع معرفاتهم
            $this->info('📋 قائمة المعلمين ومعرفاتهم للـ API:');
            $this->newLine();
            
            $teachers = DB::table('teachers')
                ->join('users', 'users.email', '=', DB::raw("CONCAT('teacher_', teachers.id, '@garb.com')"))
                ->select('teachers.id as teacher_db_id', 'teachers.name', 'users.id as user_id', 'users.email')
                ->get();
                
            if ($teachers->count() > 0) {
                $this->table(
                    ['اسم المعلم', 'Teachers ID', 'API teacher_id', 'البريد الإلكتروني'],
                    $teachers->map(function ($t) {
                        return [
                            $t->name,
                            $t->teacher_db_id,
                            $t->user_id,
                            $t->email
                        ];
                    })->toArray()
                );
            } else {
                $this->warn('⚠️ لا يوجد معلمين مرتبطين بمستخدمين');
            }
        }
    }
}
