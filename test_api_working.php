<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// إعداد العميل
$client = new Client([
    'base_uri' => 'http://localhost:8000',
    'timeout' => 30,
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]
]);

echo "=== اختبار API للحضور ===\n";
echo "الوقت: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. اختبار الاتصال الأساسي
    echo "1. اختبار الاتصال مع الخادم...\n";
    $response = $client->get('/');
    echo "✅ الخادم يعمل - كود الاستجابة: " . $response->getStatusCode() . "\n\n";

    // 2. اختبار تسجيل دخول المعلم
    echo "2. اختبار تسجيل دخول المعلم...\n";
    $loginData = [
        'identity_number' => '1234567890',
        'password' => 'password123'
    ];
    
    echo "بيانات الدخول: " . json_encode($loginData, JSON_UNESCAPED_UNICODE) . "\n";
    
    $response = $client->post('/api/teacher/login', [
        'json' => $loginData
    ]);
    
    $loginResult = json_decode($response->getBody(), true);
    echo "نتيجة تسجيل الدخول: " . json_encode($loginResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
    if (isset($loginResult['token'])) {
        $token = $loginResult['token'];
        echo "✅ تم الحصول على التوكن بنجاح\n\n";
        
        // 3. اختبار تسجيل الحضور
        echo "3. اختبار تسجيل حضور الطالب...\n";
        $attendanceData = [
            'student_id' => 1,
            'date' => date('Y-m-d'),
            'status' => 'حاضر',
            'period' => 'الفترة الصباحية',
            'notes' => 'اختبار تسجيل الحضور'
        ];
        
        echo "بيانات الحضور: " . json_encode($attendanceData, JSON_UNESCAPED_UNICODE) . "\n";
        
        $response = $client->post('/api/student-attendance', [
            'json' => $attendanceData,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
        
        $attendanceResult = json_decode($response->getBody(), true);
        echo "نتيجة تسجيل الحضور: " . json_encode($attendanceResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        echo "✅ تم تسجيل الحضور بنجاح\n\n";
        
        // 4. اختبار جلب بيانات الحضور
        echo "4. اختبار جلب بيانات الحضور...\n";
        $response = $client->get('/api/student-attendance', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);
        
        $attendanceList = json_decode($response->getBody(), true);
        echo "قائمة الحضور: " . json_encode($attendanceList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        echo "✅ تم جلب بيانات الحضور بنجاح\n\n";
        
    } else {
        echo "❌ فشل في الحصول على التوكن\n";
        echo "الاستجابة: " . json_encode($loginResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (RequestException $e) {
    echo "❌ خطأ في الطلب: " . $e->getMessage() . "\n";
    
    if ($e->hasResponse()) {
        $response = $e->getResponse();
        echo "كود الخطأ: " . $response->getStatusCode() . "\n";
        echo "نص الخطأ: " . $response->getBody() . "\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ عام: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
