<?php

echo "=== Simple PHP Test ===" . PHP_EOL;
echo "Current time: " . date('Y-m-d H:i:s') . PHP_EOL;

// Test basic PHP functionality
echo "PHP Version: " . PHP_VERSION . PHP_EOL;

// Test if we can load Laravel
try {
    require 'vendor/autoload.php';
    echo "✅ Autoload successful" . PHP_EOL;
    
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel app loaded" . PHP_EOL;
    
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "✅ Laravel bootstrapped" . PHP_EOL;
    
    // Test database connection
    $connection = Illuminate\Support\Facades\DB::connection();
    $pdo = $connection->getPdo();
    echo "✅ Database connected" . PHP_EOL;
    
    // Simple database test
    $result = Illuminate\Support\Facades\DB::select('SELECT 1 as test');
    echo "✅ Database query successful: " . $result[0]->test . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
}

echo "=== Test completed ===" . PHP_EOL;
