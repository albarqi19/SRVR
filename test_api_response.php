<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\SupervisorController;

echo "🔍 اختبار استجابة API مباشرة:\n";
echo str_repeat('=', 50) . "\n\n";

// محاكاة الطلب
$request = new Request([
    'supervisor_id' => 1,
    'date' => '2025-07-01'
]);

$controller = new SupervisorController();
$response = $controller->getTeacherDailyActivity($request);

$data = json_decode($response->getContent(), true);

echo "📊 الاستجابة:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

if (isset($data['data']['teachers_activity']) && count($data['data']['teachers_activity']) > 0) {
    echo "\n🔍 فحص بيانات المعلم الأول:\n";
    $firstTeacher = $data['data']['teachers_activity'][0];
    
    echo "الاسم: " . ($firstTeacher['teacher_name'] ?? 'غير محدد') . "\n";
    echo "daily_activity موجود؟ " . (isset($firstTeacher['daily_activity']) ? 'نعم' : 'لا') . "\n";
    
    if (isset($firstTeacher['daily_activity'])) {
        $activity = $firstTeacher['daily_activity'];
        echo "attendance_recorded: " . ($activity['attendance_recorded'] ? 'true' : 'false') . "\n";
        echo "recitation_recorded: " . ($activity['recitation_recorded'] ? 'true' : 'false') . "\n";
        echo "activity_status: " . ($activity['activity_status'] ?? 'غير محدد') . "\n";
    }
}
?>
