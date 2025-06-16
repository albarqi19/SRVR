<?php
echo "Starting debug..." . PHP_EOL;
flush();

try {
    echo "Loading autoload..." . PHP_EOL;
    flush();
    require 'vendor/autoload.php';
    echo "Autoload loaded!" . PHP_EOL;
    flush();
    
    echo "Loading Laravel app..." . PHP_EOL;
    flush();
    $app = require_once 'bootstrap/app.php';
    echo "Laravel app loaded!" . PHP_EOL;
    flush();
    
    echo "Bootstrapping..." . PHP_EOL;
    flush();
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Kernel created!" . PHP_EOL;
    flush();
    
    $kernel->bootstrap();
    echo "Bootstrap completed!" . PHP_EOL;
    flush();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . PHP_EOL;
    echo "Line: " . $e->getLine() . PHP_EOL;
}

echo "Debug completed!" . PHP_EOL;
