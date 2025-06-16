<?php

echo "=== اختبار API للمعلمين ===\n\n";

// Function to make API requests
function testApiEndpoint($url, $description, $method = 'GET', $data = null) {
    echo "🔹 $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL Error: $error\n";
        return false;
    }
    
    echo "Status Code: $httpCode\n";
    
    if ($httpCode === 200 || $httpCode === 201) {
        echo "✅ نجح!\n";
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            // Show limited data to avoid too much output
            if (isset($data['data']) && is_array($data['data'])) {
                echo "عدد العناصر: " . count($data['data']) . "\n";
                if (count($data['data']) > 0) {
                    echo "أول عنصر: " . json_encode($data['data'][0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                }
            } else {
                echo "الاستجابة: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "الاستجابة: $response\n";
        }
    } else {
        echo "❌ فشل!\n";
        echo "الاستجابة: $response\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
    return $httpCode === 200 || $httpCode === 201;
}

// Base URL
$baseUrl = 'http://localhost:8000/api';

echo "🚀 بدء اختبار API endpoints للمعلمين...\n\n";

// Test teacher endpoints
$endpoints = [
    ['GET', "$baseUrl/teachers", "جلب قائمة المعلمين"],
    ['GET', "$baseUrl/teachers/1", "جلب تفاصيل المعلم رقم 1"],
    ['GET', "$baseUrl/teachers/1/circles", "جلب حلقات المعلم رقم 1"],
    ['GET', "$baseUrl/teachers/1/students", "جلب طلاب المعلم رقم 1"],
    ['GET', "$baseUrl/teachers/1/mosques", "جلب مساجد المعلم رقم 1"],
];

foreach ($endpoints as $endpoint) {
    testApiEndpoint($endpoint[1], $endpoint[2], $endpoint[0]);
}

echo "=== انتهى الاختبار ===\n";
