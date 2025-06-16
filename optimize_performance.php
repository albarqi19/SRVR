<?php
/**
 * ملف لتحسين أداء Laravel
 * قم بتشغيل هذا الملف لتطبيق الإعدادات المثلى للأداء
 */

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

// عنوان الترويسة
echo "========================================================\n";
echo "      تحسين أداء تطبيق Laravel                          \n";
echo "========================================================\n\n";

try {
    // مسح الذاكرة المخبأة
    echo "1. جاري مسح الذاكرة المخبأة للتطبيق... ";
    Artisan::call('cache:clear');
    echo "✓ تم بنجاح\n";

    // مسح التهيئة المخزنة
    echo "2. جاري مسح تهيئة التطبيق المخزنة... ";
    Artisan::call('config:clear');
    echo "✓ تم بنجاح\n";

    // مسح مخبأة الروابط
    echo "3. جاري مسح مخبأة الروابط... ";
    Artisan::call('route:clear');
    echo "✓ تم بنجاح\n";
    
    // مسح مخبأة العرض
    echo "4. جاري مسح مخبأة العرض... ";
    Artisan::call('view:clear');
    echo "✓ تم بنجاح\n";
    
    // إعادة تحميل تهيئة التطبيق
    echo "5. جاري إنشاء ملف التهيئة... ";
    Artisan::call('config:cache');
    echo "✓ تم بنجاح\n";
    
    // إنشاء مخبأة الروابط
    echo "6. جاري إنشاء مخبأة الروابط... ";
    Artisan::call('route:cache');
    echo "✓ تم بنجاح\n";
    
    // تحسين التسجيل التلقائي للفئات
    echo "7. جاري تحسين التسجيل التلقائي للفئات... ";
    Artisan::call('optimize');
    echo "✓ تم بنجاح\n";

    // إنشاء ملف .env.production
    echo "\n8. جاري إنشاء نسخة من ملف .env للإنتاج... ";
    $envFile = file_get_contents(__DIR__ . '/.env');
    $prodEnv = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $envFile);
    $prodEnv = str_replace('APP_ENV=local', 'APP_ENV=production', $prodEnv);
    
    if (file_put_contents(__DIR__ . '/.env.production', $prodEnv)) {
        echo "✓ تم بنجاح\n";
        echo "   تم إنشاء ملف .env.production مع ضبط وضع الإنتاج\n";
    } else {
        echo "✗ فشل في إنشاء الملف\n";
    }

    echo "\n===================== نصائح إضافية =====================\n";
    echo "للحصول على أداء أفضل في بيئة الإنتاج:\n";
    echo "1. استخدم ملف .env.production بدلاً من .env\n";
    echo "2. تأكد من ضبط APP_DEBUG=false لتعطيل وضع التصحيح\n";
    echo "3. تأكد من تفعيل OPcache في إعدادات PHP\n";
    echo "4. ضبط CACHE_DRIVER=file أو redis للأداء الأفضل\n";
    echo "5. ضبط SESSION_DRIVER=file أو redis للأداء الأفضل\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}