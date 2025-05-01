<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

try {
    // التحقق من اتصال قاعدة البيانات
    echo "التحقق من الاتصال بقاعدة البيانات...\n";
    echo "مزود قاعدة البيانات: " . config('database.default') . "\n";
    echo "اسم قاعدة البيانات: " . config('database.connections.' . config('database.default') . '.database') . "\n";
    echo "اسم المستخدم: " . config('database.connections.' . config('database.default') . '.username') . "\n";
    
    // اختبار الاتصال
    $connection = DB::connection();
    echo "تم الاتصال بقاعدة البيانات بنجاح!\n";
    
    // التحقق من وجود جدول users
    if (Schema::hasTable('users')) {
        echo "جدول المستخدمين (users) موجود.\n";
        
        // عد المستخدمين
        $usersCount = DB::table('users')->count();
        echo "عدد المستخدمين في الجدول: " . $usersCount . "\n";
        
        // التحقق من وجود المستخدم admin
        $adminUser = DB::table('users')->where('email', 'admin@quran-center.com')->orWhere('username', 'admin')->first();
        
        if ($adminUser) {
            echo "تم العثور على المستخدم الإداري.\n";
            echo "اسم المستخدم: " . $adminUser->name . "\n";
            echo "البريد الإلكتروني: " . $adminUser->email . "\n";
            echo "اسم المستخدم (username): " . ($adminUser->username ?? "غير محدد") . "\n";
            
            // إنشاء كلمة مرور جديدة للمستخدم
            $newPassword = 'Admin123!';
            DB::table('users')->where('id', $adminUser->id)->update([
                'password' => Hash::make($newPassword)
            ]);
            
            echo "تم تغيير كلمة مرور المستخدم الإداري إلى: " . $newPassword . "\n";
        } else {
            echo "لا يوجد مستخدم إداري! سيتم إنشاء مستخدم جديد...\n";
            
            // إنشاء مستخدم جديد
            $userId = DB::table('users')->insertGetId([
                'name' => 'مدير النظام',
                'email' => 'admin@system.com',
                'username' => 'sysadmin',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "تم إنشاء مستخدم إداري جديد بنجاح!\n";
            echo "البريد الإلكتروني: admin@system.com\n";
            echo "اسم المستخدم: sysadmin\n";
            echo "كلمة المرور: Admin123!\n";
            
            // التحقق من وجود جدول الأدوار
            if (Schema::hasTable('roles')) {
                echo "جدول الأدوار موجود. سيتم تعيين أدوار للمستخدم الجديد...\n";
                
                // التحقق من وجود جدول علاقة المستخدمين والأدوار
                if (Schema::hasTable('role_user') || Schema::hasTable('model_has_roles')) {
                    $roleTableName = Schema::hasTable('role_user') ? 'role_user' : 'model_has_roles';
                    echo "جدول علاقة الأدوار والمستخدمين الموجود هو: " . $roleTableName . "\n";
                    
                    // البحث عن الأدوار الموجودة
                    $roles = DB::table('roles')->get();
                    echo "الأدوار الموجودة: " . $roles->count() . "\n";
                    
                    foreach ($roles as $role) {
                        echo "- " . $role->name . "\n";
                        
                        // تعيين الدور للمستخدم
                        if ($roleTableName === 'role_user') {
                            DB::table($roleTableName)->insert([
                                'role_id' => $role->id,
                                'user_id' => $userId
                            ]);
                        } else {
                            DB::table($roleTableName)->insert([
                                'role_id' => $role->id,
                                'model_id' => $userId,
                                'model_type' => 'App\\Models\\User'
                            ]);
                        }
                    }
                    
                    echo "تم تعيين جميع الأدوار للمستخدم الجديد.\n";
                } else {
                    echo "جدول علاقة الأدوار والمستخدمين غير موجود!\n";
                }
            } else {
                echo "جدول الأدوار غير موجود في قاعدة البيانات!\n";
            }
        }
        
        // عرض قائمة المستخدمين
        echo "\nقائمة المستخدمين الموجودين في النظام:\n";
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            echo "-----------------------------------\n";
            echo "الاسم: " . $user->name . "\n";
            echo "البريد الإلكتروني: " . $user->email . "\n";
            echo "اسم المستخدم: " . ($user->username ?? "غير محدد") . "\n";
        }
    } else {
        echo "خطأ: جدول المستخدمين (users) غير موجود في قاعدة البيانات!\n";
    }
    
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}