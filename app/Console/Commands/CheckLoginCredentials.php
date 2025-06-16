<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CheckLoginCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login:check {--email=admin@garb.com} {--password=123456} {--create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص بيانات تسجيل الدخول وإنشاء/إصلاح المستخدم الإداري';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $shouldCreate = $this->option('create');

        $this->info("=== فحص بيانات تسجيل الدخول ===");
        $this->info("البريد الإلكتروني: {$email}");
        $this->info("كلمة المرور: {$password}");
        $this->line("");

        // عرض جميع المستخدمين الموجودين
        $this->info("=== المستخدمون الموجودون في النظام ===");
        $users = User::all();
        
        if ($users->count() == 0) {
            $this->warn("لا يوجد مستخدمون في النظام!");
        } else {
            $this->table(
                ['ID', 'الاسم', 'البريد الإلكتروني', 'اسم المستخدم', 'نشط؟', 'تاريخ الإنشاء'],
                $users->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->username ?? 'غير محدد',
                        $user->is_active ? 'نعم' : 'لا',
                        $user->created_at->format('Y-m-d H:i')
                    ];
                })
            );
        }

        $this->line("");

        // البحث عن المستخدم بالبريد الإلكتروني
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ المستخدم بالبريد الإلكتروني {$email} غير موجود!");
            
            if ($shouldCreate || $this->confirm('هل تريد إنشاء مستخدم جديد؟')) {
                $this->createNewUser($email, $password);
                return;
            }
            
            return;
        }

        $this->info("✅ تم العثور على المستخدم: {$user->name}");
        
        // التحقق من كلمة المرور
        if (Hash::check($password, $user->password)) {
            $this->info("✅ كلمة المرور صحيحة!");
        } else {
            $this->error("❌ كلمة المرور غير صحيحة!");
            
            if ($this->confirm('هل تريد تحديث كلمة المرور؟')) {
                $user->password = Hash::make($password);
                $user->save();
                $this->info("✅ تم تحديث كلمة المرور بنجاح!");
            }
        }

        // التحقق من حالة المستخدم
        if (!$user->is_active) {
            $this->warn("⚠️ المستخدم غير نشط!");
            
            if ($this->confirm('هل تريد تنشيط المستخدم؟')) {
                $user->is_active = true;
                $user->save();
                $this->info("✅ تم تنشيط المستخدم!");
            }
        } else {
            $this->info("✅ المستخدم نشط!");
        }

        // التحقق من اسم المستخدم
        if (empty($user->username)) {
            $this->warn("⚠️ اسم المستخدم فارغ!");
            
            if ($this->confirm('هل تريد إضافة اسم المستخدم؟')) {
                $user->username = 'admin';
                $user->save();
                $this->info("✅ تم إضافة اسم المستخدم: admin");
            }
        }

        $this->line("");
        $this->info("=== ملخص بيانات تسجيل الدخول ===");
        $this->info("البريد الإلكتروني: {$user->email}");
        $this->info("اسم المستخدم: " . ($user->username ?? 'غير محدد'));
        $this->info("كلمة المرور: {$password}");
        $this->info("حالة المستخدم: " . ($user->is_active ? 'نشط' : 'غير نشط'));
        $this->info("رابط تسجيل الدخول: " . url('/admin/login'));
    }

    private function createNewUser($email, $password)
    {
        $this->info("إنشاء مستخدم جديد...");
        
        $username = $this->ask('أدخل اسم المستخدم', 'admin');
        $name = $this->ask('أدخل اسم المستخدم الكامل', 'مدير النظام');
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->info("✅ تم إنشاء المستخدم بنجاح!");
        $this->info("ID: {$user->id}");
        $this->info("الاسم: {$user->name}");
        $this->info("البريد الإلكتروني: {$user->email}");
        $this->info("اسم المستخدم: {$user->username}");
        $this->info("كلمة المرور: {$password}");
    }
}
