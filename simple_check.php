<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "بدء فحص القالب...\n";
    
    $template = App\Models\WhatsAppTemplate::where('template_key', 'teacher_welcome_with_password')->first();
    
    if ($template) {
        echo "القالب موجود: " . $template->template_key . "\n";
        echo "المحتوى: " . substr($template->template_content, 0, 100) . "...\n";
    } else {
        echo "القالب غير موجود\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
