<?php

require_once 'vendor/autoload.php';

use App\Models\WhatsAppMessage;
use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "🧪 اختبار إنشاء رسالة WhatsApp...\n";
    
    $message = WhatsAppMessage::createNotification(
        '971501234567',
        'مرحباً! هذه رسالة اختبار من نظام Laravel بعد إصلاح المشكلة',
        'اختبار',
        'custom'
    );
    
    echo "✅ تم إنشاء الرسالة بنجاح!\n";
    echo "📱 رقم الهاتف: " . $message->recipient_phone . "\n";
    echo "💬 المحتوى: " . $message->message_content . "\n";
    echo "📂 النوع: " . $message->message_type . "\n";
    echo "🆔 معرف الرسالة: " . $message->id . "\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
