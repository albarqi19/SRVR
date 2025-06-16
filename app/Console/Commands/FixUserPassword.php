<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إصلاح كلمة مرور مستخدم محدد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("=== إصلاح كلمة مرور المستخدم ===");
        $this->info("البريد الإلكتروني: {$email}");
        $this->info("كلمة المرور الجديدة: {$password}");
        $this->line("");

        // البحث عن المستخدم
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ المستخدم بالبريد الإلكتروني {$email} غير موجود!");
            return;
        }

        $this->info("✅ تم العثور على المستخدم: {$user->name}");
        $this->info("ID: {$user->id}");
        $this->info("اسم المستخدم: " . ($user->username ?? 'غير محدد'));
        $this->info("حالة المستخدم: " . ($user->is_active ? 'نشط' : 'غير نشط'));
        $this->line("");

        // تحديث كلمة المرور
        $user->password = Hash::make($password);
        
        // التأكد من أن المستخدم نشط
        if (!$user->is_active) {
            $user->is_active = true;
            $this->info("🔄 تم تنشيط المستخدم");
        }
        
        // التأكد من وجود email_verified_at
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $this->info("🔄 تم تأكيد البريد الإلكتروني");
        }
        
        // إضافة اسم مستخدم إذا لم يكن موجوداً
        if (empty($user->username)) {
            $user->username = 'user_' . $user->id;
            $this->info("🔄 تم إضافة اسم المستخدم: user_{$user->id}");
        }

        $user->save();

        $this->info("✅ تم تحديث بيانات المستخدم بنجاح!");
        $this->line("");

        // اختبار كلمة المرور الجديدة
        $this->info("=== اختبار كلمة المرور الجديدة ===");
        
        if (Hash::check($password, $user->password)) {
            $this->info("✅ كلمة المرور الجديدة صحيحة!");
        } else {
            $this->error("❌ هناك مشكلة في كلمة المرور الجديدة!");
        }

        // اختبار تسجيل الدخول
        if (\Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password])) {
            $this->info("✅ تسجيل الدخول نجح!");
            \Illuminate\Support\Facades\Auth::logout();
        } else {
            $this->error("❌ تسجيل الدخول فشل!");
        }

        $this->line("");
        $this->info("=== ملخص البيانات المُحدثة ===");
        $this->info("الاسم: {$user->name}");
        $this->info("البريد الإلكتروني: {$user->email}");
        $this->info("اسم المستخدم: {$user->username}");
        $this->info("كلمة المرور: {$password}");
        $this->info("حالة المستخدم: " . ($user->is_active ? 'نشط' : 'غير نشط'));
        $this->info("رابط تسجيل الدخول: " . url('/admin/login'));
    }
}
