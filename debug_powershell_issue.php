<?php

// Test API with detailed error reporting
$url = 'http://localhost:8000/api/recitation/sessions';

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
    'duration_minutes' => 15,
    'grade' => 8.5,
    'evaluation' => 'جيد جداً',  // with tashkeel
    'teacher_notes' => 'Good performance',
    'status' => 'مكتملة'
];

echo "=== Testing API with PowerShell data ===\n";
echo "URL: $url\n";
echo "Data: " . json_encode($testData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

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
    CURLOPT_VERBOSE => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

echo "=== Response ===\n";
echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";
echo "Response Body:\n";

if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Raw response:\n" . $response . "\n";
    }
} else {
    echo "No response received\n";
}

echo "\n=== Test Complete ===\n";
