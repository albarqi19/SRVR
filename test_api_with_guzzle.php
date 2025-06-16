<?php

// اختبار API باستخدام Guzzle HTTP client
require_once 'vendor/autoload.php';

echo "=== اختبار API حضور الطلاب مع البيانات الحقيقية ===\n\n";

try {
    $client = new \GuzzleHttp\Client();
    
    // البيانات للإرسال (نفس التنسيق الذي ترسله من Frontend)
    $data = [
        'teacherId' => 1,
        'date' => '2025-06-08',
        'time' => '14:30:00',
        'students' => [
            [
                'studentId' => 1,
                'status' => 'حاضر',
                'notes' => 'حضر في الوقت المحدد'
            ]
        ]
    ];
    
    echo "البيانات المُرسلة:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // إرسال الطلب
    $response = $client->post('http://127.0.0.1:8000/api/attendance/record-batch', [
        'json' => $data,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]
    ]);
    
    echo "=== النتيجة ===\n";
    echo "كود الاستجابة: " . $response->getStatusCode() . "\n";
    
    $responseBody = $response->getBody()->getContents();
    $responseData = json_decode($responseBody, true);
    
    echo "الاستجابة:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if ($responseData['success']) {
        echo "✅ نجح تسجيل الحضور!\n";
        echo "تفاصيل: " . $responseData['message'] . "\n";
    } else {
        echo "❌ فشل تسجيل الحضور!\n";
        echo "السبب: " . $responseData['message'] . "\n";
    }
    
} catch (GuzzleHttp\Exception\RequestException $e) {
    echo "❌ خطأ في الطلب:\n";
    echo "الرسالة: " . $e->getMessage() . "\n";
    
    if ($e->hasResponse()) {
        $response = $e->getResponse();
        echo "كود الخطأ: " . $response->getStatusCode() . "\n";
        echo "تفاصيل الخطأ: " . $response->getBody()->getContents() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ عام:\n";
    echo $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
