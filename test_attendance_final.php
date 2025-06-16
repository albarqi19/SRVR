<?php
echo "=== اختبار API حضور الطلاب ===\n\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    // البحث عن طالب ومعلم
    $student = App\Models\Student::first();
    $teacher = App\Models\Teacher::first();
    
    if (!$student || !$teacher) {
        echo "❌ لا يوجد طلاب أو معلمين في النظام\n";
        exit;
    }
    
    echo "الطالب: {$student->name} (ID: {$student->id})\n";
    echo "المعلم: {$teacher->name} (ID: {$teacher->id})\n\n";
    
    // اختبار API القديم أولاً
    echo "=== اختبار API القديم ===\n";
    $request = Illuminate\Http\Request::create('/api/attendance/record', 'POST', [
        'student_name' => $student->name,
        'date' => '2025-06-08',
        'status' => 'present',
        'period' => 'العصر',
        'notes' => 'اختبار API'
    ]);
    
    $controller = new App\Http\Controllers\Api\StudentAttendanceController();
    $response = $controller->store($request);
    $data = $response->getData(true);
    
    echo "نتيجة API القديم: " . ($data['success'] ? '✅ نجح' : '❌ فشل') . "\n";
    if (!$data['success']) {
        echo "الخطأ: " . json_encode($data['errors'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";
    
    // اختبار API الجديد
    echo "=== اختبار API الجديد ===\n";
    $requestNew = Illuminate\Http\Request::create('/api/attendance/record-batch', 'POST', [
        'teacherId' => $teacher->id,
        'date' => '2025-06-08',
        'time' => '14:30:00',
        'students' => [
            [
                'studentId' => $student->id,
                'status' => 'حاضر',
                'notes' => 'اختبار API الجديد'
            ]
        ]
    ]);
    
    try {
        $responseNew = $controller->storeBatch($requestNew);
        $dataNew = $responseNew->getData(true);
        
        echo "نتيجة API الجديد: " . ($dataNew['success'] ? '✅ نجح' : '❌ فشل') . "\n";
        if (!$dataNew['success']) {
            echo "الخطأ: " . json_encode($dataNew['errors'], JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "تم تسجيل حضور {$dataNew['summary']['success']} طالب\n";
        }
    } catch (Exception $e) {
        echo "❌ خطأ في API الجديد: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ عام: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
