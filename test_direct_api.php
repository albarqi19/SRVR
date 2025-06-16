<?php
require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// محاكاة طلب API
use Illuminate\Http\Request;

echo "=== اختبار API مباشرة ===\n";

try {
    $request = Request::create('/api/teachers/1/circles', 'GET');
    $request->headers->set('Accept', 'application/json');
    
    $response = $kernel->handle($request);
    
    echo "رمز الاستجابة: " . $response->getStatusCode() . "\n";
    echo "محتوى الاستجابة: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "في الملف: " . $e->getFile() . "\n";
    echo "في السطر: " . $e->getLine() . "\n";
}
