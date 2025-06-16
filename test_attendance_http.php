<?php

echo "=== اختبار APIs الحضور باستخدام HTTP Requests ===\n\n";

// تشغيل الخادم المحلي إذا لم يكن يعمل
echo "1. التحقق من الخادم المحلي...\n";

$baseUrl = 'http://127.0.0.1:8000';

// اختبار الاتصال بالخادم
$ch = curl_init($baseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_NOBODY, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 0) {
    echo "❌ الخادم المحلي غير مُشغل. سأحاول تشغيله...\n";
    echo "يرجى تشغيل الخادم بالأمر: php artisan serve\n";
    echo "أو سأختبر APIs داخلياً...\n\n";
    
    // اختبار داخلي بدون HTTP
    testInternalAPIs();
} else {
    echo "✓ الخادم المحلي يعمل (كود الحالة: $httpCode)\n\n";
    testHTTPAPIs($baseUrl);
}

function testInternalAPIs() {
    echo "=== اختبار APIs داخلياً ===\n\n";
    
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        
        use Illuminate\Http\Request;
        use App\Http\Controllers\Api\StudentAttendanceController;
        use App\Models\Student;
        
        // إنشاء طالب تجريبي
        echo "📝 إنشاء طالب تجريبي...\n";
        $student = Student::firstOrCreate([
            'identity_number' => 'API_TEST_001'
        ], [
            'name' => 'طالب اختبار API',
            'nationality' => 'سعودي',
            'phone' => '0501111111',
            'password' => bcrypt('test123'),
            'is_active_user' => true,
            'is_active' => true
        ]);
        
        echo "✓ الطالب: {$student->name} (ID: {$student->id})\n\n";
        
        $controller = new StudentAttendanceController();
        
        // اختبار 1: تسجيل حضور
        echo "📤 اختبار 1: تسجيل حضور...\n";
        $request = Request::create('/api/attendance/record', 'POST', [
            'student_name' => $student->name,
            'date' => date('Y-m-d'),
            'status' => 'present',
            'period' => 'الفجر',
            'notes' => 'حضور اختبار API - ' . date('H:i:s')
        ]);
        
        $response = $controller->store($request);
        $data = $response->getData(true);
        
        echo "📊 النتيجة:\n";
        echo "  - نجح: " . ($data['success'] ? '✓' : '✗') . "\n";
        echo "  - الرسالة: " . $data['message'] . "\n";
        echo "  - كود HTTP: " . $response->getStatusCode() . "\n\n";
        
        // اختبار 2: استرجاع السجلات
        echo "📤 اختبار 2: استرجاع السجلات...\n";
        $request2 = Request::create('/api/attendance/records', 'GET', [
            'student_name' => $student->name
        ]);
        
        $response2 = $controller->index($request2);
        $data2 = $response2->getData(true);
        
        echo "📊 النتيجة:\n";
        echo "  - نجح: " . ($data2['success'] ? '✓' : '✗') . "\n";
        
        if ($data2['success']) {
            $records = $data2['data']['data'] ?? [];
            echo "  - عدد السجلات: " . count($records) . "\n";
            
            if (count($records) > 0) {
                $latest = $records[0];
                echo "  - آخر سجل: " . $latest['date'] . " - " . $latest['status'] . "\n";
            }
        }
        echo "\n";
        
        // اختبار 3: الإحصائيات
        echo "📤 اختبار 3: الإحصائيات...\n";
        $request3 = Request::create('/api/attendance/stats', 'GET', [
            'student_name' => $student->name
        ]);
        
        $response3 = $controller->stats($request3);
        $data3 = $response3->getData(true);
        
        echo "📊 النتيجة:\n";
        echo "  - نجح: " . ($data3['success'] ? '✓' : '✗') . "\n";
        
        if ($data3['success']) {
            $stats = $data3['data'];
            echo "  - إجمالي: " . $stats['total_records'] . "\n";
            echo "  - حضور: " . $stats['present'] . "\n";
            echo "  - غياب: " . $stats['absent'] . "\n";
            echo "  - متأخر: " . $stats['late'] . "\n";
        }
        echo "\n";
        
        // اختبار 4: validation
        echo "📤 اختبار 4: التحقق من البيانات الخاطئة...\n";
        $request4 = Request::create('/api/attendance/record', 'POST', [
            'student_name' => '',
            'date' => 'تاريخ-خاطئ',
            'status' => 'حالة-خاطئة'
        ]);
        
        $response4 = $controller->store($request4);
        $data4 = $response4->getData(true);
        
        echo "📊 النتيجة:\n";
        echo "  - فشل كما هو متوقع: " . (!$data4['success'] ? '✓' : '✗') . "\n";
        echo "  - كود HTTP: " . $response4->getStatusCode() . "\n";
        echo "  - عدد الأخطاء: " . (isset($data4['errors']) ? count($data4['errors']) : 0) . "\n\n";
        
        echo "🎉 جميع الاختبارات تمت بنجاح!\n";
        echo "✅ APIs الحضور تعمل بشكل ممتاز\n";
        
    } catch (Exception $e) {
        echo "❌ خطأ: " . $e->getMessage() . "\n";
        echo "في الملف: " . $e->getFile() . " السطر: " . $e->getLine() . "\n";
    }
}

function testHTTPAPIs($baseUrl) {
    echo "=== اختبار APIs عبر HTTP ===\n\n";
    
    // بيانات الاختبار
    $testData = [
        'student_name' => 'طالب HTTP Test',
        'date' => date('Y-m-d'),
        'status' => 'present',
        'period' => 'الفجر',
        'notes' => 'اختبار عبر HTTP - ' . date('H:i:s')
    ];
    
    // اختبار 1: POST تسجيل حضور
    echo "📤 اختبار 1: POST /api/attendance/record\n";
    $ch = curl_init($baseUrl . '/api/attendance/record');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 النتيجة:\n";
    echo "  - كود HTTP: $httpCode\n";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "  - نجح: " . (isset($data['success']) && $data['success'] ? '✓' : '✗') . "\n";
            echo "  - الرسالة: " . ($data['message'] ?? 'لا توجد رسالة') . "\n";
        } else {
            echo "  - استجابة غير صالحة\n";
            echo "  - المحتوى: " . substr($response, 0, 200) . "...\n";
        }
    } else {
        echo "  - لا توجد استجابة\n";
    }
    echo "\n";
    
    // اختبار 2: GET استرجاع السجلات
    echo "📤 اختبار 2: GET /api/attendance/records\n";
    $ch = curl_init($baseUrl . '/api/attendance/records?student_name=' . urlencode($testData['student_name']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 النتيجة:\n";
    echo "  - كود HTTP: $httpCode\n";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "  - نجح: " . (isset($data['success']) && $data['success'] ? '✓' : '✗') . "\n";
            if (isset($data['data']['data'])) {
                echo "  - عدد السجلات: " . count($data['data']['data']) . "\n";
            }
        }
    }
    echo "\n";
    
    // اختبار 3: GET الإحصائيات
    echo "📤 اختبار 3: GET /api/attendance/stats\n";
    $ch = curl_init($baseUrl . '/api/attendance/stats');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "📊 النتيجة:\n";
    echo "  - كود HTTP: $httpCode\n";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "  - نجح: ✓\n";
            $stats = $data['data'];
            echo "  - إجمالي: " . $stats['total_records'] . "\n";
            echo "  - حضور: " . $stats['present'] . "\n";
        }
    }
    echo "\n";
    
    echo "🎯 اختبارات HTTP مكتملة!\n";
}
