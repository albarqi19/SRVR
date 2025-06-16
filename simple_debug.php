<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== فحص بسيط للمصادقة ===\n\n";

try {
    // الحصول على المعلم
    $teacher = Teacher::where('identity_number', '1234567890')->first();
    
    if (!$teacher) {
        echo "❌ لم يتم العثور على المعلم\n";
        exit;
    }
    
    echo "📋 معلومات المعلم:\n";
    echo "- الاسم: {$teacher->name}\n";
    echo "- رقم الهوية: {$teacher->identity_number}\n";
    echo "- حالة النشاط: " . ($teacher->is_active_user ? 'نشط' : 'غير نشط') . "\n";
    echo "- كلمة المرور موجودة: " . (!empty($teacher->password) ? 'نعم' : 'لا') . "\n";
    
    if (!empty($teacher->password)) {
        echo "- طول كلمة المرور: " . strlen($teacher->password) . " حرف\n";
        echo "- بداية كلمة المرور: " . substr($teacher->password, 0, 10) . "...\n";
    }
    
    // اختبار كلمة مرور جديدة
    $testPassword = '123456';
    echo "\n🔧 تحديث كلمة المرور إلى: $testPassword\n";
    
    // استخدام Update مباشرة
    $hashedPassword = Hash::make($testPassword);
    Teacher::where('id', $teacher->id)->update(['password' => $hashedPassword]);
    
    // إعادة تحميل البيانات
    $teacher = Teacher::find($teacher->id);
    
    echo "✓ تم تحديث كلمة المرور\n";
    echo "- كلمة المرور الجديدة: " . substr($teacher->password, 0, 10) . "...\n";
    
    // اختبار التحقق
    echo "\n🧪 اختبار التحقق:\n";
    $checkResult = Hash::check($testPassword, $teacher->password);
    echo "- Hash::check: " . ($checkResult ? 'نجح ✓' : 'فشل ❌') . "\n";
    
    $modelResult = $teacher->checkPassword($testPassword);
    echo "- checkPassword: " . ($modelResult ? 'نجح ✓' : 'فشل ❌') . "\n";
    
    echo "\n✅ انتهى الفحص\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
