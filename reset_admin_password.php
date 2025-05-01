<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // البحث عن المستخدم بواسطة البريد الإلكتروني
    $user = User::where('email', 'admin@quran-center.com')->first();
    
    if (!$user) {
        echo "لم يتم العثور على المستخدم admin@quran-center.com\n";
        exit;
    }
    
    // تحديث كلمة المرور
    $user->password = Hash::make('0530996778');
    $user->save();
    
    echo "تم تحديث كلمة المرور بنجاح لـ " . $user->email . "\n";
    echo "يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة: 0530996778\n";
    
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}