<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking student_progress table structure ===\n";

try {
    // Check if table exists
    $tableExists = DB::select("SHOW TABLES LIKE 'student_progress'");
    
    if (empty($tableExists)) {
        echo "❌ Table 'student_progress' does not exist!\n";
        echo "Available tables:\n";
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            if (strpos($tableName, 'student') !== false || strpos($tableName, 'progress') !== false) {
                echo "  - $tableName\n";
            }
        }
    } else {
        echo "✅ Table 'student_progress' exists\n\n";
        
        // Get table structure
        $columns = DB::select('DESCRIBE student_progress');
        echo "Columns in student_progress table:\n";
        $hasIsActive = false;
        
        foreach ($columns as $col) {
            echo "  - {$col->Field} ({$col->Type})";
            if ($col->Null == 'YES') echo " NULL";
            if ($col->Default !== null) echo " DEFAULT '{$col->Default}'";
            if ($col->Key == 'PRI') echo " PRIMARY KEY";
            echo "\n";
            
            if ($col->Field === 'is_active') {
                $hasIsActive = true;
            }
        }
        
        echo "\n";
        if ($hasIsActive) {
            echo "✅ Column 'is_active' EXISTS in student_progress table\n";
        } else {
            echo "❌ Column 'is_active' is MISSING from student_progress table\n";
            echo "🔧 The suggested solution is CORRECT - need to add is_active column\n";
        }
        
        // Check record count
        $count = DB::table('student_progress')->count();
        echo "\nRecords in student_progress: $count\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
