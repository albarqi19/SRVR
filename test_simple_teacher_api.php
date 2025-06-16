<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    echo "Testing simple Teacher API call...\n";
    
    // تجربة بسيطة للتحقق من عمل TeacherController
    $controller = new App\Http\Controllers\Api\TeacherController();
    
    echo "TeacherController instantiated successfully!\n";
    
    // محاولة إنشاء request بسيط
    $request = new Illuminate\Http\Request();
    
    echo "About to call index method...\n";
    
    // استدعاء index method
    $response = $controller->index($request);
    
    echo "Response received:\n";
    echo $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
