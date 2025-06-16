<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "المستخدمون الموجودون في النظام:\n";
echo "================================\n";

try {
    $users = \App\Models\User::select('id', 'name', 'email', 'created_at')->get();
    
    if ($users->count() > 0) {
        foreach($users as $user) {
            echo "ID: {$user->id}\n";
            echo "الاسم: {$user->name}\n";
            echo "البريد الإلكتروني: {$user->email}\n";
            echo "تاريخ الإنشاء: {$user->created_at}\n";
            echo "------------------------\n";
        }
        echo "إجمالي المستخدمين: " . $users->count() . "\n";
    } else {
        echo "لا يوجد مستخدمون في النظام!\n";
        echo "سنقوم بإنشاء مستخدم تجريبي...\n";
        
        // إنشاء مستخدم تجريبي
        $user = \App\Models\User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@garb.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'email_verified_at' => now(),
        ]);
        
        echo "تم إنشاء مستخدم تجريبي:\n";
        echo "البريد الإلكتروني: admin@garb.com\n";
        echo "كلمة المرور: 123456\n";
    }
    
} catch (\Exception $e) {
    echo "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
}
