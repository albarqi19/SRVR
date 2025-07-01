<?php

require_once 'vendor/autoload.php';

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "فحص الجداول المتعلقة بالحضور والتسميع:\n";
    echo "=" . str_repeat("=", 50) . "\n";
    
    $tables = DB::select('SHOW TABLES');
    
    echo "جميع الجداول:\n";
    foreach($tables as $table) {
        $tableName = array_values((array)$table)[0];
        if(strpos($tableName, 'attendance') !== false || 
           strpos($tableName, 'recitation') !== false || 
           strpos($tableName, 'session') !== false ||
           strpos($tableName, 'student') !== false) {
            echo "- " . $tableName . "\n";
            
            // فحص هيكل الجدول
            $columns = DB::select("DESCRIBE `{$tableName}`");
            echo "  الأعمدة: ";
            foreach($columns as $column) {
                echo $column->Field . ", ";
            }
            echo "\n";
            
            // عد السجلات
            $count = DB::table($tableName)->count();
            echo "  عدد السجلات: " . $count . "\n\n";
        }
    }
    
    echo "\nفحص بيانات التسميع إذا كانت موجودة:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    // تجربة البحث في جداول مختلفة
    $possibleTables = ['recitation_sessions', 'student_recitations', 'recitations', 'quran_recitations'];
    
    foreach($possibleTables as $tableName) {
        try {
            $count = DB::table($tableName)->count();
            echo "✅ جدول {$tableName}: {$count} سجل\n";
            
            if($count > 0) {
                $sample = DB::table($tableName)->limit(1)->first();
                echo "   عينة: " . json_encode($sample, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch(Exception $e) {
            echo "❌ جدول {$tableName}: غير موجود\n";
        }
    }
    
    echo "\nفحص بيانات الحضور:\n";
    echo "-" . str_repeat("-", 30) . "\n";
    
    $possibleAttendanceTables = ['student_attendances', 'attendances', 'daily_attendances'];
    
    foreach($possibleAttendanceTables as $tableName) {
        try {
            $count = DB::table($tableName)->count();
            echo "✅ جدول {$tableName}: {$count} سجل\n";
            
            if($count > 0) {
                $sample = DB::table($tableName)->limit(1)->first();
                echo "   عينة: " . json_encode($sample, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } catch(Exception $e) {
            echo "❌ جدول {$tableName}: غير موجود\n";
        }
    }
    
} catch(Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
