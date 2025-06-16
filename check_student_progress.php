<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentProgress;

$progress = StudentProgress::where('student_id', 1)->where('is_active', true)->first();
if ($progress) {
    echo "✅ يوجد سجل نشط للطالب 1. curriculum_id = {$progress->curriculum_id}\n";
} else {
    echo "❌ لا يوجد سجل نشط للطالب 1 في StudentProgress.\n";
}
