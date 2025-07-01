<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🎤 فحص جلسات التسميع في 2025-06-30:\n";
echo "=========================================\n";

$sessions = DB::table('recitation_sessions')
    ->whereDate('created_at', '2025-06-30')
    ->get();

echo "عدد الجلسات: " . $sessions->count() . "\n\n";

foreach ($sessions as $session) {
    echo "جلسة ID: $session->id\n";
    echo "   معلم ID: $session->teacher_id\n";
    echo "   طالب ID: $session->student_id\n";
    echo "   نوع التسميع: $session->recitation_type\n";
    echo "   الدرجة: $session->grade\n";
    echo "   تاريخ الإنشاء: $session->created_at\n";
    echo "   ---\n";
}

echo "\n🎤 جميع جلسات التسميع:\n";
$allSessions = DB::table('recitation_sessions')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

foreach ($allSessions as $session) {
    echo "جلسة: $session->id, معلم: $session->teacher_id, تاريخ: $session->created_at\n";
}

?>
