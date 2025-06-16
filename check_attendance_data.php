<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// فحص أول سجل في جدول حضور الطلاب
$attendance = \App\Models\StudentAttendance::first();
echo "أول سجل حضور:\n";
var_dump($attendance);

echo "\n\nفحص القيم الممكنة للحالة:\n";
$reflection = new ReflectionClass(\App\Models\StudentAttendance::class);
$constants = $reflection->getConstants();
foreach ($constants as $name => $value) {
    if (strpos($name, 'STATUS') !== false) {
        echo "$name: $value\n";
    }
}

echo "\n\nفحص جميع الحالات الموجودة في قاعدة البيانات:\n";
$statuses = \App\Models\StudentAttendance::select('status')->distinct()->get();
foreach ($statuses as $status) {
    echo "الحالة: " . ($status->status ?? 'NULL') . "\n";
}

echo "\n\nعدد السجلات الإجمالي: " . \App\Models\StudentAttendance::count() . "\n";
