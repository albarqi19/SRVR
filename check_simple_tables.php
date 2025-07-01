<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "فحص جداول الحضور والتسميع:\n";

try {
    // فحص جدول student_attendances
    $count = DB::table('student_attendances')->count();
    echo "جدول student_attendances: {$count} سجل\n";
    
    if($count > 0) {
        $latest = DB::table('student_attendances')->orderBy('created_at', 'desc')->first();
        echo "آخر حضور: " . json_encode($latest, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch(Exception $e) {
    echo "خطأ في student_attendances: " . $e->getMessage() . "\n";
}

try {
    // فحص جدول recitation_sessions
    $count = DB::table('recitation_sessions')->count();
    echo "جدول recitation_sessions: {$count} سجل\n";
    
    if($count > 0) {
        $latest = DB::table('recitation_sessions')->orderBy('created_at', 'desc')->first();
        echo "آخر تسميع: " . json_encode($latest, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch(Exception $e) {
    echo "خطأ في recitation_sessions: " . $e->getMessage() . "\n";
}
