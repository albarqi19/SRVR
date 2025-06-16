<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CircleSupervisor;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class SupervisorLoginInfo extends Command
{
    protected $signature = 'supervisor:login-info';
    protected $description = 'عرض معلومات تسجيل دخول المشرفين ومسارات API';

    public function handle()
    {
        $this->info('=== معلومات تسجيل دخول المشرفين ===');
        $this->line('');

        // 1. المشرفون في جدول Users (لنظام Filament)
        $this->info('1. المشرفون في جدول Users (لنظام Filament):');
        $this->line('-------------------------------------------');
        
        $userSupervisors = User::role('supervisor')->get();
        
        if ($userSupervisors->count() > 0) {
            foreach ($userSupervisors as $supervisor) {
                $this->line("الاسم: {$supervisor->name}");
                $this->line("البريد الإلكتروني: {$supervisor->email}");
                $this->line("رقم الهوية: " . ($supervisor->identity_number ?? 'غير محدد'));
                $this->line("الحالة: " . ($supervisor->is_active ? 'نشط' : 'غير نشط'));
                $this->line("عدد الحلقات المشرف عليها: " . $supervisor->circleSupervisors()->active()->count());
                $this->line('---');
            }
        } else {
            $this->warn('لا يوجد مشرفون في جدول Users');
        }

        $this->line('');

        // 2. المعلمون الذين لديهم دور مشرف في جدول Teachers
        $this->info('2. المعلمون الذين لديهم دور مشرف في جدول Teachers:');
        $this->line('---------------------------------------------------');
        
        $teacherSupervisors = Teacher::whereIn('task_type', ['مشرف', 'مساعد مشرف'])
            ->where('is_active_user', true)
            ->get();
        
        if ($teacherSupervisors->count() > 0) {
            foreach ($teacherSupervisors as $teacher) {
                $this->line("الاسم: {$teacher->name}");
                $this->line("رقم الهوية: {$teacher->identity_number}");
                $this->line("نوع المهمة: {$teacher->task_type}");
                $this->line("الحالة: " . ($teacher->is_active_user ? 'نشط' : 'غير نشط'));
                $this->line("المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد'));
                $this->line("الحلقة: " . ($teacher->quranCircle ? $teacher->quranCircle->name : 'غير محدد'));
                $this->line('---');
            }
        } else {
            $this->warn('لا يوجد معلمون بدور مشرف في جدول Teachers');
        }

        $this->line('');

        // 3. مسارات API للمشرفين
        $this->info('3. مسارات API للمشرفين:');
        $this->line('---------------------------');
        
        $this->line('أ) تسجيل الدخول:');
        $this->line('   POST /api/auth/supervisor/login');
        $this->line('   البيانات المطلوبة:');
        $this->line('   - identity_number: رقم الهوية');
        $this->line('   - password: كلمة المرور');
        $this->line('');
        
        $this->line('ب) قائمة المشرفين:');
        $this->line('   GET /api/supervisors');
        $this->line('');
        
        $this->line('ج) تفاصيل مشرف محدد:');
        $this->line('   GET /api/supervisors/{id}');
        $this->line('');
        
        $this->line('د) لوحة تحكم المشرف:');
        $this->line('   GET /api/supervisor/dashboard');
        $this->line('');
        
        $this->line('هـ) حلقات المشرف:');
        $this->line('   GET /api/supervisor/circles');
        $this->line('');

        // 4. طريقة تسجيل الدخول في لوحة التحكم
        $this->info('4. تسجيل الدخول في لوحة التحكم Filament:');
        $this->line('-------------------------------------------');
        $this->line('الرابط: http://127.0.0.1:8000/admin/login');
        $this->line('');
        $this->line('يمكن للمشرفين تسجيل الدخول باستخدام:');
        $this->line('- البريد الإلكتروني وكلمة المرور');
        $this->line('- أو اسم المستخدم وكلمة المرور');
        $this->line('');

        // 5. عرض بيانات المشرف التجريبي
        $this->info('5. بيانات المشرف التجريبي:');
        $this->line('---------------------------');
        
        $demoSupervisor = User::where('email', 'supervisor@test.com')->first();
        if ($demoSupervisor) {
            $this->info("تم إنشاء مشرف تجريبي:");
            $this->line("الاسم: {$demoSupervisor->name}");
            $this->line("البريد الإلكتروني: {$demoSupervisor->email}");
            $this->line("رقم الهوية: {$demoSupervisor->identity_number}");
            $this->line("كلمة المرور: supervisor123");
        } else {
            $this->warn('لم يتم العثور على المشرف التجريبي');
        }

        $this->line('');

        // 6. إرشادات البحث في لوحة التحكم
        $this->info('6. كيفية العثور على بيانات المشرفين في لوحة التحكم:');
        $this->line('------------------------------------------------');
        $this->line('أ) تسجيل الدخول إلى: http://127.0.0.1:8000/admin/login');
        $this->line('ب) الانتقال إلى: التعليمية > مشرفي الحلقات');
        $this->line('ج) أو الانتقال إلى: إدارة المستخدمين > المستخدمين');
        $this->line('د) استخدام الفلترة حسب الدور: supervisor');
        $this->line('');

        $this->success('تم عرض جميع معلومات المشرفين بنجاح!');
    }
}
