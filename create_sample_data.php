<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\Mosque;

try {
    echo "إنشاء بيانات تجريبية...\n";

    // إنشاء مسجد تجريبي
    $mosque = Mosque::firstOrCreate([
        'name' => 'مسجد النور',
        'address' => 'الرياض، السعودية',
        'phone' => '0112345678'
    ]);
    echo "✅ تم إنشاء المسجد: {$mosque->name} (ID: {$mosque->id})\n";

    // إنشاء معلم تجريبي
    $teacher = User::firstOrCreate([
        'email' => 'teacher@test.com'
    ], [
        'name' => 'المعلم أحمد',
        'role' => 'teacher',
        'password' => bcrypt('password'),
        'mosque_id' => $mosque->id
    ]);
    echo "✅ تم إنشاء المعلم: {$teacher->name} (ID: {$teacher->id})\n";

    // إنشاء حلقة قرآن تجريبية
    $circle = QuranCircle::firstOrCreate([
        'name' => 'حلقة الحفظ المتقدمة',
        'mosque_id' => $mosque->id,
        'teacher_id' => $teacher->id
    ], [
        'description' => 'حلقة لحفظ القرآن الكريم',
        'capacity' => 20,
        'current_students' => 0
    ]);
    echo "✅ تم إنشاء الحلقة: {$circle->name} (ID: {$circle->id})\n";

    // إنشاء طالب تجريبي
    $student = Student::firstOrCreate([
        'national_id' => '1234567890'
    ], [
        'name' => 'الطالب محمد',
        'phone' => '0501234567',
        'guardian_phone' => '0501234568',
        'address' => 'الرياض',
        'birth_date' => '2010-01-01',
        'enrollment_date' => now(),
        'quran_circle_id' => $circle->id,
        'mosque_id' => $mosque->id
    ]);
    echo "✅ تم إنشاء الطالب: {$student->name} (ID: {$student->id})\n";

    echo "\n📋 البيانات الجاهزة للاستخدام:\n";
    echo "student_id: {$student->id}\n";
    echo "teacher_id: {$teacher->id}\n";
    echo "quran_circle_id: {$circle->id}\n";
    echo "mosque_id: {$mosque->id}\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
