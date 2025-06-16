<?php

echo "Starting validation test...\n";

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "Laravel loaded successfully...\n";

// Test simple validation
$data = ['recitation_type' => 'حفظ'];
$rules = ['recitation_type' => 'required|in:حفظ,مراجعة صغرى,مراجعة كبرى,تثبيت'];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "VALIDATION FAILED!\n";
    echo "Errors: " . json_encode($validator->errors()->all()) . "\n";
} else {
    echo "VALIDATION PASSED!\n";
}

echo "Test completed.\n";
