<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "🔍 فحص بنية جدول whatsapp_messages:\n\n";
    
    // عرض أعمدة الجدول
    $columns = Schema::getColumnListing('whatsapp_messages');
    echo "📋 الأعمدة الموجودة:\n";
    foreach ($columns as $column) {
        echo "  - {$column}\n";
    }
    
    echo "\n";
    
    // عرض تفاصيل عمود message_type
    $connection = Schema::connection()->getDoctrineSchemaManager();
    $table = $connection->listTableDetails('whatsapp_messages');
    $messageTypeColumn = $table->getColumn('message_type');
    
    echo "🎯 تفاصيل عمود message_type:\n";
    echo "  - النوع: " . $messageTypeColumn->getType()->getName() . "\n";
    echo "  - الطول: " . ($messageTypeColumn->getLength() ?? 'غير محدد') . "\n";
    
    // عرض عدد الرسائل الحالية
    $count = DB::table('whatsapp_messages')->count();
    echo "\n📊 عدد الرسائل الحالية: {$count}\n";
    
    if ($count > 0) {
        $messages = DB::table('whatsapp_messages')->select('message_type')->limit(5)->get();
        echo "\n📝 عينة من أنواع الرسائل:\n";
        foreach ($messages as $message) {
            echo "  - {$message->message_type}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
