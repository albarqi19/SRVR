<?php

// اختبار مباشر للـ API
$url = 'http://localhost:8000/api/teachers/1/circles';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "=== اختبار API endpoint: /api/teachers/1/circles ===\n";
echo "HTTP Status Code: " . $httpCode . "\n";
echo "Error: " . ($error ?: 'لا يوجد') . "\n";
echo "Response: \n" . $response . "\n";
echo "================================\n";

// اختبار قاعدة البيانات مباشرة
echo "\n=== اختبار قاعدة البيانات مباشرة ===\n";

require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $teacher = \App\Models\Teacher::with(['quranCircle.students'])->find(1);
    
    if ($teacher) {
        echo "المعلم موجود: " . $teacher->name . "\n";
        echo "الحلقة: " . ($teacher->quranCircle ? $teacher->quranCircle->name : 'لا توجد') . "\n";
        if ($teacher->quranCircle) {
            echo "عدد الطلاب: " . $teacher->quranCircle->students->count() . "\n";
        }
    } else {
        echo "المعلم غير موجود\n";
    }
    
} catch (Exception $e) {
    echo "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n";
}
