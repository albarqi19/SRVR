<?php

require_once 'vendor/autoload.php';

// تحميل البيئة
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WhatsAppTemplate;

echo "إنشاء/تحديث قالب teacher_welcome_with_password...\n";

$template = WhatsAppTemplate::updateOrCreate(
    ['template_key' => 'teacher_welcome_with_password'],
    [
        'template_name' => 'ترحيب المعلم الجديد مع كلمة المرور',
        'content' => "أهلا بالأستاذ {teacher_name} 📚

تم إضافتك بنجاح في منصة غرب لإدارة حلقات القرآن الكريم
المسجد: {mosque_name}

بارك الله فيك وجعل عملك في خدمة كتاب الله في ميزان حسناتك 🤲
يمكنك الدخول من هنا
appgarb.vercel.app
برقم الهوية الخاصة بك وكلمة المرور التالية :
{password}",
        'description' => 'رسالة ترحيب للمعلم الجديد تحتوي على كلمة المرور',
        'variables' => json_encode(['teacher_name', 'mosque_name', 'password', 'identity_number']),
        'category' => 'welcome',
        'is_active' => true,
    ]
);

echo "تم إنشاء/تحديث القالب بنجاح!\n";
echo "Template Key: " . $template->template_key . "\n";
echo "Content Preview: " . substr($template->content, 0, 100) . "...\n";

// اختبار دالة getProcessedContent
echo "\nاختبار دالة getProcessedContent:\n";
$processed = $template->getProcessedContent([
    'teacher_name' => 'أحمد محمد',
    'mosque_name' => 'جامع الاختبار',
    'password' => '123456',
    'identity_number' => '1234567890'
]);

echo "المحتوى المعالج:\n";
echo $processed . "\n";
