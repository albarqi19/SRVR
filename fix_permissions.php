<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// استخدام Eloquent
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

try {
    echo "بدء عملية إصلاح الصلاحيات للنظام...\n\n";
    
    // التحقق من جدول المستخدمين
    if (!Schema::hasTable('users')) {
        echo "خطأ: جدول المستخدمين غير موجود! سيتم تشغيل عمليات الترحيل (migrations)...\n";
        // تشغيل عمليات الترحيل
        Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    }
    
    // الاطلاع على جميع الجداول في قاعدة البيانات
    echo "جداول قاعدة البيانات الموجودة:\n";
    $tables = DB::select('SHOW TABLES');
    $dbName = config('database.connections.'.config('database.default').'.database');
    $tableColumn = 'Tables_in_' . $dbName;
    
    foreach ($tables as $table) {
        echo "- " . $table->$tableColumn . "\n";
    }
    
    // التحقق من وجود جدول الأدوار والصلاحيات
    $hasRolesTable = Schema::hasTable('roles');
    $hasPermissionsTable = Schema::hasTable('permissions');
    $hasModelHasRolesTable = Schema::hasTable('model_has_roles');
    $hasModelHasPermissionsTable = Schema::hasTable('model_has_permissions');
    
    echo "\nالتحقق من جداول إدارة الصلاحيات:\n";
    echo "- جدول الأدوار (roles): " . ($hasRolesTable ? "موجود" : "غير موجود") . "\n";
    echo "- جدول الصلاحيات (permissions): " . ($hasPermissionsTable ? "موجود" : "غير موجود") . "\n";
    echo "- جدول علاقة النماذج بالأدوار (model_has_roles): " . ($hasModelHasRolesTable ? "موجود" : "غير موجود") . "\n";
    echo "- جدول علاقة النماذج بالصلاحيات (model_has_permissions): " . ($hasModelHasPermissionsTable ? "موجود" : "غير موجود") . "\n";
    
    if (!$hasRolesTable || !$hasPermissionsTable) {
        echo "\nجداول إدارة الصلاحيات غير مكتملة. جاري تثبيت حزمة Spatie/Laravel-Permission...\n";
        // نفترض أن الحزمة مثبتة بالفعل، ولكن نحتاج لتطبيق ترحيلاتها
        Artisan::call('vendor:publish', ['--provider' => 'Spatie\\Permission\\PermissionServiceProvider']);
        echo Artisan::output();
        
        // تنفيذ عمليات الترحيل التي تنشئ جداول الأدوار والصلاحيات
        Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    }
    
    // التحقق مرة أخرى من وجود جدول الأدوار
    if (Schema::hasTable('roles')) {
        echo "\nجدول الأدوار موجود. جاري إنشاء الأدوار الأساسية...\n";
        
        // الأدوار التي نريد التأكد من وجودها
        $requiredRoles = [
            'super-admin' => 'مدير النظام الأعلى',
            'admin' => 'مدير النظام',
            'supervisor' => 'مشرف',
            'teacher' => 'معلم',
            'staff' => 'موظف',
            'student' => 'طالب'
        ];
        
        // إنشاء الأدوار إذا لم تكن موجودة
        foreach ($requiredRoles as $roleName => $roleDescription) {
            if (!DB::table('roles')->where('name', $roleName)->exists()) {
                DB::table('roles')->insert([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                echo "- تم إنشاء دور $roleName ($roleDescription)\n";
            } else {
                echo "- دور $roleName موجود بالفعل\n";
            }
        }
        
        // جلب معرف المستخدم
        $user = DB::table('users')
            ->where('email', 'admin@system.com')
            ->orWhere('username', 'sysadmin')
            ->first();
            
        if (!$user) {
            echo "\nلم يتم العثور على المستخدم الإداري! جاري إنشاء مستخدم جديد...\n";
            
            // إنشاء مستخدم جديد
            $userId = DB::table('users')->insertGetId([
                'name' => 'مدير النظام',
                'email' => 'admin@system.com',
                'username' => 'sysadmin',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => 1
            ]);
            echo "تم إنشاء مستخدم جديد بمعرف: $userId\n";
        } else {
            $userId = $user->id;
            echo "\nتم العثور على المستخدم الإداري بمعرف: $userId\n";
            
            // تحديث المستخدم للتأكد من أنه نشط
            DB::table('users')->where('id', $userId)->update([
                'password' => Hash::make('Admin123!'),
                'is_active' => 1
            ]);
            echo "تم تحديث كلمة المرور وتنشيط الحساب\n";
        }
        
        // التأكد من تعيين جميع الأدوار للمستخدم
        echo "\nجاري تعيين جميع الأدوار للمستخدم الإداري...\n";
        
        // مسح أي أدوار موجودة قبل إضافة الجديدة
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_id', $userId)
                ->where('model_type', 'App\\Models\\User')
                ->delete();
            
            // إضافة جميع الأدوار الأساسية للمستخدم
            foreach (DB::table('roles')->get() as $role) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $role->id,
                    'model_id' => $userId,
                    'model_type' => 'App\\Models\\User'
                ]);
                echo "- تم تعيين دور {$role->name} للمستخدم\n";
            }
        } else {
            echo "خطأ: جدول model_has_roles غير موجود!\n";
        }
        
        // إعادة تخزين جميع الأذونات
        echo "\nإعادة تخزين الأذونات في النظام...\n";
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        echo "تم مسح ذاكرة التخزين المؤقت\n";
        
        echo "\nتم الانتهاء من إصلاح الصلاحيات بنجاح!\n";
        echo "\nيمكنك الآن تسجيل الدخول باستخدام:\n";
        echo "البريد الإلكتروني: admin@system.com\n";
        echo "اسم المستخدم: sysadmin\n";
        echo "كلمة المرور: Admin123!\n";
    } else {
        echo "خطأ: لم يتم إنشاء جدول الأدوار بنجاح!\n";
    }
    
} catch (Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
    echo "الخطأ في السطر: " . $e->getLine() . " في الملف " . $e->getFile() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}