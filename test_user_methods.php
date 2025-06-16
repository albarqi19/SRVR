<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// اختبار وجود الدوال
$user = new App\Models\User();

echo "Testing User methods:\n";
echo "hasRole method exists: " . (method_exists($user, 'hasRole') ? 'YES' : 'NO') . "\n";
echo "can method exists: " . (method_exists($user, 'can') ? 'YES' : 'NO') . "\n";

// اختبار traits
$traits = class_uses($user);
echo "\nTraits used by User model:\n";
foreach ($traits as $trait) {
    echo "- $trait\n";
}

// اختبار حقيقي للدوال
try {
    $testUser = App\Models\User::first();
    if ($testUser) {
        echo "\nTesting with real user:\n";
        echo "User ID: " . $testUser->id . "\n";
        echo "User name: " . $testUser->name . "\n";
        echo "hasRole('admin'): " . ($testUser->hasRole('admin') ? 'true' : 'false') . "\n";
        echo "can('view_users'): " . ($testUser->can('view_users') ? 'true' : 'false') . "\n";
    } else {
        echo "\nNo users found in database\n";
    }
} catch (Exception $e) {
    echo "\nError testing with real user: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
