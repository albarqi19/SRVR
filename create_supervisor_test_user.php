<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\Facades\Hash;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {    // إنشاء مستخدم مشرف للاختبار
    $user = \App\Models\User::create([
        'name' => 'مشرف الاختبار',
        'email' => 'supervisor@example.com',
        'username' => 'supervisor_test',
        'password' => bcrypt('password'),
        'phone' => '0501234567',
        'is_active' => true,
        'identity_number' => '1234567890'
    ]);

    echo "✅ تم إنشاء المستخدم بنجاح\n";
    echo "الاسم: {$user->name}\n";
    echo "البريد الإلكتروني: {$user->email}\n";
    echo "معرف المستخدم: {$user->id}\n";

    // إنشاء role مشرف إذا لم يكن موجوداً
    $supervisorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'supervisor']);
    echo "✅ تم إنشاء أو العثور على دور المشرف\n";

    // تعيين دور المشرف للمستخدم
    $user->assignRole('supervisor');
    echo "✅ تم تعيين دور المشرف للمستخدم\n";

    // إنشاء permissions إذا لم تكن موجودة
    $permissions = [
        'view_circles',
        'view_students', 
        'view_teachers',
        'transfer_students',
        'view_student_transfer_requests',
        'approve_student_transfers',
        'evaluate_teachers',
        'view_teacher_evaluations',
        'create_teacher_reports'
    ];

    foreach ($permissions as $permission) {
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        $user->givePermissionTo($permission);
    }
    echo "✅ تم تعيين الصلاحيات للمستخدم\n";

    // إنشاء بيانات تجريبية إذا احتجنا
    echo "\n📊 معلومات المستخدم:\n";
    echo "الأدوار: " . $user->getRoleNames()->implode(', ') . "\n";
    echo "الصلاحيات: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";

    echo "\n🎯 بيانات تسجيل الدخول:\n";
    echo "البريد الإلكتروني: supervisor@example.com\n";
    echo "كلمة المرور: password\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
