<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== فحص بيانات المستخدمين في قاعدة البيانات ===\n\n";

// فحص المعلمين
echo "المعلمين:\n";
$teachers = Teacher::select('id', 'name', 'identity_number', 'password')->get();
foreach ($teachers as $teacher) {
    echo "- ID: {$teacher->id}, Name: {$teacher->name}, Identity: {$teacher->identity_number}\n";
    echo "  Password Hash: " . substr($teacher->password, 0, 30) . "...\n";
    echo "  Hash Length: " . strlen($teacher->password) . "\n\n";
}

// فحص الطلاب
echo "الطلاب:\n";
$students = Student::select('id', 'name', 'identity_number', 'password')->get();
foreach ($students as $student) {
    echo "- ID: {$student->id}, Name: {$student->name}, Identity: {$student->identity_number}\n";
    echo "  Password Hash: " . substr($student->password, 0, 30) . "...\n";
    echo "  Hash Length: " . strlen($student->password) . "\n\n";
}

// اختبار تشفير كلمة مرور
echo "=== اختبار تشفير كلمة المرور ===\n";
$testPassword = '123456';
$hashedPassword = \Illuminate\Support\Facades\Hash::make($testPassword);
echo "Password: $testPassword\n";
echo "Hashed: $hashedPassword\n";
echo "Hash Length: " . strlen($hashedPassword) . "\n";

// اختبار التحقق من كلمة المرور
echo "\n=== اختبار التحقق من كلمة المرور ===\n";
$isValid = \Illuminate\Support\Facades\Hash::check($testPassword, $hashedPassword);
echo "Hash::check('$testPassword', '$hashedPassword'): " . ($isValid ? 'صحيح' : 'خطأ') . "\n";
