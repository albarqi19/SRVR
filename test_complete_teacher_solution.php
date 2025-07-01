<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 اختبار شامل لحل مشكلة تسجيل المعلمين\n";
echo str_repeat("=", 70) . "\n\n";

// 1. اختبار بنية قاعدة البيانات
echo "1️⃣ فحص بنية قاعدة البيانات:\n";
echo "   📋 أعمدة جدول teachers:\n";
$teacherColumns = DB::select('DESCRIBE teachers');
foreach($teacherColumns as $column) {
    $indicator = $column->Field === 'user_id' ? '✅' : '  ';
    echo "   {$indicator} {$column->Field} ({$column->Type})\n";
}

$hasUserId = collect($teacherColumns)->pluck('Field')->contains('user_id');
echo "\n   " . ($hasUserId ? '✅' : '❌') . " عمود user_id موجود: " . ($hasUserId ? 'نعم' : 'لا') . "\n\n";

// 2. إحصائيات المعلمين والمستخدمين
echo "2️⃣ إحصائيات:\n";
$totalTeachers = Teacher::count();
$totalUsers = User::count();

echo "   📊 إجمالي المعلمين: {$totalTeachers}\n";
echo "   👤 إجمالي المستخدمين: {$totalUsers}\n";

if ($hasUserId) {
    $teachersWithUserId = Teacher::whereNotNull('user_id')->count();
    $teachersWithoutUserId = $totalTeachers - $teachersWithUserId;
    
    echo "   ✅ معلمين مرتبطين بمستخدمين: {$teachersWithUserId}\n";
    echo "   ❌ معلمين غير مرتبطين: {$teachersWithoutUserId}\n";
} else {
    echo "   ⚠️ لا يمكن فحص الارتباط - عمود user_id غير موجود\n";
}

echo "\n";

// 3. فحص ValidTeacherId rule
echo "3️⃣ اختبار ValidTeacherId rule:\n";
try {
    $rule = new App\Rules\ValidTeacherId();
    
    // اختبار معلم موجود
    $testTeacherId = Teacher::first()->id ?? 1;
    $passes = $rule->passes('teacher_id', $testTeacherId);
    echo "   🧪 اختبار teacher_id = {$testTeacherId}: " . ($passes ? '✅ نجح' : '❌ فشل') . "\n";
    if (!$passes) {
        echo "      رسالة الخطأ: " . $rule->message() . "\n";
    }
    
    // اختبار user_id صحيح
    $testUserId = User::first()->id ?? 1;
    $rule2 = new App\Rules\ValidTeacherId();
    $passes2 = $rule2->passes('teacher_id', $testUserId);
    echo "   🧪 اختبار user_id = {$testUserId}: " . ($passes2 ? '✅ نجح' : '❌ فشل') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ خطأ في اختبار ValidTeacherId: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. اختبار Observer
echo "4️⃣ اختبار TeacherObserver:\n";
try {
    // فحص إذا كان Observer مُسجل
    echo "   📝 TeacherObserver: " . (class_exists('App\Observers\TeacherObserver') ? '✅ موجود' : '❌ غير موجود') . "\n";
} catch (Exception $e) {
    echo "   ❌ خطأ في فحص Observer: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. قائمة المعلمين الذين يحتاجون إصلاح
echo "5️⃣ المعلمين الذين يحتاجون إصلاح:\n";
try {
    $teachersNeedingFix = Teacher::leftJoin('users', function($join) {
        $join->on('teachers.identity_number', '=', 'users.identity_number')
             ->orWhere(function($query) {
                 $query->whereRaw("users.email LIKE CONCAT('teacher_', teachers.id, '@garb.com')");
             });
    })
    ->whereNull('users.id')
    ->select('teachers.id', 'teachers.name', 'teachers.identity_number')
    ->get();
    
    if ($teachersNeedingFix->count() > 0) {
        echo "   ⚠️ يحتاج إصلاح: {$teachersNeedingFix->count()} معلم\n";
        foreach($teachersNeedingFix as $teacher) {
            echo "      - ID: {$teacher->id}, الاسم: {$teacher->name}\n";
        }
        
        echo "\n   💡 لإصلاح هذه المشكلة، شغل الأمر:\n";
        echo "      php artisan fix:all-teachers-users\n";
    } else {
        echo "   ✅ جميع المعلمين لديهم حسابات مستخدمين\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ خطأ في فحص المعلمين: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. توصيات الحل
echo "6️⃣ خطوات الحل النهائي:\n";
echo "   1. تشغيل migration لإضافة عمود user_id:\n";
echo "      php artisan migrate\n\n";
echo "   2. إصلاح المعلمين الموجودين:\n";
echo "      php artisan fix:all-teachers-users\n\n";
echo "   3. اختبار إنشاء معلم جديد للتأكد من التكامل التلقائي\n\n";

echo str_repeat("=", 70) . "\n";
echo "انتهى الاختبار ✅\n";
