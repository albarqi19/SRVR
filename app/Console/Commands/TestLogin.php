<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TestLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار تسجيل الدخول بيانات محددة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("اختبار تسجيل الدخول...");
        $this->info("البريد الإلكتروني: {$email}");
        $this->info("كلمة المرور: {$password}");
        $this->line("");

        // محاولة تسجيل الدخول
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $this->info("✅ نجح تسجيل الدخول!");
            $this->info("مرحباً بك: {$user->name}");
            $this->info("ID: {$user->id}");
            $this->info("البريد الإلكتروني: {$user->email}");
            $this->info("اسم المستخدم: " . ($user->username ?? 'غير محدد'));
            
            // تسجيل الخروج
            Auth::logout();
        } else {
            $this->error("❌ فشل في تسجيل الدخول!");
            
            // التحقق من وجود المستخدم
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("المستخدم بالبريد الإلكتروني {$email} غير موجود!");
                return;
            }
            
            $this->info("المستخدم موجود: {$user->name}");
            
            // التحقق من كلمة المرور يدوياً
            if (Hash::check($password, $user->password)) {
                $this->info("✅ كلمة المرور صحيحة في قاعدة البيانات");
                
                // التحقق من حالة المستخدم
                if (!$user->is_active) {
                    $this->error("❌ المستخدم غير نشط!");
                } else {
                    $this->info("✅ المستخدم نشط");
                    $this->error("❌ مشكلة في إعدادات المصادقة!");
                }
            } else {
                $this->error("❌ كلمة المرور غير صحيحة في قاعدة البيانات");
                
                // عرض تفاصيل كلمة المرور المخزنة
                $this->info("كلمة المرور المخزنة (مُشفرة): " . substr($user->password, 0, 20) . "...");
            }
        }
        
        // اختبار إضافي بـ username إذا كان متوفراً
        if (!empty($user->username ?? '')) {
            $this->line("");
            $this->info("اختبار تسجيل الدخول بـ username...");
            
            $usernameCredentials = [
                'username' => $user->username,
                'password' => $password,
            ];
            
            if (Auth::attempt($usernameCredentials)) {
                $this->info("✅ نجح تسجيل الدخول بـ username!");
                Auth::logout();
            } else {
                $this->error("❌ فشل تسجيل الدخول بـ username");
            }
        }
    }
}
