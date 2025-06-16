<?php
echo "=== Testing Student Attendance API ===\n\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "Bootstrap successful\n";
    
    // Find student and teacher
    $student = App\Models\Student::first();
    $teacher = App\Models\Teacher::first();
    
    if (!$student || !$teacher) {
        echo "ERROR: No students or teachers found\n";
        exit;
    }
    
    echo "Student: {$student->name} (ID: {$student->id})\n";
    echo "Teacher: {$teacher->name} (ID: {$teacher->id})\n\n";
    
    // Test old API
    echo "=== Testing Old API ===\n";
    $request = Illuminate\Http\Request::create('/api/attendance/record', 'POST', [
        'student_name' => $student->name,
        'date' => '2025-06-08',
        'status' => 'present',
        'period' => 'afternoon',
        'notes' => 'API test'
    ]);
    
    $controller = new App\Http\Controllers\Api\StudentAttendanceController();
    $response = $controller->store($request);
    $data = $response->getData(true);
    
    echo "Old API result: " . ($data['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if (!$data['success']) {
        echo "Error: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Test new API
    echo "=== Testing New API ===\n";
    $requestNew = Illuminate\Http\Request::create('/api/attendance/record-batch', 'POST', [
        'teacherId' => $teacher->id,
        'date' => '2025-06-08',
        'time' => '14:30:00',
        'students' => [
            [
                'studentId' => $student->id,
                'status' => 'حاضر',
                'notes' => 'New API test'
            ]
        ]
    ]);
    
    if (method_exists($controller, 'storeBatch')) {
        $responseNew = $controller->storeBatch($requestNew);
        $dataNew = $responseNew->getData(true);
        
        echo "New API result: " . ($dataNew['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$dataNew['success']) {
            echo "Error: " . json_encode($dataNew, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Recorded attendance for {$dataNew['summary']['success']} students\n";
        }
    } else {
        echo "storeBatch method not found\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
