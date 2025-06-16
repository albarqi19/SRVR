<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsAppTemplateService;

echo "=== اختبار قالب رسالة الترحيب مع كلمة المرور ===\n\n";

// اختبار الدالة مباشرة
$teacherName = "أحمد محمد";
$mosqueName = "مسجد النور";
$password = "123456";
$identityNumber = "1234567890";

echo "البيانات المرسلة:\n";
echo "اسم المعلم: $teacherName\n";
echo "المسجد: $mosqueName\n";
echo "كلمة المرور: $password\n";
echo "رقم الهوية: $identityNumber\n";
echo "============================\n\n";

// اختبار الدالة الرئيسية
$message = WhatsAppTemplateService::teacherWelcomeWithPasswordMessage(
    $teacherName, 
    $mosqueName, 
    $password, 
    $identityNumber
);

echo "الرسالة المولدة:\n";
echo "=================\n";
echo $message;
echo "\n=================\n\n";

// اختبار إذا كانت كلمة المرور موجودة
if (strpos($message, $password) !== false) {
    echo "✅ كلمة المرور موجودة في الرسالة\n";
} else {
    echo "❌ كلمة المرور غير موجودة في الرسالة\n";
}

if (strpos($message, $teacherName) !== false) {
    echo "✅ اسم المعلم موجود في الرسالة\n";
} else {
    echo "❌ اسم المعلم غير موجود في الرسالة\n";
}

if (strpos($message, $mosqueName) !== false) {
    echo "✅ اسم المسجد موجود في الرسالة\n";
} else {
    echo "❌ اسم المسجد غير موجود في الرسالة\n";
}

if (strpos($message, $identityNumber) !== false) {
    echo "✅ رقم الهوية موجود في الرسالة\n";
} else {
    echo "❌ رقم الهوية غير موجود في الرسالة\n";
}

echo "\n=== انتهى الاختبار ===\n";
