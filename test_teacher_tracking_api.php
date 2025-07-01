<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎯 اختبار API تتبع المعلم - الحضور والتسميع\n";
echo str_repeat("=", 60) . "\n\n";

// استدعاء الـ Controller مباشرة
use App\Http\Controllers\Api\SupervisorController;
use Illuminate\Http\Request;

$controller = new SupervisorController();

// إنشاء request
$request = new Request([
    'supervisor_id' => 1,
    'date' => '2025-07-01'
]);

echo "📊 اختبار API نشاط المعلمين اليومي:\n";
echo "المشرف ID: 1\n";
echo "التاريخ: 2025-07-01\n\n";

try {
    $response = $controller->getTeacherDailyActivity($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ API يعمل بنجاح!\n\n";
        
        $summary = $data['data']['summary'];
        echo "📋 ملخص النشاط:\n";
        echo "   إجمالي المعلمين: {$summary['total_teachers']}\n";
        echo "   المعلمين النشطين: {$summary['active_teachers']}\n";
        echo "   سجلوا الحضور: {$summary['attendance_recorded']}\n";
        echo "   سجلوا التسميع: {$summary['recitation_recorded']}\n";
        echo "   معدل الإنجاز: {$summary['completion_rate']}%\n";
        echo "   نسبة التحضير: {$summary['attendance_percentage']}%\n";
        echo "   نسبة التسميع: {$summary['recitation_percentage']}%\n\n";
        
        if (!empty($data['data']['teachers_activity'])) {
            echo "👥 تفاصيل المعلمين:\n";
            foreach ($data['data']['teachers_activity'] as $teacher) {
                $activity = $teacher['daily_activity'];
                echo "   📚 المعلم: {$teacher['teacher_name']}\n";
                echo "      الحلقة: " . ($teacher['circle']['name'] ?? 'غير محدد') . "\n";
                echo "      الحالة: {$activity['activity_status']}\n";
                echo "      التحضير: " . ($activity['attendance_recorded'] ? 'تم (' . $activity['attendance_percentage'] . '%)' : 'لم يتم') . "\n";
                echo "      التسميع: " . ($activity['recitation_recorded'] ? 'تم (' . $activity['recitation_percentage'] . '%)' : 'لم يتم') . "\n";
                echo "      الطلاب: {$activity['students_count']}\n";
                echo "      الملخص: {$activity['details']['completion_summary']}\n\n";
            }
        } else {
            echo "ℹ️ لا توجد بيانات معلمين لهذا المشرف\n\n";
        }
        
    } else {
        echo "❌ فشل API: {$data['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: {$e->getMessage()}\n";
}

echo str_repeat("=", 60) . "\n";
echo "📊 اختبار API إحصائيات المعلمين:\n";
echo str_repeat("=", 60) . "\n\n";

// اختبار API الإحصائيات
$request2 = new Request([
    'supervisor_id' => 1,
    'start_date' => '2025-06-24',
    'end_date' => '2025-07-01'
]);

try {
    $response2 = $controller->getTeachersActivityStatistics($request2);
    $data2 = json_decode($response2->getContent(), true);
    
    if ($data2['success']) {
        echo "✅ API الإحصائيات يعمل بنجاح!\n\n";
        
        if (isset($data2['data']['overall_summary'])) {
            $summary = $data2['data']['overall_summary'];
            echo "📈 الملخص العام:\n";
            echo "   إجمالي المعلمين: {$summary['total_teachers']}\n";
            echo "   متوسط أيام التحضير: {$summary['average_attendance_days']}\n";
            echo "   متوسط أيام التسميع: {$summary['average_recitation_days']}\n";
            echo "   متوسط الأداء: {$summary['average_performance_score']}\n";
            echo "   معدل التحضير: {$summary['attendance_rate']}%\n";
            echo "   معدل التسميع: {$summary['recitation_rate']}%\n\n";
        }
        
    } else {
        echo "❌ فشل API الإحصائيات: {$data2['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ في الإحصائيات: {$e->getMessage()}\n";
}

echo "🔗 APIs المتاحة:\n";
echo "1. النشاط اليومي: GET /api/supervisors/teachers-daily-activity\n";
echo "2. الإحصائيات: GET /api/supervisors/teachers-activity-statistics\n\n";

echo "✨ انتهى اختبار APIs تتبع المعلم!\n";

?>
