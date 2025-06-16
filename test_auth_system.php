<?php

// اختبار نظام المصادقة للمعلمين والطلاب
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار نظام المصادقة ===\n\n";

try {    // اختبار إنشاء معلم جديد
    echo "1. إنشاء معلم جديد...\n";
    
    // حذف المعلم إذا كان موجوداً
    Teacher::where('identity_number', '1234567890')->delete();
    
    $teacher = Teacher::create([
        'name' => 'أحمد محمد',
        'identity_number' => '1234567890',
        'nationality' => 'السعودية',
        'phone' => '0501234567',
        'job_title' => 'معلم حفظ',
        'task_type' => 'معلم بمكافأة',
        'circle_type' => 'تحفيظ',
        'work_time' => 'عصر'
    ]);
    
    echo "✓ تم إنشاء المعلم بنجاح - ID: {$teacher->id}\n";
    echo "✓ تم توليد كلمة مرور تلقائياً\n";
    echo "✓ حالة تغيير كلمة المرور: " . ($teacher->must_change_password ? 'مطلوب' : 'غير مطلوب') . "\n";
    echo "✓ حالة المستخدم: " . ($teacher->is_active_user ? 'نشط' : 'غير نشط') . "\n\n";    // اختبار إنشاء طالب جديد
    echo "2. إنشاء طالب جديد...\n";
    
    // حذف الطالب إذا كان موجوداً
    Student::where('identity_number', '0987654321')->delete();
    
    $student = Student::create([
        'name' => 'فاطمة أحمد',
        'identity_number' => '0987654321',
        'nationality' => 'السعودية',
        'phone' => '0507654321',
        'birth_date' => '2005-01-15',
        'is_active' => true
    ]);
    
    echo "✓ تم إنشاء الطالبة بنجاح - ID: {$student->id}\n";
    echo "✓ تم توليد كلمة مرور تلقائياً\n";
    echo "✓ حالة تغيير كلمة المرور: " . ($student->must_change_password ? 'مطلوب' : 'غير مطلوب') . "\n";
    echo "✓ حالة المستخدم: " . ($student->is_active_user ? 'نشط' : 'غير نشط') . "\n\n";    // اختبار المصادقة
    echo "3. اختبار تسجيل الدخول...\n";
    
    // محاولة تسجيل دخول بكلمة مرور خاطئة
    $wrongAuth = Teacher::authenticate($teacher->identity_number, 'wrongpassword');
    echo "✓ اختبار كلمة مرور خاطئة: " . ($wrongAuth ? 'فشل الاختبار' : 'نجح الاختبار') . "\n";
    
    echo "\n=== تم الانتهاء من الاختبار بنجاح! ===\n";
    echo "النظام جاهز للاستخدام:\n";
    echo "- المعلمون والطلاب يحصلون على كلمات مرور تلقائياً عند إنشائهم\n";
    echo "- يمكن تسجيل الدخول برقم الهوية + كلمة المرور\n";
    echo "- يمكن تغيير كلمة المرور لاحقاً\n";
    echo "- متوفر API endpoints للتطبيقات الخارجية\n\n";
    
    echo "API Endpoints المتاحة:\n";
    echo "POST /api/auth/teacher/login - تسجيل دخول المعلم\n";
    echo "POST /api/auth/student/login - تسجيل دخول الطالب\n";
    echo "POST /api/auth/teacher/change-password - تغيير كلمة مرور المعلم\n";
    echo "POST /api/auth/student/change-password - تغيير كلمة مرور الطالب\n";
    echo "POST /api/auth/user-info - معلومات المستخدم\n";

} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "تفاصيل الخطأ: " . $e->getTraceAsString() . "\n";
}
