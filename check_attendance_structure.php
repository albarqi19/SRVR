<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Checking Attendances Table Structure ===\n";

if (Schema::hasTable('attendances')) {
    echo "✅ Attendances table exists\n";
    
    $columns = Schema::getColumnListing('attendances');
    echo "\n📋 Columns in attendances table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
    $count = DB::table('attendances')->count();
    echo "\n📊 Total records: $count\n";
    
    if ($count > 0) {
        echo "\n📄 Sample record:\n";
        $sample = DB::table('attendances')->first();
        foreach ($sample as $key => $value) {
            echo "  $key: $value\n";
        }
    }
} else {
    echo "❌ Attendances table does not exist\n";
}
