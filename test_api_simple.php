<?php

echo "=== اختبار API مباشر بدون مكتبات ===\n";
echo "الوقت: " . date('Y-m-d H:i:s') . "\n\n";

// دالة مساعدة لإرسال طلبات HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $context_options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
        ]
    ];
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $context_options['http']['content'] = $data;
    }
    
    $context = stream_context_create($context_options);
    $result = file_get_contents($url, false, $context);
    
    $http_response_header_info = isset($http_response_header) ? $http_response_header : [];
    
    return [
        'body' => $result,
        'headers' => $http_response_header_info
    ];
}

try {
    // 1. اختبار الاتصال الأساسي
    echo "1. اختبار الاتصال مع الخادم...\n";
    $response = makeRequest('http://localhost:8000/');
    
    if ($response['body'] !== false) {
        echo "✅ الخادم يعمل\n\n";
        
        // 2. اختبار تسجيل دخول المعلم
        echo "2. اختبار تسجيل دخول المعلم...\n";
        $loginData = json_encode([
            'identity_number' => '1234567890',
            'password' => 'password123'
        ]);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $loginResponse = makeRequest(
            'http://localhost:8000/api/teacher/login',
            'POST',
            $loginData,
            $headers
        );
        
        echo "استجابة تسجيل الدخول:\n";
        echo $loginResponse['body'] . "\n\n";
        
        $loginResult = json_decode($loginResponse['body'], true);
        
        if (isset($loginResult['token'])) {
            $token = $loginResult['token'];
            echo "✅ تم الحصول على التوكن: " . substr($token, 0, 20) . "...\n\n";
            
            // 3. اختبار تسجيل الحضور
            echo "3. اختبار تسجيل حضور الطالب...\n";
            $attendanceData = json_encode([
                'student_id' => 1,
                'date' => date('Y-m-d'),
                'status' => 'حاضر',
                'period' => 'الفترة الصباحية',
                'notes' => 'اختبار تسجيل الحضور'
            ]);
            
            $headersWithAuth = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ];
            
            $attendanceResponse = makeRequest(
                'http://localhost:8000/api/student-attendance',
                'POST',
                $attendanceData,
                $headersWithAuth
            );
            
            echo "استجابة تسجيل الحضور:\n";
            echo $attendanceResponse['body'] . "\n\n";
            
            // 4. اختبار جلب بيانات الحضور
            echo "4. اختبار جلب بيانات الحضور...\n";
            $getHeaders = [
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ];
            
            $getResponse = makeRequest(
                'http://localhost:8000/api/student-attendance',
                'GET',
                null,
                $getHeaders
            );
            
            echo "استجابة جلب الحضور:\n";
            echo $getResponse['body'] . "\n\n";
            
        } else {
            echo "❌ فشل في الحصول على التوكن\n";
            echo "تفاصيل الخطأ: " . $loginResponse['body'] . "\n";
        }
        
    } else {
        echo "❌ فشل في الاتصال مع الخادم\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
