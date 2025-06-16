<?php

echo "=== Testing RecitationSession API with CURL ===" . PHP_EOL;

// Prepare the data
$data = [
    'student_id' => 1,
    'teacher_id' => 1,
    'quran_circle_id' => 1,
    'start_surah_number' => 1,
    'start_verse' => 1,
    'end_surah_number' => 1,
    'end_verse' => 5,
    'recitation_type' => 'حفظ',
    'duration_minutes' => 30,
    'grade' => 8.5,
    'evaluation' => 'ممتاز',
    'teacher_notes' => 'Test session from PHP',
    'status' => 'جارية'
];

echo "Data to be sent:" . PHP_EOL;
print_r($data);

$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
echo "JSON Data:" . PHP_EOL;
echo $jsonData . PHP_EOL . PHP_EOL;

// Initialize cURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/recitation/sessions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json; charset=utf-8',
        'Accept: application/json',
        'Content-Length: ' . strlen($jsonData)
    ],
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => fopen('php://stdout', 'w')
]);

echo "Making API request..." . PHP_EOL;
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo PHP_EOL . "=== Response ===" . PHP_EOL;
echo "HTTP Code: " . $httpCode . PHP_EOL;

if ($error) {
    echo "CURL Error: " . $error . PHP_EOL;
} else {
    echo "Response Body:" . PHP_EOL;
    echo $response . PHP_EOL;
    
    // Try to decode JSON response
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo PHP_EOL . "Decoded Response:" . PHP_EOL;
        print_r($responseData);
    }
}
