<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use App\Models\Teacher;
use App\Models\Student;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار توليد كلمات المرور التلقائية ===\n\n";

try {
    // اختبار 1: إنشاء معلم بدون كلمة مرور
    echo "1. إنشاء معلم جديد بدون كلمة مرور:\n";
    
    $teacher = new Teacher();
    $teacher->name = 'أحمد محمد التجريبي';
    $teacher->identity_number = '1111111111';
    $teacher->nationality = 'سعودي';
    $teacher->phone = '0501111111';
    $teacher->job_title = 'معلم حفظ';
    $teacher->task_type = 'معلم بمكافأة';
    $teacher->is_active_user = true;
    
    // لا نضع كلمة مرور، يجب أن يتم توليدها تلقائياً في صفحة الإنشاء
    $teacher->save();
    
    echo "   - تم إنشاء المعلم بنجاح\n";
    echo "   - ID: " . $teacher->id . "\n";
    echo "   - كلمة المرور الأصلية: " . ($teacher->plain_password ?? 'غير موجودة') . "\n";
    echo "   - كلمة المرور المشفرة: " . ($teacher->password ? 'موجودة' : 'غير موجودة') . "\n\n";
    
    // اختبار 2: إنشاء معلم مع كلمة مرور مخصصة
    echo "2. إنشاء معلم مع كلمة مرور مخصصة:\n";
    
    $teacher2 = new Teacher();
    $teacher2->name = 'محمد أحمد التجريبي';
    $teacher2->identity_number = '2222222222';
    $teacher2->nationality = 'سعودي';
    $teacher2->phone = '0502222222';
    $teacher2->job_title = 'معلم تلقين';
    $teacher2->task_type = 'معلم محتسب';
    $teacher2->is_active_user = true;
    $teacher2->password = '123456'; // كلمة مرور مخصصة
    
    $teacher2->save();
    
    echo "   - تم إنشاء المعلم بنجاح\n";
    echo "   - ID: " . $teacher2->id . "\n";
    echo "   - كلمة المرور الأصلية: " . ($teacher2->plain_password ?? 'غير موجودة') . "\n";
    echo "   - كلمة المرور المشفرة: " . ($teacher2->password ? 'موجودة' : 'غير موجودة') . "\n\n";
    
    // اختبار 3: توليد كلمة مرور عشوائية
    echo "3. اختبار توليد كلمة مرور عشوائية:\n";
    
    for ($i = 1; $i <= 5; $i++) {
        $randomPassword = Teacher::generateRandomPassword();
        echo "   - كلمة مرور {$i}: {$randomPassword}\n";
    }
    echo "\n";
    
    // اختبار 4: إنشاء طالب مع كلمة مرور
    echo "4. إنشاء طالب مع كلمة مرور:\n";
    
    $student = new Student();
    $student->name = 'عبدالله سعد التجريبي';
    $student->identity_number = '3333333333';
    $student->nationality = 'سعودي';
    $student->phone = '0503333333';
    $student->is_active_user = true;
    $student->password = '654321'; // كلمة مرور مخصصة
    
    $student->save();
    
    echo "   - تم إنشاء الطالب بنجاح\n";
    echo "   - ID: " . $student->id . "\n";
    echo "   - كلمة المرور الأصلية: " . ($student->plain_password ?? 'غير موجودة') . "\n";
    echo "   - كلمة المرور المشفرة: " . ($student->password ? 'موجودة' : 'غير موجودة') . "\n\n";
    
    // اختبار 5: التحقق من المصادقة
    echo "5. اختبار المصادقة:\n";
    
    // تجريب تسجيل دخول المعلم
    $authTeacher = Teacher::authenticate('2222222222', '123456');
    echo "   - تسجيل دخول المعلم: " . ($authTeacher ? 'نجح' : 'فشل') . "\n";
    
    // تجريب تسجيل دخول الطالب
    $authStudent = Student::authenticate('3333333333', '654321');
    echo "   - تسجيل دخول الطالب: " . ($authStudent ? 'نجح' : 'فشل') . "\n\n";
    
    echo "=== انتهى الاختبار بنجاح ===\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "في الملف: " . $e->getFile() . "\n";
    echo "في السطر: " . $e->getLine() . "\n";
}
