<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 فحص بيانات المعلمين والمستخدمين\n";
echo "=" . str_repeat("=", 50) . "\n\n";

echo "📊 الإحصائيات:\n";
echo "   - إجمالي المعلمين في جدول teachers: " . Teacher::count() . "\n";
echo "   - إجمالي المستخدمين في جدول users: " . User::count() . "\n\n";

echo "👨‍🏫 قائمة المعلمين:\n";
$teachers = Teacher::select('id', 'name', 'identity_number')->get();
foreach($teachers as $teacher) {
    echo "   - ID: {$teacher->id}, الاسم: {$teacher->name}, رقم الهوية: {$teacher->identity_number}\n";
}

echo "\n👤 قائمة المستخدمين:\n";
$users = User::select('id', 'name', 'username', 'identity_number')->get();
foreach($users as $user) {
    echo "   - ID: {$user->id}, الاسم: {$user->name}, اسم المستخدم: {$user->username}, رقم الهوية: {$user->identity_number}\n";
}

echo "\n🔍 المعلمين الذين لديهم حسابات مستخدمين:\n";
$teachers = Teacher::all();
foreach($teachers as $teacher) {
    $user = User::where('identity_number', $teacher->identity_number)->first();
    if($user) {
        echo "   ✅ {$teacher->name} - له حساب مستخدم (User ID: {$user->id})\n";
    } else {
        echo "   ❌ {$teacher->name} - ليس له حساب مستخدم\n";
    }
}
