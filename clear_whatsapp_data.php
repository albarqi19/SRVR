<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // حذف جميع الرسائل
    DB::table('whatsapp_messages')->truncate();
    echo "✅ تم حذف جميع رسائل WhatsApp بنجاح\n";
    
    // عرض عدد الرسائل المتبقية
    $count = DB::table('whatsapp_messages')->count();
    echo "📊 عدد الرسائل المتبقية: {$count}\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
