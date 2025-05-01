<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام الواجهات اللازمة
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

echo "=======================================================\n";
echo "تشخيص مشكلة 403 Forbidden في تطبيق Laravel\n";
echo "=======================================================\n\n";

// التحقق من اتصال قاعدة البيانات
echo "1. التحقق من اتصال قاعدة البيانات:\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ اتصال قاعدة البيانات يعمل بشكل صحيح.\n";
    echo "   - اسم قاعدة البيانات: " . DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "   ✗ فشل الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
}

// التحقق من وجود جدول المستخدمين
echo "\n2. التحقق من جدول المستخدمين:\n";
if (Schema::hasTable('users')) {
    echo "   ✓ جدول المستخدمين موجود.\n";
    
    // عرض عدد المستخدمين
    $userCount = DB::table('users')->count();
    echo "   - عدد المستخدمين في النظام: $userCount\n";
    
    // التحقق من وجود المستخدم admin@quran-center.com
    $adminUser = DB::table('users')->where('email', 'admin@quran-center.com')->first();
    if ($adminUser) {
        echo "   ✓ تم العثور على المستخدم admin@quran-center.com\n";
        echo "   - اسم المستخدم: " . $adminUser->name . "\n";
        echo "   - الحالة: " . ($adminUser->is_active ? 'نشط' : 'غير نشط') . "\n";
    } else {
        echo "   ✗ لم يتم العثور على المستخدم admin@quran-center.com\n";
    }
} else {
    echo "   ✗ جدول المستخدمين غير موجود!\n";
}

// التحقق من نظام الصلاحيات
echo "\n3. التحقق من نظام الصلاحيات:\n";
if (Schema::hasTable('roles')) {
    echo "   ✓ جدول الأدوار موجود.\n";
    
    // عرض عدد الأدوار
    $roleCount = DB::table('roles')->count();
    echo "   - عدد الأدوار في النظام: $roleCount\n";
    
    // عرض قائمة الأدوار
    echo "   - قائمة الأدوار المتوفرة:\n";
    $roles = DB::table('roles')->get();
    foreach ($roles as $role) {
        echo "     * " . $role->name . "\n";
    }
    
    // التحقق من وجود جدول علاقة المستخدمين بالأدوار
    if (Schema::hasTable('model_has_roles')) {
        echo "   ✓ جدول علاقة المستخدمين بالأدوار موجود.\n";
        
        // إذا وُجد المستخدم الإداري، تحقق من أدواره
        if (isset($adminUser) && $adminUser) {
            $userRoles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $adminUser->id)
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->pluck('roles.name')
                ->toArray();
            
            if (count($userRoles) > 0) {
                echo "   ✓ المستخدم الإداري لديه أدوار مُعيّنة: " . implode(', ', $userRoles) . "\n";
            } else {
                echo "   ✗ المستخدم الإداري ليس لديه أي أدوار مُعيّنة!\n";
            }
        }
    } else {
        echo "   ✗ جدول علاقة المستخدمين بالأدوار غير موجود!\n";
    }
} else {
    echo "   ✗ جدول الأدوار غير موجود!\n";
}

// التحقق من ملفات التخزين المؤقت والمسارات
echo "\n4. التحقق من ملفات التخزين المؤقت والمسارات:\n";
$storagePath = storage_path();
$cachePath = $storagePath . '/framework/cache';
$sessionPath = $storagePath . '/framework/sessions';
$viewsPath = $storagePath . '/framework/views';

echo "   - مسار التخزين: $storagePath\n";
echo "   - هل مجلد التخزين قابل للكتابة: " . (is_writable($storagePath) ? 'نعم' : 'لا') . "\n";
echo "   - هل مجلد التخزين المؤقت قابل للكتابة: " . (is_writable($cachePath) ? 'نعم' : 'لا') . "\n";
echo "   - هل مجلد الجلسات قابل للكتابة: " . (is_writable($sessionPath) ? 'نعم' : 'لا') . "\n";
echo "   - هل مجلد العروض قابل للكتابة: " . (is_writable($viewsPath) ? 'نعم' : 'لا') . "\n";

// التحقق من وجود الملفات المهمة
echo "\n5. التحقق من وجود الملفات المهمة:\n";
$publicIndex = public_path('index.php');
$envFile = base_path('.env');

echo "   - هل ملف index.php موجود: " . (file_exists($publicIndex) ? 'نعم' : 'لا') . "\n";
echo "   - هل ملف .env موجود: " . (file_exists($envFile) ? 'نعم' : 'لا') . "\n";

// التحقق من المسارات المسجلة
echo "\n6. التحقق من المسارات المسجلة:\n";
$routes = Route::getRoutes();
$adminRoutes = [];

foreach ($routes as $route) {
    if (strpos($route->uri, 'admin') !== false) {
        $adminRoutes[] = $route->uri;
    }
}

echo "   - عدد المسارات الإجمالي: " . count($routes) . "\n";
echo "   - عدد مسارات لوحة الإدارة: " . count($adminRoutes) . "\n";
echo "   - أمثلة على مسارات لوحة الإدارة:\n";
$showCount = min(count($adminRoutes), 5);
for ($i = 0; $i < $showCount; $i++) {
    echo "     * " . $adminRoutes[$i] . "\n";
}

echo "\n=======================================================\n";
echo "انتهى التشخيص. يمكنك استخدام هذه المعلومات للمساعدة في حل مشكلة 403.\n";
echo "=======================================================\n";