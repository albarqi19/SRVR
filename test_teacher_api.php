<?php

/**
 * اختبار API endpoints للمعلمين
 */

echo "🧪 اختبار Teacher API endpoints...\n\n";

// Base URL للـ API
$baseUrl = 'http://127.0.0.1:8000/api';

// دالة لاختبار API endpoint
function testEndpoint($url, $description) {
    echo "🔍 اختبار: $description\n";
    echo "📍 URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ خطأ في الاتصال: $error\n\n";
        return false;
    }
    
    echo "📊 HTTP Status: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "✅ نجح الاختبار!\n";
        $data = json_decode($response, true);
        if ($data) {
            echo "📄 البيانات المُستلمة:\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ فشل الاختبار!\n";
        echo "📄 الاستجابة: $response\n";
    }
    
    echo str_repeat("─", 80) . "\n\n";
    return $httpCode === 200;
}

// اختبار endpoints المختلفة
$tests = [
    "$baseUrl/teachers" => "جلب قائمة المعلمين",
    "$baseUrl/teachers?search=أحمد" => "البحث في المعلمين",
    "$baseUrl/teachers/1" => "تفاصيل معلم محدد (ID=1)",
    "$baseUrl/teachers/1/circles" => "حلقات المعلم (ID=1)",
    "$baseUrl/teachers/1/students" => "طلاب المعلم (ID=1)",
    "$baseUrl/teachers/1/mosques" => "مساجد المعلم مع الطلاب (ID=1)"
];

$successCount = 0;
$totalTests = count($tests);

foreach ($tests as $url => $description) {
    if (testEndpoint($url, $description)) {
        $successCount++;
    }
    
    // تأخير قصير بين الاختبارات
    sleep(1);
}

echo "📈 نتائج الاختبار:\n";
echo "✅ نجح: $successCount/$totalTests اختبارات\n";
echo "❌ فشل: " . ($totalTests - $successCount) . "/$totalTests اختبارات\n";

if ($successCount === $totalTests) {
    echo "🎉 جميع الاختبارات نجحت!\n";
} else {
    echo "⚠️  بعض الاختبارات فشلت. تحقق من البيانات والاتصال بقاعدة البيانات.\n";
}
