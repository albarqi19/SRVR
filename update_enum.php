<?php

use Illuminate\Support\Facades\DB;

try {
    echo "جاري تحديث enum...\n";
    
    DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN message_type ENUM('notification', 'command', 'response', 'reminder', 'attendance', 'custom', 'session') NOT NULL");
    
    echo "تم تحديث enum بنجاح!\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
