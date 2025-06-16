<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking available curricula:\n";
echo "============================\n";

try {
    $curricula = App\Models\Curriculum::all(['id', 'name']);
    
    if ($curricula->count() > 0) {
        echo "Found " . $curricula->count() . " curricula:\n\n";
        foreach ($curricula as $curriculum) {
            echo "ID: {$curriculum->id} - Name: {$curriculum->name}\n";
        }
    } else {
        echo "No curricula found in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n============================\n";
echo "Done.\n";
