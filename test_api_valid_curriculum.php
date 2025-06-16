<?php

// Test API with valid curriculum IDs
$url = 'http://localhost:8000/api/recitation/sessions';

// Test data with curriculum_id = 6 (منهج "جديد")
$testData = [
    'student_id' => 1,
    'teacher_id' => 1,
    'quran_circle_id' => 1,
    'curriculum_id' => 6,  // Valid curriculum ID
    'start_surah_number' => 1,
    'start_verse' => 1,
    'end_surah_number' => 1,
    'end_verse' => 7,
    'recitation_type' => 'حفظ',
    'duration_minutes' => 30,
    'grade' => 85.50,
    'evaluation' => 'جيد جداً',
    'status' => 'مكتملة',
    'teacher_notes' => 'أداء ممتاز في التلاوة',
    'has_errors' => false,
    'total_verses' => 7
];

echo "=== Testing API with Valid Curriculum ID ===\n";
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
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => fopen('php://temp', 'w+'),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

echo "=== Response ===\n";
echo "HTTP Code: $httpCode\n";
echo "Content-Type: $contentType\n";
echo "Response Body:\n";

if ($response) {
    // Try to format JSON response
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
} else {
    echo "No response received\n";
    echo "cURL Error: " . curl_error($ch) . "\n";
}

// Show verbose cURL info
rewind(curl_getinfo($ch, CURLOPT_STDERR));
$verboseLog = stream_get_contents(curl_getinfo($ch, CURLOPT_STDERR));
if ($verboseLog) {
    echo "\n=== cURL Verbose Log ===\n";
    echo $verboseLog . "\n";
}

curl_close($ch);

echo "\n=== Test Complete ===\n";
