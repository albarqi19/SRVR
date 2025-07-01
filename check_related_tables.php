<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 فحص الجداول ذات الصلة\n";
echo str_repeat("=", 40) . "\n\n";

$tables = ['attendances', 'recitation_sessions', 'whatsapp_messages'];

foreach ($tables as $table) {
    echo "📋 جدول {$table}:\n";
    try {
        if (Schema::hasTable($table)) {
            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                $hasUserId = $column === 'user_id' ? '✅' : '  ';
                $hasTeacherId = $column === 'teacher_id' ? '🎯' : '  ';
                echo "   {$hasUserId}{$hasTeacherId} {$column}\n";
            }
        } else {
            echo "   ❌ الجدول غير موجود\n";
        }
    } catch (Exception $e) {
        echo "   ❌ خطأ: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "📝 الملاحظات:\n";
echo "   ✅ = يحتوي على user_id\n";
echo "   🎯 = يحتوي على teacher_id\n";
