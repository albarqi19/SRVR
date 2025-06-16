<?php

echo "=== Simple API Test ===" . PHP_EOL;

$url = 'http://localhost:8000/api/recitation/sessions';
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
    'teacher_notes' => 'Test',
    'status' => 'جارية'
];

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
echo "JSON to send: " . $json . PHP_EOL;

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $json,
    ],
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Error: Failed to make request" . PHP_EOL;
} else {
    echo "Response: " . $result . PHP_EOL;
}
