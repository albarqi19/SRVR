<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 اختبار إنشاء معلم جديد مع حساب مستخدم تلقائي\n";
echo str_repeat("=", 70) . "\n\n";

// عد المعلمين والمستخدمين قبل الإنشاء
$teachersBefore = Teacher::count();
$usersBefore = User::count();

echo "📊 الإحصائيات قبل الإنشاء:\n";
echo "   - المعلمين: $teachersBefore\n";
echo "   - المستخدمين: $usersBefore\n\n";

try {
    // إنشاء معلم جديد
    $randomId = rand(1000000000, 9999999999);
    $teacher = Teacher::create([
        'name' => 'معلم تجريبي للاختبار',
        'identity_number' => $randomId,
        'phone' => '0501234567',
        'nationality' => 'سعودي',
        'mosque_id' => 1, // تأكد من وجود المسجد
        'job_title' => 'معلم حفظ',
        'task_type' => 'معلم بمكافأة',
        'circle_type' => 'حلقة فردية',
        'work_time' => 'عصر'
    ]);

    echo "✅ تم إنشاء المعلم بنجاح!\n";
    echo "   - ID المعلم: {$teacher->id}\n";
    echo "   - اسم المعلم: {$teacher->name}\n";
    echo "   - user_id: " . ($teacher->user_id ?? 'غير محدد') . "\n\n";

    // فحص إنشاء حساب المستخدم
    if ($teacher->user_id) {
        $user = User::find($teacher->user_id);
        if ($user) {
            echo "✅ تم إنشاء حساب مستخدم تلقائياً!\n";
            echo "   - ID المستخدم: {$user->id}\n";
            echo "   - اسم المستخدم: {$user->name}\n";
            echo "   - البريد الإلكتروني: {$user->email}\n";
            echo "   - اسم المستخدم: {$user->username}\n\n";
        } else {
            echo "❌ user_id موجود لكن المستخدم غير موجود!\n";
        }
    } else {
        echo "❌ لم يتم إنشاء حساب مستخدم تلقائياً!\n";
        echo "🔧 سيتم إنشاؤه يدوياً...\n";
        
        // إنشاء حساب مستخدم يدوياً
        $user = User::create([
            'name' => $teacher->name,
            'email' => "teacher_{$teacher->id}@garb.com",
            'username' => "teacher_{$teacher->id}",
            'password' => bcrypt('123456'),
            'identity_number' => $teacher->identity_number,
            'phone' => $teacher->phone,
            'role' => 'teacher'
        ]);
        
        // ربط المعلم بالمستخدم
        $teacher->update(['user_id' => $user->id]);
        
        echo "✅ تم إنشاء الحساب يدوياً وربطه!\n";
        echo "   - ID المستخدم: {$user->id}\n";
    }

    // عد المعلمين والمستخدمين بعد الإنشاء
    $teachersAfter = Teacher::count();
    $usersAfter = User::count();

    echo "📊 الإحصائيات بعد الإنشاء:\n";
    echo "   - المعلمين: $teachersAfter (زيادة: " . ($teachersAfter - $teachersBefore) . ")\n";
    echo "   - المستخدمين: $usersAfter (زيادة: " . ($usersAfter - $usersBefore) . ")\n\n";

    // اختبار الـ API validation
    echo "🔍 اختبار ValidTeacherId rule:\n";
    
    $rule = new \App\Rules\ValidTeacherId();
    
    // اختبار بـ teacher_id الجديد
    if ($rule->passes('teacher_id', $teacher->id)) {
        echo "   ✅ teacher_id ({$teacher->id}) مقبول\n";
        echo "   🎯 user_id المُعاد: " . $rule->getFoundUserId() . "\n";
    } else {
        echo "   ❌ teacher_id ({$teacher->id}) مرفوض: " . $rule->message() . "\n";
    }
    
    // اختبار بـ user_id المرتبط
    if ($teacher->user_id && $rule->passes('teacher_id', $teacher->user_id)) {
        echo "   ✅ user_id ({$teacher->user_id}) مقبول\n";
    } else {
        echo "   ❌ user_id ({$teacher->user_id}) مرفوض\n";
    }

    echo "\n✨ اختبار إنشاء معلم جديد نجح بالكامل!\n";

} catch (Exception $e) {
    echo "❌ خطأ أثناء الاختبار: " . $e->getMessage() . "\n";
    echo "📄 تفاصيل الخطأ:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "انتهى اختبار إنشاء المعلم الجديد\n";
