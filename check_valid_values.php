<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Valid Values for RecitationSession ===" . PHP_EOL;

// Check database schema
try {
    $pdo = DB::connection()->getPdo();
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE recitation_sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n--- Table Structure ---" . PHP_EOL;
    foreach ($columns as $column) {
        if (in_array($column['Field'], ['recitation_type', 'evaluation', 'status'])) {
            echo "Field: {$column['Field']}" . PHP_EOL;
            echo "Type: {$column['Type']}" . PHP_EOL;
            echo "Default: {$column['Default']}" . PHP_EOL;
            echo "---" . PHP_EOL;
        }
    }
    
    // Check existing values in database
    echo "\n--- Existing Values in Database ---" . PHP_EOL;
    
    $recitationTypes = DB::table('recitation_sessions')
        ->select('recitation_type')
        ->distinct()
        ->whereNotNull('recitation_type')
        ->get();
    
    echo "Recitation Types in DB:" . PHP_EOL;
    foreach ($recitationTypes as $type) {
        echo "- '{$type->recitation_type}'" . PHP_EOL;
    }
    
    $evaluations = DB::table('recitation_sessions')
        ->select('evaluation')
        ->distinct()
        ->whereNotNull('evaluation')
        ->get();
    
    echo "\nEvaluations in DB:" . PHP_EOL;
    foreach ($evaluations as $eval) {
        echo "- '{$eval->evaluation}'" . PHP_EOL;
    }
    
    $statuses = DB::table('recitation_sessions')
        ->select('status')
        ->distinct()
        ->whereNotNull('status')
        ->get();
    
    echo "\nStatuses in DB:" . PHP_EOL;
    foreach ($statuses as $status) {
        echo "- '{$status->status}'" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

echo "\n=== Testing API Validation Rules ===" . PHP_EOL;

// Let's also check what the validation rules expect by testing individual values
$testValues = [
    'recitation_type' => ['حفظ', 'مراجعة صغرى', 'مراجعة كبرى', 'تثبيت'],
    'evaluation' => ['ممتاز', 'جيد جداً', 'جيد', 'مقبول', 'ضعيف'],
    'status' => ['جارية', 'غير مكتملة', 'مكتملة']
];

foreach ($testValues as $field => $values) {
    echo "\nTesting $field values:" . PHP_EOL;
    foreach ($values as $value) {
        echo "- '$value' (bytes: " . bin2hex($value) . ")" . PHP_EOL;
    }
}
