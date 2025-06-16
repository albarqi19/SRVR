<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// فحص القالب
$template = App\Models\WhatsAppTemplate::where('template_key', 'teacher_welcome_with_password')->first();

if ($template) {
    echo "✅ القالب موجود:\n";
    echo "المفتاح: " . $template->template_key . "\n";
    echo "المحتوى: " . $template->template_content . "\n\n";
    
    // اختبار استبدال المتغيرات
    $testVariables = [
        'teacher_name' => 'أحمد محمد',
        'mosque_name' => 'جامع الاختبار',
        'password' => '123456',
        'identity_number' => '1234567890'
    ];
    
    echo "🔧 اختبار استبدال المتغيرات:\n";
    $processedContent = $template->getProcessedContent($testVariables);
    echo "المحتوى بعد الاستبدال:\n" . $processedContent . "\n";
    
} else {
    echo "❌ القالب غير موجود\n";
    
    // فحص جميع القوالب الموجودة
    $allTemplates = App\Models\WhatsAppTemplate::all();
    echo "القوالب الموجودة:\n";
    foreach ($allTemplates as $t) {
        echo "- " . $t->template_key . "\n";
    }
}
