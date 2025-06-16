<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "التحقق من بنية enum...\n";
    
    $columns = DB::select("SHOW COLUMNS FROM whatsapp_messages WHERE Field = 'message_type'");
    
    if (!empty($columns)) {
        echo "Type: " . $columns[0]->Type . "\n";
        echo "تم التحديث بنجاح!\n";
    } else {
        echo "لم يتم العثور على العمود\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
