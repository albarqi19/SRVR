<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Users Table Contents ===\n";
$users = \App\Models\User::select('id', 'name', 'email')->get();

foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
}

echo "\n=== Teachers Table Contents ===\n";
$teachers = \App\Models\Teacher::select('id', 'name', 'email')->get();

foreach ($teachers as $teacher) {
    echo "ID: {$teacher->id}, Name: {$teacher->name}, Email: {$teacher->email}\n";
}
