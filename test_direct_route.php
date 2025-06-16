<?php

// Test the temporary direct route that bypasses validation
$url = 'http://localhost:8000/api/recitation/sessions/direct';

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
    'teacher_notes' => 'اختبار الطريق المباشر',
    'has_errors' => false,
    'total_verses' => 7
];

echo "=== Testing Direct Route ===\n";
echo "URL: $url\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Response: ";

if ($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
} else {
    echo "No response - Error: " . curl_error($ch) . "\n";
}

curl_close($ch);
