<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsAppTemplate;

echo "فحص قالب teacher_welcome_with_password:\n";

$template = WhatsAppTemplate::where('template_key', 'teacher_welcome_with_password')->first();

if ($template) {
    echo "تم العثور على القالب:\n";
    echo "المحتوى: " . $template->content . "\n";
} else {
    echo "لم يتم العثور على القالب في قاعدة البيانات\n";
    echo "سيتم استخدام القالب الثابت\n";
}

echo "\nاختبار القالب الثابت:\n";
$staticTemplate = App\Services\WhatsAppTemplateService::teacherWelcomeWithPasswordMessage(
    'اختبار المعلم',
    'المسجد التجريبي', 
    '123456',
    '1234567890'
);

echo $staticTemplate;
