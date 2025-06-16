<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "فحص المستخدمين في النظام:\n";
echo "==========================\n";

$users = \App\Models\User::all();

if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "ID: {$user->id}\n";
        echo "الاسم: {$user->name}\n";
        echo "البريد الإلكتروني: {$user->email}\n";
        echo "اسم المستخدم: " . ($user->username ?? 'غير محدد') . "\n";
        echo "هل المستخدم نشط: " . ($user->is_active ? 'نعم' : 'لا') . "\n";
        echo "تاريخ الإنشاء: {$user->created_at}\n";
        echo "------------------------\n";
    }
    
    echo "إجمالي المستخدمين: " . $users->count() . "\n\n";
    
    // تحديث المستخدم الإداري وضبط كلمة المرور
    echo "تحديث بيانات تسجيل الدخول...\n";
    
    $adminUser = \App\Models\User::where('email', 'admin@garb.com')->first();
    if ($adminUser) {
        $adminUser->password = \Illuminate\Support\Facades\Hash::make('123456');
        $adminUser->username = 'admin';
        $adminUser->is_active = true;
        $adminUser->save();
        
        echo "تم تحديث بيانات المستخدم الإداري:\n";
        echo "البريد الإلكتروني: admin@garb.com\n";
        echo "اسم المستخدم: admin\n";
        echo "كلمة المرور: 123456\n";
        echo "الحالة: نشط\n";
    } else {
        echo "لم يتم العثور على المستخدم الإداري، سنقوم بإنشائه...\n";
        $newAdmin = \App\Models\User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@garb.com',
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        echo "تم إنشاء المستخدم الإداري الجديد:\n";
        echo "البريد الإلكتروني: admin@garb.com\n";
        echo "اسم المستخدم: admin\n";
        echo "كلمة المرور: 123456\n";
    }
    
} else {
    echo "لا يوجد مستخدمون في النظام!\n";
    echo "سيتم إنشاء مستخدم إداري جديد...\n";
    
    $newAdmin = \App\Models\User::create([
        'name' => 'مدير النظام',
        'email' => 'admin@garb.com',
        'username' => 'admin',
        'password' => \Illuminate\Support\Facades\Hash::make('123456'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    
    echo "تم إنشاء المستخدم الإداري:\n";
    echo "البريد الإلكتروني: admin@garb.com\n";
    echo "اسم المستخدم: admin\n";
    echo "كلمة المرور: 123456\n";
}

echo "\nيمكنك الآن تسجيل الدخول باستخدام:\n";
echo "البريد الإلكتروني: admin@garb.com\n";
echo "كلمة المرور: 123456\n";
