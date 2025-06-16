<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== بنية جدول المعلمين ===\n";
$teachers = DB::select('DESCRIBE teachers');
foreach($teachers as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}

echo "\n=== بنية جدول الطلاب ===\n";
$students = DB::select('DESCRIBE students');
foreach($students as $col) {
    echo "- {$col->Field} ({$col->Type})\n";
}
