<?php

require_once 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "جاري حذف جميع رسائل WhatsApp...\n";
    
    // حذف جميع البيانات الموجودة
    DB::table('whatsapp_messages')->truncate();
    echo "تم حذف جميع الرسائل بنجاح.\n";
    
    echo "جاري تحديث enum...\n";
    
    // تحديث enum مباشرة
    DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN message_type ENUM('notification', 'command', 'response', 'reminder', 'attendance', 'custom', 'session') NOT NULL");
    
    echo "تم تحديث enum بنجاح!\n";
    
    // التحقق من البنية الجديدة
    $columns = DB::select("SHOW COLUMNS FROM whatsapp_messages WHERE Field = 'message_type'");
    
    if (!empty($columns)) {
        echo "بنية العمود الحالية:\n";
        echo "Type: " . $columns[0]->Type . "\n";
    }
    
    echo "تم الانتهاء بنجاح!\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
