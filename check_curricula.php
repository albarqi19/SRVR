<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== فحص المناهج المتاحة ===\n";

try {
    // فحص جدول curricula
    echo "\n1. فحص جدول curricula:\n";
    $curricula = DB::table('curricula')->get();
    
    if ($curricula->isEmpty()) {
        echo "❌ لا توجد مناهج في جدول curricula\n";
    } else {
        echo "✅ المناهج المتاحة:\n";
        foreach ($curricula as $curriculum) {
            echo "  - ID: {$curriculum->id}, Name: {$curriculum->name}\n";
        }
    }

    // فحص بنية جدول curricula
    echo "\n2. بنية جدول curricula:\n";
    $columns = DB::select("DESCRIBE curricula");
    foreach ($columns as $column) {
        echo "  - {$column->Field}: {$column->Type}\n";
    }

    // فحص جدول curriculum_levels إذا كان موجود
    echo "\n3. فحص جدول curriculum_levels:\n";
    try {
        $levels = DB::table('curriculum_levels')->get();
        if ($levels->isEmpty()) {
            echo "❌ لا توجد مستويات في جدول curriculum_levels\n";
        } else {
            echo "✅ المستويات المتاحة:\n";
            foreach ($levels as $level) {
                echo "  - ID: {$level->id}, Name: {$level->name}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ جدول curriculum_levels غير موجود: " . $e->getMessage() . "\n";
    }

    // فحص العلاقات في recitation_sessions
    echo "\n4. فحص بنية جدول recitation_sessions:\n";
    try {
        $sessionColumns = DB::select("DESCRIBE recitation_sessions");
        foreach ($sessionColumns as $column) {
            echo "  - {$column->Field}: {$column->Type}\n";
        }
    } catch (Exception $e) {
        echo "❌ مشكلة في فحص جدول recitation_sessions: " . $e->getMessage() . "\n";
    }

    // إنشاء منهج تجريبي إذا لم يكن موجود
    echo "\n5. إنشاء منهج تجريبي إذا لزم الأمر:\n";
    if ($curricula->isEmpty()) {
        try {
            $curriculumId = DB::table('curricula')->insertGetId([
                'name' => 'منهج تجريبي',
                'description' => 'منهج تجريبي لاختبار API',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "✅ تم إنشاء منهج تجريبي بـ ID: {$curriculumId}\n";
        } catch (Exception $e) {
            echo "❌ فشل في إنشاء منهج تجريبي: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ خطأ في فحص قاعدة البيانات: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الفحص ===\n";
