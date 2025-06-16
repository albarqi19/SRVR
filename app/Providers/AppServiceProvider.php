<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\RecitationSession;
use App\Observers\TeacherObserver;
use App\Observers\StudentObserver;
use App\Observers\RecitationSessionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل الـ Observers لتوليد كلمات المرور تلقائياً
        Teacher::observe(TeacherObserver::class);
        Student::observe(StudentObserver::class);
        
        // تسجيل Observer لربط جلسات التسميع بتقدم الطلاب
        RecitationSession::observe(RecitationSessionObserver::class);
        
        // تسجيل Observer للحضور لإرسال إشعارات WhatsApp
        \App\Models\Attendance::observe(\App\Observers\AttendanceObserver::class);
    }
}
