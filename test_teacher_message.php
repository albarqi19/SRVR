<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Teacher;
use App\Helpers\WhatsAppHelper;

echo "اختبار إرسال رسالة مع كلمة المرور...\n\n";

// البحث عن معلم لديه رقم هاتف
$teacher = Teacher::where('phone', '!=', null)
    ->where('phone', '!=', '')
    ->where('plain_password', '!=', null)
    ->where('plain_password', '!=', '')
    ->first();

if (!$teacher) {
    echo "❌ لا يوجد معلم لديه رقم هاتف وكلمة مرور\n";
    
    // إنشاء معلم تجريبي
    $teacher = new Teacher();
    $teacher->name = 'معلم تجريبي';
    $teacher->identity_number = '1234567890';
    $teacher->phone = '966530996778'; // نفس الرقم الذي استخدمته
    $teacher->nationality = 'سعودي';
    $teacher->job_title = 'معلم حفظ';
    $teacher->task_type = 'معلم بمكافأة';
    $teacher->circle_type = 'حلقة فردية';
    $teacher->work_time = 'عصر';
    $teacher->password = '123456'; // سيحفظ في plain_password تلقائياً
    // لا نحفظ في قاعدة البيانات، فقط للاختبار
    
    echo "✅ تم إنشاء معلم تجريبي للاختبار\n";
}

echo "📋 بيانات المعلم:\n";
echo "- الاسم: {$teacher->name}\n";
echo "- رقم الهاتف: {$teacher->phone}\n";
echo "- كلمة المرور: {$teacher->plain_password}\n";
echo "- رقم الهوية: {$teacher->identity_number}\n";

echo "\n🧪 اختبار القالب الثابت:\n";
$message = \App\Services\WhatsAppTemplateService::teacherWelcomeWithPasswordMessage(
    $teacher->name,
    'المسجد التجريبي',
    $teacher->plain_password ?? '123456',
    $teacher->identity_number
);

echo "📱 الرسالة التي ستُرسل:\n";
echo "=" . str_repeat("=", 50) . "\n";
echo $message . "\n";
echo "=" . str_repeat("=", 50) . "\n";

echo "\n✅ الآن جرب إنشاء معلم جديد وستصل الرسالة مع كلمة المرور بالتنسيق الصحيح\n";
