<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار مفصل للمصادقة ===\n\n";

// الحصول على المعلم والطالب
$teacher = Teacher::where('identity_number', '1234567890')->first();
$student = Student::where('identity_number', '0987654321')->first();

if (!$teacher || !$student) {
    echo "❌ لم يتم العثور على بيانات الاختبار\n";
    exit;
}

echo "🔍 اختبار المعلم:\n";
echo "- ID: {$teacher->id}\n";
echo "- الاسم: {$teacher->name}\n";
echo "- رقم الهوية: {$teacher->identity_number}\n";
echo "- كلمة المرور الحالية (مشفرة): " . substr($teacher->password, 0, 30) . "...\n";
echo "- نشط: " . ($teacher->is_active_user ? 'نعم' : 'لا') . "\n\n";

// تعيين كلمة مرور جديدة
$newPassword = '123456';
echo "🔧 تعيين كلمة مرور جديدة: $newPassword\n";

// طريقة 1: استخدام Hash::make مباشرة
$hashedPassword = Hash::make($newPassword);
$teacher->password = $hashedPassword;
$teacher->save();

echo "✓ تم حفظ كلمة المرور المشفرة\n";
echo "- كلمة المرور الجديدة (مشفرة): " . substr($hashedPassword, 0, 30) . "...\n\n";

// اختبار التحقق من كلمة المرور
echo "🧪 اختبار التحقق من كلمة المرور:\n";

// إعادة تحميل المعلم من قاعدة البيانات
$teacher->refresh();

echo "1. باستخدام Hash::check مباشرة:\n";
$directCheck = Hash::check($newPassword, $teacher->password);
echo "   - Hash::check('$newPassword', password_hash): " . ($directCheck ? 'نجح ✓' : 'فشل ❌') . "\n";

echo "2. باستخدام دالة checkPassword في النموذج:\n";
$modelCheck = $teacher->checkPassword($newPassword);
echo "   - \$teacher->checkPassword('$newPassword'): " . ($modelCheck ? 'نجح ✓' : 'فشل ❌') . "\n";

echo "3. باستخدام دالة authenticate:\n";
$authTeacher = Teacher::authenticate('1234567890', $newPassword);
echo "   - Teacher::authenticate('1234567890', '$newPassword'): " . ($authTeacher ? 'نجح ✓' : 'فشل ❌') . "\n";

// نفس الاختبار للطالب
echo "\n🔍 اختبار الطالب:\n";
echo "- ID: {$student->id}\n";
echo "- الاسم: {$student->name}\n";
echo "- رقم الهوية: {$student->identity_number}\n";

$studentPassword = '654321';
echo "🔧 تعيين كلمة مرور جديدة للطالب: $studentPassword\n";

$student->password = Hash::make($studentPassword);
$student->save();
$student->refresh();

echo "🧪 اختبار التحقق من كلمة المرور للطالب:\n";
$studentDirectCheck = Hash::check($studentPassword, $student->password);
echo "1. Hash::check مباشرة: " . ($studentDirectCheck ? 'نجح ✓' : 'فشل ❌') . "\n";

$studentModelCheck = $student->checkPassword($studentPassword);
echo "2. دالة النموذج: " . ($studentModelCheck ? 'نجح ✓' : 'فشل ❌') . "\n";

$authStudent = Student::authenticate('0987654321', $studentPassword);
echo "3. دالة authenticate: " . ($authStudent ? 'نجح ✓' : 'فشل ❌') . "\n";

echo "\n=== انتهى الاختبار ===\n";
