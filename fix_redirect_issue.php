<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام الواجهات اللازمة
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "=======================================================\n";
echo "تشخيص وإصلاح مشكلة 403 بعد تسجيل الدخول\n";
echo "=======================================================\n\n";

// 1. عرض معلومات حول التوجيه بعد تسجيل الدخول
echo "1. فحص مسار التوجيه بعد تسجيل الدخول\n";
$routes = Route::getRoutes();
$loginRoute = null;
$redirectRoutes = [];
$dashboardRoutes = [];

foreach ($routes as $route) {
    if (stripos($route->uri(), 'login') !== false && in_array('POST', $route->methods())) {
        $loginRoute = $route;
    }
    
    if (stripos($route->uri(), 'dashboard') !== false || 
        stripos($route->uri(), 'admin') !== false || 
        stripos($route->uri(), 'home') !== false) {
        
        if (in_array('GET', $route->methods())) {
            $dashboardRoutes[] = [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->gatherMiddleware() ?: []
            ];
        }
    }
    
    if (stripos($route->getActionName(), 'RedirectController') !== false || 
        stripos($route->getActionName(), 'redirect') !== false) {
        $redirectRoutes[] = [
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName()
        ];
    }
}

echo " - مسار تسجيل الدخول: " . ($loginRoute ? $loginRoute->uri() : 'غير موجود') . "\n";
echo " - عدد مسارات لوحة التحكم المحتملة: " . count($dashboardRoutes) . "\n";

if (!empty($dashboardRoutes)) {
    echo " - مسارات لوحة التحكم المحتملة:\n";
    foreach ($dashboardRoutes as $route) {
        echo "   * " . $route['uri'] . " (الاسم: " . ($route['name'] ?: 'بدون اسم') . ")\n";
        if (!empty($route['middleware'])) {
            echo "     الوسائط: " . implode(', ', (array) $route['middleware']) . "\n";
        }
    }
}

// 2. فحص الوسائط المستخدمة
echo "\n2. فحص الوسائط (Middleware) المسجلة\n";
try {
    // الحصول على قائمة الوسائط المسجلة (Middleware)
    $middlewareGroups = app('Illuminate\Contracts\Http\Kernel')->getMiddlewareGroups();
    echo " - مجموعات الوسائط المسجلة:\n";
    foreach ($middlewareGroups as $groupName => $middlewares) {
        echo "   * " . $groupName . ": ";
        echo implode(', ', (array) $middlewares) . "\n";
    }
} catch (Exception $e) {
    echo " - حدث خطأ أثناء محاولة استرداد الوسائط: " . $e->getMessage() . "\n";
}

// 3. فحص وإصلاح مشاكل التخزين المؤقت
echo "\n3. تنظيف وإعادة بناء التخزين المؤقت\n";
try {
    echo " - تنظيف ذاكرة التخزين المؤقت... ";
    Artisan::call('cache:clear');
    echo "تم\n";
    
    echo " - تنظيف ذاكرة التكوين المؤقت... ";
    Artisan::call('config:clear');
    echo "تم\n";
    
    echo " - تنظيف ذاكرة المسارات المؤقتة... ";
    Artisan::call('route:clear');
    echo "تم\n";
    
    echo " - تنظيف ذاكرة العروض المؤقتة... ";
    Artisan::call('view:clear');
    echo "تم\n";

    echo " - إعادة بناء ذاكرة التكوين المؤقت... ";
    Artisan::call('config:cache');
    echo "تم\n";
    
    echo " - إعادة بناء ذاكرة المسارات المؤقتة... ";
    Artisan::call('route:cache');
    echo "تم\n";
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}

// 4. التحقق من إعدادات ملف .env
echo "\n4. التحقق من إعدادات ملف .env\n";
try {
    $envFile = file_get_contents(base_path('.env'));
    preg_match('/APP_URL=([^\n]+)/', $envFile, $appUrlMatches);
    preg_match('/APP_ENV=([^\n]+)/', $envFile, $appEnvMatches);
    preg_match('/APP_DEBUG=([^\n]+)/', $envFile, $appDebugMatches);
    
    $appUrl = isset($appUrlMatches[1]) ? trim($appUrlMatches[1]) : 'غير محدد';
    $appEnv = isset($appEnvMatches[1]) ? trim($appEnvMatches[1]) : 'غير محدد';
    $appDebug = isset($appDebugMatches[1]) ? trim($appDebugMatches[1]) : 'غير محدد';
    
    echo " - عنوان التطبيق (APP_URL): " . $appUrl . "\n";
    echo " - بيئة التطبيق (APP_ENV): " . $appEnv . "\n";
    echo " - وضع التصحيح (APP_DEBUG): " . $appDebug . "\n";
    
    // التحقق من صحة URL التطبيق
    $parsedUrl = parse_url($appUrl);
    if (empty($parsedUrl['host'])) {
        echo " ⚠️ تحذير: قد يكون APP_URL غير صالح. يجب أن يكون مثل https://garbpro.pro\n";
        
        // تعديل ملف .env لإصلاح APP_URL إذا لزم الأمر
        $correctAppUrl = "https://garbpro.pro";
        $newEnvContent = preg_replace('/APP_URL=([^\n]+)/', "APP_URL=$correctAppUrl", $envFile);
        
        if ($newEnvContent != $envFile) {
            file_put_contents(base_path('.env'), $newEnvContent);
            echo " ✓ تم تصحيح APP_URL إلى $correctAppUrl\n";
            echo " - جاري إعادة تخزين التكوين... ";
            Artisan::call('config:cache');
            echo "تم\n";
        }
    }

} catch (Exception $e) {
    echo "حدث خطأ أثناء التحقق من ملف .env: " . $e->getMessage() . "\n";
}

// 5. التحقق من صلاحيات مجلد public
echo "\n5. التحقق من صلاحيات مجلد public\n";
try {
    $publicPath = public_path();
    echo " - مسار مجلد public: " . $publicPath . "\n";
    
    if (is_dir($publicPath)) {
        echo " - المجلد موجود: نعم\n";
        echo " - هل المجلد قابل للقراءة: " . (is_readable($publicPath) ? 'نعم' : 'لا') . "\n";
        
        // التحقق من الصلاحيات على الخادم
        if (function_exists('posix_getpwuid')) {
            $owner = posix_getpwuid(fileowner($publicPath));
            $group = posix_getgrgid(filegroup($publicPath));
            $perms = substr(sprintf('%o', fileperms($publicPath)), -4);
            
            echo " - المالك: " . $owner['name'] . "\n";
            echo " - المجموعة: " . $group['name'] . "\n";
            echo " - الصلاحيات: " . $perms . "\n";
        }
    } else {
        echo " ⚠️ خطأ: مجلد public غير موجود!\n";
    }
} catch (Exception $e) {
    echo "حدث خطأ أثناء التحقق من صلاحيات مجلد public: " . $e->getMessage() . "\n";
}

// 6. التحقق من وجود ملفات لوحة التحكم الرئيسية
echo "\n6. التحقق من وجود ملفات لوحة التحكم الرئيسية\n";
$filamentPath = app_path('Filament');
$filamentExists = is_dir($filamentPath);

echo " - هل مجلد Filament موجود: " . ($filamentExists ? 'نعم' : 'لا') . "\n";

if ($filamentExists) {
    $adminResourcePath = $filamentPath . '/Resources';
    $hasAdminResources = is_dir($adminResourcePath);
    echo " - هل مجلد Resources في Filament موجود: " . ($hasAdminResources ? 'نعم' : 'لا') . "\n";
    
    // البحث عن ملفات موارد Filament
    if ($hasAdminResources) {
        $resourceFiles = File::glob($adminResourcePath . '/*/*.php');
        echo " - عدد ملفات موارد Filament: " . count($resourceFiles) . "\n";
        
        if (count($resourceFiles) > 0) {
            echo " - أمثلة على موارد Filament:\n";
            $showCount = min(count($resourceFiles), 3);
            for ($i = 0; $i < $showCount; $i++) {
                echo "   * " . basename($resourceFiles[$i]) . "\n";
            }
        }
    }
}

// 7. التحقق من ملف الدخول والصفحات الرئيسية
echo "\n7. التحقق من ملفات الإعدادات الأساسية\n";

// التحقق من التكوين الرئيسي
$authConfig = config('auth');
$filamentConfig = config('filament');

echo " - مزود المصادقة: " . ($authConfig['providers']['users']['driver'] ?? 'غير محدد') . "\n";
echo " - نموذج المستخدم: " . ($authConfig['providers']['users']['model'] ?? 'غير محدد') . "\n";

if (isset($filamentConfig)) {
    echo " - مسار لوحة تحكم Filament: " . ($filamentConfig['path'] ?? 'غير محدد') . "\n";
    
    if (isset($filamentConfig['auth'])) {
        echo " - صفحة تسجيل دخول Filament: " . ($filamentConfig['auth']['pages']['login'] ?? 'غير محدد') . "\n";
    }
}

// 8. إنشاء مسار آمن لاختبار الوصول
echo "\n8. إنشاء مسار آمن لاختبار الوصول\n";

try {
    // إنشاء ملف اختبار بسيط في المجلد العام
    $testRoute = "test-access-" . rand(1000, 9999);
    $testFilePath = public_path($testRoute . '.php');
    
    $testContent = '<?php
echo "تم الوصول بنجاح إلى صفحة الاختبار!";
echo "<br>";
echo "المستخدم الحالي: " . (auth()->check() ? auth()->user()->name : "غير مسجل الدخول");
?>';
    
    file_put_contents($testFilePath, $testContent);
    echo " ✓ تم إنشاء صفحة اختبار بنجاح. يمكنك الوصول إليها عبر:\n";
    echo "   https://garbpro.pro/" . $testRoute . ".php\n";
    echo "   (استخدم هذه الصفحة للتحقق ما إذا كانت لديك قدرة الوصول إلى التطبيق أم أن المشكلة في مسارات معينة)\n";
} catch (Exception $e) {
    echo "حدث خطأ أثناء إنشاء صفحة اختبار: " . $e->getMessage() . "\n";
}

// 9. تقديم توصيات لحل المشكلة
echo "\n9. توصيات لحل مشكلة 403 بعد تسجيل الدخول\n";

echo " 1. قم بزيارة صفحة الاختبار (الرابط أعلاه) للتحقق من إمكانية الوصول إلى التطبيق.\n";
echo " 2. تأكد من تنفيذ التعديلات المقترحة على ملف Nginx (fixed_nginx.conf).\n";
echo " 3. تأكد من صلاحيات المجلدات باستخدام سكريبت fix_server_permissions.sh.\n";
echo " 4. إذا كانت المشكلة لا تزال موجودة، جرب تعطيل وضع التصحيح (APP_DEBUG=false) وإعادة التشغيل.\n";
echo " 5. تحقق من الوسائط (middleware) المطبقة على مسارات لوحة التحكم.\n";

echo "\n=======================================================\n";
echo "عرض مؤقت: للتغلب على مشكلة 403، جرب الوصول إلى لوحة التحكم مباشرة عبر:\n";

if (!empty($dashboardRoutes)) {
    foreach ($dashboardRoutes as $idx => $route) {
        if ($idx <= 2) { // عرض أول 3 مسارات فقط
            echo "https://garbpro.pro/" . $route['uri'] . "\n";
        }
    }
} else {
    echo "لم يتم العثور على مسارات لوحة التحكم المحتملة.\n";
    echo "جرب: https://garbpro.pro/admin\n";
}

echo "=======================================================\n";