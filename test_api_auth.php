<?php

// اختبار API endpoints للمصادقة
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار API endpoints للمصادقة ===\n\n";

function callAPI($endpoint, $data) {
    $baseUrl = 'http://localhost:8000'; // تأكد من أن السيرفر يعمل على هذا المنفذ
    $url = $baseUrl . $endpoint;
    
    $postData = json_encode($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

try {
    // التأكد من وجود مستخدمين للاختبار
    $teacher = Teacher::where('identity_number', '1234567890')->first();
    $student = Student::where('identity_number', '0987654321')->first();
    
    if (!$teacher || !$student) {
        echo "❌ لا توجد بيانات اختبار. يرجى تشغيل test_auth_system.php أولاً\n";
        exit;
    }
    
    echo "📋 المستخدمين المتاحين للاختبار:\n";
    echo "   - معلم: {$teacher->name} (رقم الهوية: {$teacher->identity_number})\n";
    echo "   - طالب: {$student->name} (رقم الهوية: {$student->identity_number})\n\n";
    
    // الحصول على كلمة المرور للمعلم (نعرف أنها موجودة لكن مشفرة)
    echo "🔍 اختبار تسجيل دخول المعلم...\n";
    
    // نحتاج لمعرفة كلمة المرور المولدة
    echo "   - نظراً لأن كلمات المرور مشفرة، سنولد كلمة مرور جديدة للاختبار...\n";
      // تغيير كلمة مرور المعلم لكلمة مرور معروفة
    $teacherPassword = '123456';
    $teacher->password = $teacherPassword; // لا نحتاج Hash::make لأن setPasswordAttribute تتولى التشفير
    $teacher->save();
    
    // تغيير كلمة مرور الطالب لكلمة مرور معروفة
    $studentPassword = '654321';
    $student->password = $studentPassword; // لا نحتاج Hash::make لأن setPasswordAttribute تتولى التشفير
    $student->save();
    
    echo "   ✓ تم تعيين كلمة مرور الاختبار\n\n";
    
    // اختبار تسجيل دخول المعلم
    echo "1. اختبار تسجيل دخول المعلم:\n";
    $result = callAPI('/api/auth/teacher/login', [
        'identity_number' => $teacher->identity_number,
        'password' => $teacherPassword
    ]);
    
    echo "   - الحالة: " . $result['status_code'] . "\n";
    echo "   - الاستجابة: " . ($result['response']['success'] ? '✓ نجح' : '❌ فشل') . "\n";
    if (!$result['response']['success']) {
        echo "   - الرسالة: " . $result['response']['message'] . "\n";
    }
    echo "\n";
    
    // اختبار تسجيل دخول الطالب
    echo "2. اختبار تسجيل دخول الطالب:\n";
    $result = callAPI('/api/auth/student/login', [
        'identity_number' => $student->identity_number,
        'password' => $studentPassword
    ]);
    
    echo "   - الحالة: " . $result['status_code'] . "\n";
    echo "   - الاستجابة: " . ($result['response']['success'] ? '✓ نجح' : '❌ فشل') . "\n";
    if (!$result['response']['success']) {
        echo "   - الرسالة: " . $result['response']['message'] . "\n";
    }
    echo "\n";
    
    // اختبار تغيير كلمة مرور المعلم
    echo "3. اختبار تغيير كلمة مرور المعلم:\n";
    $newPassword = '789012';
    $result = callAPI('/api/auth/teacher/change-password', [
        'identity_number' => $teacher->identity_number,
        'current_password' => $teacherPassword,
        'new_password' => $newPassword,
        'new_password_confirmation' => $newPassword
    ]);
    
    echo "   - الحالة: " . $result['status_code'] . "\n";
    echo "   - الاستجابة: " . ($result['response']['success'] ? '✓ نجح' : '❌ فشل') . "\n";
    if (!$result['response']['success']) {
        echo "   - الرسالة: " . $result['response']['message'] . "\n";
    }
    echo "\n";
    
    // اختبار معلومات المستخدم
    echo "4. اختبار الحصول على معلومات المعلم:\n";
    $result = callAPI('/api/auth/user-info', [
        'user_type' => 'teacher',
        'identity_number' => $teacher->identity_number
    ]);
    
    echo "   - الحالة: " . $result['status_code'] . "\n";
    echo "   - الاستجابة: " . ($result['response']['success'] ? '✓ نجح' : '❌ فشل') . "\n";
    if ($result['response']['success']) {
        echo "   - الاسم: " . $result['response']['data']['name'] . "\n";
        echo "   - يجب تغيير كلمة المرور: " . ($result['response']['data']['must_change_password'] ? 'نعم' : 'لا') . "\n";
    }
    echo "\n";
    
    echo "=== تم انتهاء اختبار API ===\n";
    echo "ملاحظة: تأكد من تشغيل السيرفر باستخدام: php artisan serve\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'cURL') !== false) {
        echo "تأكد من تشغيل السيرفر باستخدام: php artisan serve\n";
    }
}
