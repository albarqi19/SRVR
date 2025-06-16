<?php

// تشغيل هذا الملف لاختبار API الحضور الجديد
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار API حضور الطلاب الجديد ===\n\n";

// التحقق من وجود طالب للاختبار
$student = App\Models\Student::first();
if (!$student) {
    echo "إنشاء طالب تجريبي...\n";
    $student = App\Models\Student::create([
        'identity_number' => 'TEST123456',
        'name' => 'أحمد محمد التجريبي',
        'nationality' => 'سعودي',
        'phone' => '0500000000',
        'password' => bcrypt('password'),
        'is_active_user' => true,
        'is_active' => true
    ]);
}

// التحقق من وجود معلم للاختبار
$teacher = App\Models\Teacher::first();
if (!$teacher) {
    echo "إنشاء معلم تجريبي...\n";
    $teacher = App\Models\Teacher::create([
        'identity_number' => 'TEACHER123',
        'name' => 'الأستاذ محمد التجريبي',
        'nationality' => 'سعودي',
        'phone' => '0500000001',
        'password' => bcrypt('password'),
        'is_active_user' => true
    ]);
}

echo "الطالب: {$student->name} (ID: {$student->id})\n";
echo "المعلم: {$teacher->name} (ID: {$teacher->id})\n\n";

// بيانات الاختبار بنفس التنسيق الذي ترسله
$testData = [
    'teacherId' => $teacher->id,
    'date' => '2025-06-08',
    'time' => '14:30:00',
    'students' => [
        [
            'studentId' => $student->id,
            'status' => 'حاضر',
            'notes' => 'حضر في الوقت المحدد'
        ]
    ]
];

echo "بيانات الاختبار:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// محاكاة طلب HTTP
$request = Illuminate\Http\Request::create(
    '/api/attendance/record-batch',
    'POST',
    $testData,
    [],
    [],
    ['CONTENT_TYPE' => 'application/json']
);

try {
    $controller = new App\Http\Controllers\Api\StudentAttendanceController();
    $response = $controller->storeBatch($request);
    
    echo "=== نتيجة الاختبار ===\n";
    echo "كود الاستجابة: " . $response->getStatusCode() . "\n";
    
    $responseData = $response->getData(true);
    echo "الاستجابة:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if ($responseData['success']) {
        echo "✅ API يعمل بنجاح!\n";
        echo "تم تسجيل حضور {$responseData['summary']['success']} طالب\n";
    } else {
        echo "❌ API فشل:\n";
        echo "الرسالة: " . $responseData['message'] . "\n";
        if (isset($responseData['errors'])) {
            echo "الأخطاء: " . json_encode($responseData['errors'], JSON_UNESCAPED_UNICODE) . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ خطأ في تشغيل الاختبار:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
