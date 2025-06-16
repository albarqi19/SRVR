<?php

// Test using localhost instead of 127.0.0.1
$url = 'http://127.0.0.1:8000/api/recitation/sessions';

$testData = [
    'student_id' => 1,
    'teacher_id' => 1,
    'quran_circle_id' => 1,
    'curriculum_id' => 6,
    'start_surah_number' => 1,
    'start_verse' => 1,
    'end_surah_number' => 1,
    'end_verse' => 7,
    'recitation_type' => 'حفظ',
    'duration_minutes' => 30,
    'grade' => 8.5,
    'evaluation' => 'جيد جدا',
    'status' => 'مكتملة',
    'teacher_notes' => 'test updated API',
    'has_errors' => false,
    'total_verses' => 7
];

echo "=== API Test Starting ===\n";
echo "URL: $url\n";
echo "Data: " . json_encode($testData, JSON_UNESCAPED_UNICODE) . "\n\n";

// Test server connectivity first
echo "Testing server connectivity...\n";
$testCh = curl_init();
curl_setopt_array($testCh, [
    CURLOPT_URL => 'http://127.0.0.1:8000',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_CONNECTTIMEOUT => 5
]);

$testResponse = curl_exec($testCh);
$testHttpCode = curl_getinfo($testCh, CURLINFO_HTTP_CODE);
curl_close($testCh);

echo "Server test - HTTP Code: $testHttpCode\n";
if ($testHttpCode === 200) {
    echo "✅ Server is responding\n\n";
} else {
    echo "❌ Server not responding properly\n";
    echo "Test response: " . substr($testResponse, 0, 200) . "...\n\n";
}

// Now test the API
echo "Testing API endpoint...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_VERBOSE => false,
    CURLOPT_FOLLOWLOCATION => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlError = curl_error($ch);

curl_close($ch);

echo "=== API Response ===\n";
echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
}

echo "Response Body:\n";
if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Raw response (not valid JSON):\n" . $response . "\n";
    }
} else {
    echo "No response received\n";
}

echo "\n=== Test Complete ===\n";
