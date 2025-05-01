<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

try {
    // إنشاء اسم مستخدم فريد باستخدام الطابع الزمني
    $timestamp = time();
    $username = "testuser_" . $timestamp;
    $email = "testuser_" . $timestamp . "@example.com";
    
    // التحقق من وجود المستخدم
    $userExists = DB::table('users')
        ->where('email', $email)
        ->orWhere('username', $username)
        ->exists();

    if ($userExists) {
        echo "المستخدم موجود بالفعل\n";
    } else {
        // إنشاء مستخدم جديد
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => $email,
            'username' => $username, // إضافة اسم المستخدم المطلوب
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "تم إنشاء المستخدم بنجاح. معرف المستخدم: " . $userId . "\n";
        echo "اسم المستخدم: " . $username . "\n";
        echo "البريد الإلكتروني: " . $email . "\n";
        echo "كلمة المرور: password123\n";
        
        // إضافة أدوار للمستخدم
        try {
            // الحصول على المستخدم
            $user = \App\Models\User::find($userId);
            
            // إذا كانت الأدوار موجودة، قم بإضافتها
            if ($user) {
                // جرب إضافة دور admin
                try {
                    $user->assignRole('admin');
                    echo "تم تعيين دور 'admin' للمستخدم.\n";
                } catch (Exception $e) {
                    echo "فشل في تعيين دور 'admin': " . $e->getMessage() . "\n";
                    
                    // جرب إنشاء الدور إذا لم يكن موجودًا
                    try {
                        Role::create(['name' => 'admin']);
                        $user->assignRole('admin');
                        echo "تم إنشاء وتعيين دور 'admin' للمستخدم.\n";
                    } catch (Exception $e2) {
                        echo "فشل في إنشاء دور 'admin': " . $e2->getMessage() . "\n";
                    }
                }
                
                // تحاول أيضًا إضافة دور supervisor
                try {
                    $user->assignRole('supervisor');
                    echo "تم تعيين دور 'supervisor' للمستخدم.\n";
                } catch (Exception $e) {
                    // لا حاجة لفعل أي شيء إذا فشل
                }
            }
        } catch (Exception $e) {
            echo "حدث خطأ أثناء محاولة تعيين الأدوار: " . $e->getMessage() . "\n";
        }
    }

    // عرض جميع المستخدمين في النظام
    echo "\nقائمة المستخدمين الموجودين في النظام:\n";
    $users = DB::table('users')->get();
    
    foreach ($users as $user) {
        echo "-----------------------------------\n";
        echo "الاسم: " . $user->name . "\n";
        echo "البريد الإلكتروني: " . $user->email . "\n";
        echo "اسم المستخدم: " . ($user->username ?? 'غير محدد') . "\n";
    }
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}