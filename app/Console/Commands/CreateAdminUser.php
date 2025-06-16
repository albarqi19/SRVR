<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--email=admin@garb.com} {--password=123456} {--name=مدير النظام}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء مستخدم إداري جديد للوحة التحكم';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // التحقق من وجود المستخدم
        if (User::where('email', $email)->exists()) {
            $this->error("المستخدم بالبريد الإلكتروني {$email} موجود بالفعل!");
            
            if ($this->confirm('هل تريد تحديث كلمة المرور للمستخدم الموجود؟')) {
                $user = User::where('email', $email)->first();
                $user->password = Hash::make($password);
                $user->save();
                
                $this->info("تم تحديث كلمة المرور للمستخدم: {$email}");
                $this->info("كلمة المرور الجديدة: {$password}");
            }
            return;
        }

        // إنشاء المستخدم الجديد
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => 'admin', // إضافة اسم المستخدم
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->info("تم إنشاء المستخدم الإداري بنجاح!");
        $this->info("الاسم: {$name}");
        $this->info("البريد الإلكتروني: {$email}");
        $this->info("كلمة المرور: {$password}");
        $this->info("يمكنك الآن تسجيل الدخول إلى لوحة التحكم على: /admin");
    }
}
