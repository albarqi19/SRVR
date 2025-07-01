<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحديد Laravel بشكل صحيح
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\Api\QuranSchoolStudentController;
use Illuminate\Http\Request;

echo "🧪 اختبار مباشر لـ Controller المدرسة القرآنية\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // إنشاء instance من الـ Controller
    $controller = new QuranSchoolStudentController();
    
    echo "1️⃣ اختبار دالة getQuranSchoolInfo...\n";
    
    // إنشاء request فارغ
    app()->instance('request', Request::create('/api/quran-schools/1/info', 'GET'));
    
    // استدعاء الدالة
    $response = $controller->getQuranSchoolInfo(1);
    
    echo "✅ تم استدعاء الدالة بنجاح\n";
    echo "HTTP Status: " . $response->getStatusCode() . "\n";
    
    $content = json_decode($response->getContent(), true);
    
    if ($content && isset($content['success'])) {
        if ($content['success']) {
            echo "✅ الاستجابة ناجحة\n";
            echo "اسم المدرسة: " . ($content['data']['quran_school']['name'] ?? 'غير محدد') . "\n";
        } else {
            echo "❌ فشل API: " . $content['message'] . "\n";
            if (isset($content['error'])) {
                echo "تفاصيل الخطأ: " . $content['error'] . "\n";
            }
        }
    } else {
        echo "❌ استجابة غير صحيحة\n";
        echo "المحتوى: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage() . "\n";
    echo "الملف: " . $e->getFile() . " - السطر: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
