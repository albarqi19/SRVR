<?php
echo "بدء الاختبار...\n";

try {
    require_once 'vendor/autoload.php';
    echo "تم تحميل autoload\n";
    
    $app = require_once 'bootstrap/app.php';
    echo "تم تحميل التطبيق\n";
    
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "تم تحميل kernel\n";
    
    echo "الاختبار تم بنجاح!\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
?>
