<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
    }

    // عرض جميع المستخدمين في النظام
    echo "\nقائمة المستخدمين الموجودين في النظام:\n";
    $users = DB::table('users')->get();
    
    foreach ($users as $user) {
        echo "-----------------------------------\n";
        echo "الاسم: " . $user->name . "\n";
        echo "البريد الإلكتروني: " . $user->email . "\n";
        echo "اسم المستخدم: " . $user->username . "\n";
    }
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}