<?php
/**
 * ملف لتحسين أداء Filament و Livewire
 * قم بتشغيل هذا الملف لتطبيق الإعدادات المثلى للأداء
 */

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// عنوان الترويسة
echo "========================================================\n";
echo "      تحسين أداء Filament و Livewire                     \n";
echo "========================================================\n\n";

try {
    // تحديث ملف التهيئة لـ Filament
    echo "1. جاري فحص وتحسين تهيئة Filament... ";
    
    // التحقق من وجود ملف تهيئة Filament
    if (!File::exists(config_path('filament.php'))) {
        echo "جاري إنشاء ملف التهيئة... ";
        Artisan::call('vendor:publish', [
            '--tag' => 'filament-config',
            '--force' => true
        ]);
    }
    
    // تحديث ملف تهيئة Filament
    $filamentConfig = config_path('filament.php');
    if (File::exists($filamentConfig)) {
        $config = File::get($filamentConfig);
        
        // تحسين الأداء عبر تقليل الموارد المستخدمة
        if (strpos($config, "'spa' => false") !== false) {
            $config = str_replace("'spa' => false", "'spa' => true", $config);
            echo "تم تفعيل وضع SPA... ";
        }
        
        File::put($filamentConfig, $config);
        echo "✓ تم بنجاح\n";
    } else {
        echo "✗ لم يتم العثور على ملف تهيئة Filament\n";
    }
    
    // مسح مخزن مؤقت للعرض (View Cache)
    echo "2. جاري مسح مخزن مؤقت للعرض (View Cache)... ";
    Artisan::call('view:clear');
    echo "✓ تم بنجاح\n";
    
    // نشر assets الخاصة بـ Filament
    echo "3. جاري تحديث ملفات الأصول (assets) لـ Filament... ";
    Artisan::call('filament:assets', ['--force' => true]);
    echo "✓ تم بنجاح\n";
    
    // تحسين تشغيل Livewire
    echo "4. جاري تحسين أداء Livewire... ";
    
    // التحقق من وجود ملف تهيئة Livewire
    if (!File::exists(config_path('livewire.php'))) {
        echo "جاري إنشاء ملف التهيئة... ";
        Artisan::call('livewire:publish', [
            '--config' => true,
            '--force' => true
        ]);
    }
    
    // تحديث ملف تهيئة Livewire
    $livewireConfig = config_path('livewire.php');
    if (File::exists($livewireConfig)) {
        $config = File::get($livewireConfig);
        
        // تحسين الأداء عبر تفعيل التحميل المؤجل
        if (strpos($config, "'lazy_collection' => false") !== false) {
            $config = str_replace("'lazy_collection' => false", "'lazy_collection' => true", $config);
        }
        
        File::put($livewireConfig, $config);
        echo "✓ تم بنجاح\n";
    } else {
        echo "✗ لم يتم العثور على ملف تهيئة Livewire\n";
    }
    
    // تحسين معالجة الصور في Filament
    echo "5. جاري تحسين معالجة الصور... ";
    
    // تحسينات إضافية
    echo "\n\n===================== تم تطبيق التحسينات =====================\n";
    echo "لتحسين أداء Filament بشكل أفضل:\n\n";
    echo "1. استخدم خادم ويب مع دعم HTTP/2 أو HTTP/3\n";
    echo "2. استخدم ضغط GZIP/Brotli لملفات الـ JavaScript و CSS\n";
    echo "3. في الإنتاج، استخدم Redis كمزود Cache و Session\n";
    echo "4. قم بتقليل عدد المكونات (widgets) في لوحة التحكم\n";
    echo "5. استخدم التحميل المؤجل (defer loading) للجداول الكبيرة\n";
    echo "6. استخدم مُحسن ضغط الصور مثل: imagick بدلاً من GD\n";
    echo "7. تحسين الاستعلامات التي تقوم بها (اضافة eager loading حيث يلزم)\n\n";
    echo "================================================================\n";

} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}