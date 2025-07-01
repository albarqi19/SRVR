<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 فحص بنية جداول المعلمين والمستخدمين\n";
echo str_repeat("=", 60) . "\n\n";

// فحص أعمدة جدول teachers
echo "📋 أعمدة جدول teachers:\n";
try {
    $teacherColumns = Schema::getColumnListing('teachers');
    foreach($teacherColumns as $column) {
        echo "   - $column\n";
    }
} catch(Exception $e) {
    echo "   خطأ: " . $e->getMessage() . "\n";
}

echo "\n📋 أعمدة جدول users:\n";
try {
    $userColumns = Schema::getColumnListing('users');
    foreach($userColumns as $column) {
        echo "   - $column\n";
    }
} catch(Exception $e) {
    echo "   خطأ: " . $e->getMessage() . "\n";
}

// فحص وجود user_id في جدول teachers
echo "\n🔍 فحص وجود user_id في جدول teachers:\n";
if (in_array('user_id', $teacherColumns ?? [])) {
    echo "   ✅ يوجد عمود user_id في جدول teachers\n";
    
    // فحص المعلمين الذين لديهم user_id
    $teachersWithUser = DB::table('teachers')
        ->whereNotNull('user_id')
        ->where('user_id', '>', 0)
        ->count();
    
    $totalTeachers = DB::table('teachers')->count();
    
    echo "   📊 إحصائيات:\n";
    echo "     - إجمالي المعلمين: $totalTeachers\n";
    echo "     - المعلمين الذين لديهم user_id: $teachersWithUser\n";
    echo "     - المعلمين بدون user_id: " . ($totalTeachers - $teachersWithUser) . "\n";
    
} else {
    echo "   ❌ لا يوجد عمود user_id في جدول teachers\n";
}

// فحص المعلمين الذين ليس لديهم حساب مستخدم
echo "\n🔍 المعلمين الذين ليس لديهم حساب مستخدم:\n";
$teachersWithoutUser = DB::table('teachers')
    ->leftJoin('users', function($join) {
        $join->on('teachers.identity_number', '=', 'users.identity_number')
             ->orWhere(function($query) {
                 $query->whereRaw("users.email LIKE CONCAT('teacher_', teachers.id, '@garb.com')");
             });
    })
    ->whereNull('users.id')
    ->select('teachers.id', 'teachers.name', 'teachers.identity_number', 'teachers.phone')
    ->get();

if ($teachersWithoutUser->count() > 0) {
    echo "   عدد المعلمين بدون حساب مستخدم: " . $teachersWithoutUser->count() . "\n";
    foreach($teachersWithoutUser->take(10) as $teacher) {
        echo "   - ID: {$teacher->id}, الاسم: {$teacher->name}, رقم الهوية: {$teacher->identity_number}\n";
    }
    if ($teachersWithoutUser->count() > 10) {
        echo "   ... و " . ($teachersWithoutUser->count() - 10) . " معلم آخر\n";
    }
} else {
    echo "   ✅ جميع المعلمين لديهم حسابات مستخدمين\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "انتهى الفحص\n";
