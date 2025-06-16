<?php

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Load Laravel configuration
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set up database connection
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== فحص بنية المساجد والحلقات القرآنية ===\n\n";

// 1. عرض جميع المساجد
echo "1. المساجد:\n";
$mosques = DB::table('mosques')->select('id', 'name', 'parent_id')->get();
foreach ($mosques as $mosque) {
    echo "   - ID: {$mosque->id}, الاسم: {$mosque->name}, المسجد الأب: " . ($mosque->parent_id ?? 'لا يوجد') . "\n";
}

echo "\n2. الحلقات القرآنية:\n";
$circles = DB::table('quran_circles')->select('id', 'name', 'mosque_id')->get();
foreach ($circles as $circle) {
    echo "   - ID: {$circle->id}, الاسم: {$circle->name}, معرف المسجد: {$circle->mosque_id}\n";
}

echo "\n3. المعلمين:\n";
$teachers = DB::table('teachers')->select('id', 'name', 'mosque_id', 'quran_circle_id')->get();
foreach ($teachers as $teacher) {
    echo "   - ID: {$teacher->id}, الاسم: {$teacher->name}, معرف المسجد: " . ($teacher->mosque_id ?? 'لا يوجد') . ", معرف الحلقة: " . ($teacher->quran_circle_id ?? 'لا يوجد') . "\n";
}

echo "\n4. الطلاب:\n";
$students = DB::table('students')->select('id', 'name', 'mosque_id', 'quran_circle_id')->get();
foreach ($students as $student) {
    echo "   - ID: {$student->id}, الاسم: {$student->name}, معرف المسجد: " . ($student->mosque_id ?? 'لا يوجد') . ", معرف الحلقة: " . ($student->quran_circle_id ?? 'لا يوجد') . "\n";
}

echo "\n5. تحليل العلاقات:\n";

// تحقق من المعلم 1
$teacher1 = DB::table('teachers')->where('id', 1)->first();
if ($teacher1) {
    echo "المعلم 1: {$teacher1->name}\n";
    echo "   - مسجد: {$teacher1->mosque_id}\n";
    echo "   - حلقة قرآنية: {$teacher1->quran_circle_id}\n";
    
    // العثور على الطلاب في نفس الحلقة
    $studentsInCircle = DB::table('students')->where('quran_circle_id', $teacher1->quran_circle_id)->count();
    echo "   - عدد الطلاب في نفس الحلقة: {$studentsInCircle}\n";
    
    // العثور على الطلاب في نفس المسجد
    $studentsInMosque = DB::table('students')->where('mosque_id', $teacher1->mosque_id)->count();
    echo "   - عدد الطلاب في نفس المسجد: {$studentsInMosque}\n";
}

echo "\n=== انتهاء الفحص ===\n";
