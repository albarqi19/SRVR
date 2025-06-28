<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🧪 اختبار اتصال WhatsApp API...\n";
    
    $url = 'http://localhost:3000/api/webhook/N4rqjrZBt7Pf5Rqh0yHAh6Oo3Ne0qkGQ';
    echo "📡 الرابط: {$url}\n";
    
    $data = [
        'to' => '966501234567',
        'message' => 'اختبار من Laravel - ' . date('Y-m-d H:i:s'),
        'type' => 'text'
    ];
    
    echo "📤 البيانات المرسلة: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    $response = Http::timeout(30)->post($url, $data);
    
    echo "📨 رمز الاستجابة: " . $response->status() . "\n";
    echo "📋 الاستجابة: " . $response->body() . "\n";
    
    if ($response->successful()) {
        echo "✅ نجح الاختبار!\n";
    } else {
        echo "❌ فشل الاختبار\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 النوع: " . get_class($e) . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
