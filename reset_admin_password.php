<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

try {
    // البحث عن المستخدم بواسطة البريد الإلكتروني
    $user = User::where('email', 'admin@quran-center.com')->first();
    
    if (!$user) {
        // إنشاء مستخدم جديد إذا لم يكن موجوداً
        $user = new User();
        $user->name = 'مدير النظام';
        $user->email = 'admin@quran-center.com';
        $user->username = 'admin';
        $user->password = Hash::make('0530996778');
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();
        
        echo "تم إنشاء مستخدم مدير النظام بنجاح.\n";
        
        // التحقق من وجود دور super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        if (!$superAdminRole) {
            // إنشاء دور super_admin إذا لم يكن موجوداً
            $superAdminRole = Role::create(['name' => 'super_admin']);
            echo "تم إنشاء دور super_admin.\n";
        }
        
        // إسناد دور مدير النظام للمستخدم
        $user->assignRole('super_admin');
        echo "تم إسناد دور super_admin للمستخدم.\n";
    } else {
        // تحديث كلمة المرور للمستخدم الموجود
        $user->password = Hash::make('0530996778');
        $user->save();
        
        echo "تم تحديث كلمة المرور بنجاح لـ " . $user->email . "\n";
    }
    
    echo "يمكنك الآن تسجيل الدخول باستخدام:\n";
    echo "البريد الإلكتروني: admin@quran-center.com\n";
    echo "كلمة المرور: 0530996778\n";
    
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}