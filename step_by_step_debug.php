<?php

echo "=== Step by Step Debug ===" . PHP_EOL;

// Step 1: Basic PHP
echo "Step 1: Basic PHP" . PHP_EOL;
echo "PHP Version: " . PHP_VERSION . PHP_EOL;
echo "Current directory: " . getcwd() . PHP_EOL;

// Step 2: Check if vendor exists
echo "\nStep 2: Check files" . PHP_EOL;
echo "vendor/autoload.php exists: " . (file_exists('vendor/autoload.php') ? 'YES' : 'NO') . PHP_EOL;
echo "bootstrap/app.php exists: " . (file_exists('bootstrap/app.php') ? 'YES' : 'NO') . PHP_EOL;
echo ".env exists: " . (file_exists('.env') ? 'YES' : 'NO') . PHP_EOL;

// Step 3: Try autoload
echo "\nStep 3: Try autoload" . PHP_EOL;
try {
    require 'vendor/autoload.php';
    echo "✅ Autoload successful" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Autoload failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Step 4: Try to load app
echo "\nStep 4: Try to load Laravel app" . PHP_EOL;
try {
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel app loaded" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Laravel app load failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "\n=== All steps completed successfully ===" . PHP_EOL;
