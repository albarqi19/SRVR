<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

echo "إصلاح كلمات المرور...\n";

$teacher = Teacher::where('identity_number', '1234567890')->first();
$student = Student::where('identity_number', '0987654321')->first();

if ($teacher) {
    $teacher->password = Hash::make('123456');
    $teacher->save();
    echo "تم تحديث كلمة مرور المعلم\n";
}

if ($student) {
    $student->password = Hash::make('654321');
    $student->save();
    echo "تم تحديث كلمة مرور الطالب\n";
}

echo "انتهى\n";
