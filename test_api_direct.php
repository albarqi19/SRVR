<?php

echo "=== اختبار API مباشر ===\n\n";

// 1. اختبار تسجيل الحضور مباشرة
echo "1. اختبار تسجيل الحضور:\n";

$data = [
    'student_id' => 1,
    'date' => '2025-06-08',
    'status' => 'حاضر',
    'period' => 'صباحي'
];

$url = 'http://127.0.0.1:8000/api/attendance/record';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "Curl Error: $error\n";
}
echo "Response: $response\n\n";

// 2. اختبار تسجيل دخول المعلم
echo "2. اختبار تسجيل دخول المعلم:\n";

$loginData = [
    'identity_number' => '1074554773',
    'password' => '0530996778'
];

$loginUrl = 'http://127.0.0.1:8000/api/auth/teacher/login';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $loginUrl);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($curl);
$loginHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$loginError = curl_error($curl);
curl_close($curl);

echo "HTTP Code: $loginHttpCode\n";
if ($loginError) {
    echo "Curl Error: $loginError\n";
}
echo "Response: $loginResponse\n";

// 3. إذا نجح تسجيل الدخول، اختبر API مع التوكن
if ($loginHttpCode == 200) {
    $loginData = json_decode($loginResponse, true);
    if (isset($loginData['token'])) {
        echo "\n3. اختبار الحضور مع التوكن:\n";
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $loginData['token']
        ]);

        $authResponse = curl_exec($curl);
        $authHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $authError = curl_error($curl);
        curl_close($curl);

        echo "HTTP Code: $authHttpCode\n";
        if ($authError) {
            echo "Curl Error: $authError\n";
        }
        echo "Response: $authResponse\n";
    }
}

echo "\n=== انتهى الاختبار ===\n";
