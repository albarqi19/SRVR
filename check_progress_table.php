<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    if (Schema::hasTable('student_curriculum_progress')) {
        echo "✓ جدول student_curriculum_progress موجود في قاعدة البيانات\n\n";
        
        // الحصول على هيكل الجدول
        $columns = DB::select('SHOW COLUMNS FROM student_curriculum_progress');
        echo "هيكل الجدول:\n";
        echo str_repeat('-', 80) . "\n";
        echo "| " . str_pad('الاسم', 25) . " | " . str_pad('النوع', 25) . " | " . str_pad('Null', 6) . " | " . str_pad('المفتاح', 10) . " | " . str_pad('افتراضي', 10) . " |\n";
        echo str_repeat('-', 80) . "\n";
        
        foreach ($columns as $column) {
            echo "| " . str_pad($column->Field, 25) . " | " . 
                 str_pad($column->Type, 25) . " | " . 
                 str_pad($column->Null, 6) . " | " . 
                 str_pad($column->Key, 10) . " | " . 
                 str_pad(($column->Default !== null) ? $column->Default : 'NULL', 10) . " |\n";
        }
        echo str_repeat('-', 80) . "\n\n";
        
        // التحقق من المفاتيح الأجنبية
        $foreignKeys = DB::select("
            SELECT 
                COLUMN_NAME AS 'column',
                REFERENCED_TABLE_NAME AS 'referenced_table',
                REFERENCED_COLUMN_NAME AS 'referenced_column'
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = (SELECT DATABASE())
            AND TABLE_NAME = 'student_curriculum_progress'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (count($foreignKeys) > 0) {
            echo "المفاتيح الأجنبية:\n";
            echo str_repeat('-', 100) . "\n";
            echo "| " . str_pad('العمود', 25) . " | " . str_pad('الجدول المرجعي', 25) . " | " . str_pad('العمود المرجعي', 25) . " |\n";
            echo str_repeat('-', 100) . "\n";
            
            foreach ($foreignKeys as $fk) {
                echo "| " . str_pad($fk->column, 25) . " | " . 
                     str_pad($fk->referenced_table, 25) . " | " . 
                     str_pad($fk->referenced_column, 25) . " |\n";
            }
            echo str_repeat('-', 100) . "\n";
        } else {
            echo "❌ لا توجد مفاتيح أجنبية معرفة لهذا الجدول!\n";
        }
        
    } else {
        echo "❌ جدول student_curriculum_progress غير موجود في قاعدة البيانات!\n";
    }
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
