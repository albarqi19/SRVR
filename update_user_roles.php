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
use Spatie\Permission\Models\Permission;

try {
    // تحديد المستخدم المراد تعديله - يمكنك تغيير اسم المستخدم أو البريد الإلكتروني حسب الحاجة
    $userEmail = "admin@quran-center.com"; // يمكن تغييره إلى أي مستخدم موجود
    
    // البحث عن المستخدم
    $user = \App\Models\User::where('email', $userEmail)->first();
    
    if (!$user) {
        echo "المستخدم غير موجود. جاري البحث عن المستخدم عن طريق اسم المستخدم 'admin'...\n";
        $user = \App\Models\User::where('username', 'admin')->first();
        
        if (!$user) {
            echo "لا يوجد مستخدم بهذا الاسم أو البريد الإلكتروني.\n";
            
            // عرض قائمة المستخدمين المتوفرين
            echo "\nقائمة المستخدمين الموجودين في النظام:\n";
            $users = DB::table('users')->get();
            
            foreach ($users as $existingUser) {
                echo "-----------------------------------\n";
                echo "الاسم: " . $existingUser->name . "\n";
                echo "البريد الإلكتروني: " . $existingUser->email . "\n";
                echo "اسم المستخدم: " . ($existingUser->username ?? 'غير محدد') . "\n";
            }
            
            exit;
        }
    }
    
    echo "تم العثور على المستخدم: " . $user->name . " (" . $user->email . ")\n";
    
    // جرب إنشاء الأدوار إذا لم تكن موجودة
    $roles = ['admin', 'super-admin', 'supervisor', 'staff', 'teacher', 'student'];
    $createdRoles = [];
    
    foreach ($roles as $roleName) {
        try {
            // محاولة إضافة الأدوار الموجودة
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
                echo "تم إنشاء دور: {$roleName}\n";
                $createdRoles[] = $roleName;
            } else {
                echo "الدور {$roleName} موجود بالفعل\n";
                $createdRoles[] = $roleName;
            }
        } catch (Exception $e) {
            echo "خطأ في إنشاء الدور {$roleName}: " . $e->getMessage() . "\n";
        }
    }
    
    // إضافة الأدوار للمستخدم
    $user->syncRoles([]); // إزالة جميع الأدوار الحالية
    
    foreach ($createdRoles as $role) {
        try {
            $user->assignRole($role);
            echo "تم تعيين دور '{$role}' للمستخدم.\n";
        } catch (Exception $e) {
            echo "خطأ في تعيين الدور {$role}: " . $e->getMessage() . "\n";
        }
    }
    
    // تحديث كلمة المرور للمستخدم إذا كنت ترغب في ذلك
    $user->password = Hash::make('password123');
    $user->save();
    echo "تم تحديث كلمة المرور للمستخدم إلى: password123\n";
    
    // عرض جميع أدوار المستخدم
    echo "\nأدوار المستخدم الحالية:\n";
    foreach ($user->roles as $role) {
        echo "- " . $role->name . "\n";
    }
    
    echo "\nتم تحديث المستخدم بنجاح. يمكنك الآن تسجيل الدخول بالبريد الإلكتروني: " . $user->email . " وكلمة المرور: password123\n";
    
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}