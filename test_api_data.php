<?php
require 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Bootstrap Laravel
$app->singleton(
    \Illuminate\Contracts\Console\Kernel::class,
    \App\Console\Kernel::class
);

$app->singleton(
    \Illuminate\Contracts\Debug\ExceptionHandler::class,
    \App\Exceptions\Handler::class
);

$app->make(\Illuminate\Contracts\Console\Kernel::class);

echo "Getting sample data...\n";

try {
    // Get teachers
    $teachers = \App\Models\Teacher::select('id', 'name')->take(3)->get();
    echo "Teachers:\n";
    foreach ($teachers as $teacher) {
        echo "ID: {$teacher->id} - Name: {$teacher->name}\n";