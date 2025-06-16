<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class FixFilamentAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament:fix-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إصلاح مشكلة مصادقة Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== تشخيص مشكلة مصادقة Filament ===");
        
        // فحص إعدادات المصادقة
        $this->info("=== إعدادات المصادقة ===");
        $this->info("Default Guard: " . Config::get('auth.defaults.guard'));
        $this->info("Web Guard Driver: " . Config::get('auth.guards.web.driver'));
        $this->info("Web Guard Provider: " . Config::get('auth.guards.web.provider'));
        $this->info("Users Provider Driver: " . Config::get('auth.providers.users.driver'));
        $this->info("Users Provider Model: " . Config::get('auth.providers.users.model'));
        $this->line("");
        
        // التحقق من نموذج User
        $this->info("=== فحص نموذج User ===");
        $userModel = Config::get('auth.providers.users.model');
        $this->info("User Model: {$userModel}");
        
        if (class_exists($userModel)) {
            $this->info("✅ نموذج User موجود");
            
            // التحقق من الحقول المطلوبة
            $user = new $userModel;
            $fillable = $user->getFillable();
            $this->info("Fillable Fields: " . implode(', ', $fillable));
            
            // التحقق من Hidden Fields
            $hidden = $user->getHidden();
            $this->info("Hidden Fields: " . implode(', ', $hidden));
            
        } else {
            $this->error("❌ نموذج User غير موجود!");
        }
        
        $this->line("");
        
        // إنشاء مستخدم اختبار جديد مع بيانات مبسطة
        $this->info("=== إنشاء مستخدم اختبار مبسط ===");
        
        $email = 'test@filament.com';
        $password = 'password';
        
        // حذف المستخدم إذا كان موجوداً
        User::where('email', $email)->delete();
        
        try {
            $user = User::create([
                'name' => 'Filament Test User',
                'email' => $email,
                'username' => 'filament_test',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $this->info("✅ تم إنشاء مستخدم الاختبار بنجاح!");
            $this->info("البريد الإلكتروني: {$email}");
            $this->info("كلمة المرور: {$password}");
            
        } catch (\Exception $e) {
            $this->error("❌ فشل في إنشاء مستخدم الاختبار: " . $e->getMessage());
        }
        
        $this->line("");
        
        // إصلاح المستخدم الرئيسي
        $this->info("=== إصلاح المستخدم الرئيسي ===");
        
        $adminEmail = 'admin@garb.com';
        $adminPassword = '123456';
        
        $admin = User::where('email', $adminEmail)->first();
        
        if ($admin) {
            $this->info("تحديث بيانات المستخدم الرئيسي...");
            
            $admin->update([
                'name' => 'مدير النظام المُحدث',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'is_active' => true,
                'username' => 'admin_updated',
            ]);
            
            $this->info("✅ تم تحديث المستخدم الرئيسي بنجاح!");
            $this->info("البريد الإلكتروني: {$adminEmail}");
            $this->info("كلمة المرور: {$adminPassword}");
            $this->info("اسم المستخدم: admin_updated");
        }
        
        $this->line("");
        $this->info("=== اختبار تسجيل الدخول ===");
        
        // اختبار تسجيل الدخول بـ Laravel Auth
        if (\Illuminate\Support\Facades\Auth::attempt(['email' => $adminEmail, 'password' => $adminPassword])) {
            $this->info("✅ تسجيل الدخول بـ Laravel Auth نجح!");
            \Illuminate\Support\Facades\Auth::logout();
        } else {
            $this->error("❌ تسجيل الدخول بـ Laravel Auth فشل!");
        }
        
        $this->line("");
        $this->info("=== الحلول المقترحة ===");
        $this->info("1. امسح ذاكرة التخزين المؤقت: php artisan cache:clear");
        $this->info("2. امسح إعدادات التكوين: php artisan config:clear");
        $this->info("3. أعد تشغيل الخادم: php artisan serve");
        $this->info("4. جرب تسجيل الدخول بالبيانات المُحدثة");
        $this->info("5. تأكد من عدم وجود JavaScript errors في المتصفح");
    }
}
