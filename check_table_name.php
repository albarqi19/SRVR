<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Database Tables ===\n";

// البحث عن جداول المناهج
try {
    $tables = DB::select('SHOW TABLES');
    $curriculumTables = [];
    
    foreach ($tables as $table) {
        $tableName = array_values((array) $table)[0];
        if (strpos($tableName, 'curriculum') !== false) {
            $curriculumTables[] = $tableName;
        }
    }
    
    echo "Found curriculum-related tables:\n";
    foreach ($curriculumTables as $table) {
        echo "- $table\n";
    }
    
    // التحقق من جدول curriculums أو curricula
    if (in_array('curriculums', $curriculumTables)) {
        echo "\n✅ Table 'curriculums' exists\n";
        $curricula = DB::table('curriculums')->get();
        echo "Records in curriculums table: " . count($curricula) . "\n";
        foreach ($curricula as $curriculum) {
            echo "  ID: {$curriculum->id} - Name: {$curriculum->name}\n";
        }
    } elseif (in_array('curricula', $curriculumTables)) {
        echo "\n✅ Table 'curricula' exists\n";
        $curricula = DB::table('curricula')->get();
        echo "Records in curricula table: " . count($curricula) . "\n";
        foreach ($curricula as $curriculum) {
            echo "  ID: {$curriculum->id} - Name: {$curriculum->name}\n";
        }
    } else {
        echo "\n❌ No curriculums or curricula table found!\n";
        echo "Available curriculum-related tables: " . implode(', ', $curriculumTables) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
