<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking student_curricula table structure:\n";
    echo "==========================================\n";
    
    if (Schema::hasTable('student_curricula')) {
        $columns = DB::select('DESCRIBE student_curricula');
        foreach($columns as $column) {
            echo "Column: {$column->Field} - Type: {$column->Type}\n";
        }
        
        echo "\n\nChecking for completion_percentage column:\n";
        if (Schema::hasColumn('student_curricula', 'completion_percentage')) {
            echo "✓ completion_percentage column exists\n";
        } else {
            echo "✗ completion_percentage column does NOT exist\n";
        }
        
        // Check some sample data
        echo "\n\nSample data from student_curricula:\n";
        $sampleData = DB::table('student_curricula')->limit(3)->get();
        foreach($sampleData as $record) {
            echo "ID: {$record->id}\n";
            if (isset($record->completion_percentage)) {
                echo "  - completion_percentage: {$record->completion_percentage}\n";
            } else {
                echo "  - completion_percentage: NOT SET\n";
            }
        }
        
    } else {
        echo "Table student_curricula does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
