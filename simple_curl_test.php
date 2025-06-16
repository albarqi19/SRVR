<?php

// اختبار API الحضور - نسخة مبسطة
echo "=== اختبار API حضور الطلاب ===\n";
echo "التاريخ: " . date('Y-m-d H:i:s') . "\n\n";

// بدلاً من استخدام bootstrap Laravel المعقد، سنستخدم cURL مباشرة
$url = 'http://127.0.0.1:8000/api/attendance/record-batch';

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

echo "البيانات المرسلة:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// إنشاء طلب cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10
]);

echo "إرسال طلب إلى: $url\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo "❌ خطأ في cURL: $error\n";
    exit(1);
}

echo "كود HTTP: $httpCode\n";
echo "الاستجابة:\n";

if ($response) {
    $decodedResponse = json_decode($response, true);
    if ($decodedResponse) {
        echo json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (isset($decodedResponse['success']) && $decodedResponse['success']) {
            echo "\n✅ API يعمل بنجاح!\n";
        } else {
            echo "\n❌ API فشل!\n";
        }
    } else {
        echo "استجابة غير صالحة: $response\n";
    }
} else {
    echo "لا توجد استجابة\n";
}

echo "\n=== انتهى الاختبار ===\n";
