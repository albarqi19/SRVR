<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;

echo "=== اختبار نظام WhatsApp ===\n";

try {
    // إنشاء رسالة جديدة
    $message = WhatsAppMessage::createNotification(
        '971501234567',
        'رسالة اختبار من نظام Laravel',
        'اختبار',
        'custom'
    );
    
    echo "✅ تم إنشاء الرسالة برقم: " . $message->id . "\n";
    echo "📱 رقم الهاتف: " . $message->recipient_phone . "\n";
    echo "💬 الرسالة: " . $message->message_content . "\n";
    echo "📊 الحالة: " . $message->status . "\n";
    
    // محاولة إرسال الرسالة
    echo "\n=== محاولة الإرسال ===\n";
    
    $whatsAppService = app(WhatsAppService::class);
    $result = $whatsAppService->sendMessage($message->recipient_phone, $message->message_content);
    
    if ($result) {
        echo "✅ تم إرسال الرسالة بنجاح!\n";
        $message->markAsSent();
        echo "✅ تم تحديث حالة الرسالة إلى 'مرسلة'\n";
    } else {
        echo "❌ فشل إرسال الرسالة\n";
        $message->markAsFailed('فشل في الإرسال');
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
