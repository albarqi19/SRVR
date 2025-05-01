<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام الواجهات اللازمة
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=======================================================\n";
echo "إنشاء مستخدم للعرض التوضيحي\n";
echo "=======================================================\n\n";

try {
    // إنشاء اسم مستخدم وبريد إلكتروني فريدين
    $timestamp = time();
    $username = "demo_" . $timestamp;
    $email = "demo_" . $timestamp . "@quran-center.com";
    $password = "Demo123456";

    // إنشاء مستخدم جديد
    $user = new User();
    $user->name = "مستخدم العرض التوضيحي";
    $user->email = $email;
    $user->username = $username;
    $user->password = Hash::make($password);
    $user->email_verified_at = now();
    $user->is_active = true;
    $user->save();

    echo "تم إنشاء المستخدم الجديد بنجاح!\n";
    echo "معلومات المستخدم:\n";
    echo "- الاسم: " . $user->name . "\n";
    echo "- اسم المستخدم: " . $user->username . "\n";
    echo "- البريد الإلكتروني: " . $user->email . "\n";
    echo "- كلمة المرور: " . $password . "\n";

    // إنشاء وإسناد جميع الأدوار المتاحة للمستخدم
    $roles = ['super_admin', 'admin', 'supervisor', 'teacher', 'staff', 'student'];
    $assignedRoles = [];

    foreach ($roles as $roleName) {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->assignRole($roleName);
            $assignedRoles[] = $roleName;
        } else {
            echo "الدور {$roleName} غير موجود في النظام. جاري محاولة إنشائه...\n";
            try {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
                $user->assignRole($roleName);
                $assignedRoles[] = $roleName . " (تم إنشاؤه)";
            } catch (Exception $e) {
                echo "فشل في إنشاء الدور {$roleName}: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\nالأدوار المسندة للمستخدم: " . implode(', ', $assignedRoles) . "\n";
    echo "\n=======================================================\n";
    echo "يمكنك الآن استخدام هذا المستخدم لتسجيل الدخول إلى النظام للعرض التوضيحي\n";
    echo "=======================================================\n";

} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
    echo "الخطأ في السطر: " . $e->getLine() . " في الملف " . $e->getFile() . "\n";
}