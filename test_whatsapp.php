<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار نظام WhatsApp ===\n";

try {
    // عرض الإحصائيات الحالية
    echo "\n📊 الإحصائيات الحالية:\n";
    $pendingCount = WhatsAppMessage::where('status', 'pending')->count();
    $failedCount = WhatsAppMessage::where('status', 'failed')->count();
    $sentCount = WhatsAppMessage::where('status', 'sent')->count();
    $totalCount = WhatsAppMessage::count();
    
    echo "- إجمالي الرسائل: {$totalCount}\n";
    echo "- الرسائل المعلقة: {$pendingCount}\n";
    echo "- الرسائل الفاشلة: {$failedCount}\n";
    echo "- الرسائل المرسلة: {$sentCount}\n";
    
    // إنشاء رسالة اختبار جديدة
    echo "\n🧪 إنشاء رسالة اختبار...\n";
    $message = WhatsAppMessage::createNotification(
        'teacher',
        null,
        '966501234567',
        'رسالة اختبار من نظام Laravel - ' . date('Y-m-d H:i:s'),
        'test'
    );
    
    echo "✅ تم إنشاء الرسالة برقم: " . $message->id . "\n";
    echo "📱 رقم الهاتف: " . $message->phone_number . "\n";
    echo "💬 الرسالة: " . $message->content . "\n";
    echo "📊 الحالة: " . $message->status . "\n";
    
    // محاولة معالجة الرسائل المعلقة
    echo "\n🔄 معالجة الرسائل المعلقة...\n";
    $pendingMessages = WhatsAppMessage::where('status', 'pending')->get();
    echo "عدد الرسائل المعلقة للمعالجة: " . $pendingMessages->count() . "\n";
    
    foreach ($pendingMessages->take(5) as $msg) {
        echo "- معالجة رسالة رقم: {$msg->id} للهاتف: {$msg->phone_number}\n";
        
        try {
            // إضافة الرسالة إلى قائمة الانتظار
            \App\Jobs\SendWhatsAppMessage::dispatch($msg->id);
            echo "  ✅ تم إضافة الرسالة إلى قائمة الانتظار\n";
        } catch (Exception $e) {
            echo "  ❌ خطأ في إضافة الرسالة: " . $e->getMessage() . "\n";
        }
    }
    
    // عرض إحصائيات قاعدة البيانات
    echo "\n📋 إحصائيات قائمة الانتظار:\n";    $queueJobs = DB::table('jobs')->count();
    $failedJobs = DB::table('failed_jobs')->count();
    echo "- المهام في القائمة: {$queueJobs}\n";
    echo "- المهام الفاشلة: {$failedJobs}\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
