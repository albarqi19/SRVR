<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 تشخيص مشكلة خلط معرف المعلم مع معرف المستخدم\n";
echo str_repeat("=", 70) . "\n\n";

// البحث عن المعلم "عبدالله الشنقيطي"
echo "1️⃣ البحث عن المعلم 'عبدالله الشنقيطي':\n";
$abdullahTeacher = Teacher::where('name', 'like', '%عبدالله الشنقيطي%')->first();

if ($abdullahTeacher) {
    echo "   ✅ تم العثور على المعلم:\n";
    echo "      - معرف المعلم في جدول teachers: {$abdullahTeacher->id}\n";
    echo "      - الاسم: {$abdullahTeacher->name}\n";
    echo "      - معرف المستخدم المرتبط (user_id): " . ($abdullahTeacher->user_id ?? 'غير محدد') . "\n";
    
    if ($abdullahTeacher->user_id) {
        $user = User::find($abdullahTeacher->user_id);
        if ($user) {
            echo "      - اسم المستخدم المرتبط: {$user->name}\n";
            echo "      - بريد المستخدم: {$user->email}\n";
        }
    }
} else {
    echo "   ❌ لم يتم العثور على المعلم\n";
}

echo "\n";

// البحث عن المستخدم بـ ID = 89
echo "2️⃣ فحص المستخدم بـ ID = 89 (المُرسل من API):\n";
$user89 = User::find(89);

if ($user89) {
    echo "   ✅ تم العثور على المستخدم:\n";
    echo "      - معرف المستخدم: {$user89->id}\n";
    echo "      - الاسم: {$user89->name}\n";
    echo "      - البريد الإلكتروني: {$user89->email}\n";
    echo "      - رقم الهوية: " . ($user89->identity_number ?? 'غير محدد') . "\n";
    
    // البحث عن المعلم المرتبط بهذا المستخدم
    $linkedTeacher = Teacher::where('user_id', 89)->first();
    if ($linkedTeacher) {
        echo "      - المعلم المرتبط: {$linkedTeacher->name} (Teacher ID: {$linkedTeacher->id})\n";
    } else {
        echo "      - لا يوجد معلم مرتبط بهذا المستخدم\n";
    }
} else {
    echo "   ❌ لم يتم العثور على المستخدم بـ ID = 89\n";
}

echo "\n";

// البحث عن المعلم "فهد5416"
echo "3️⃣ البحث عن المعلم 'فهد5416' (الذي ظهر في النتيجة):\n";
$fahdTeacher = Teacher::where('name', 'like', '%فهد5416%')->first();

if ($fahdTeacher) {
    echo "   ✅ تم العثور على المعلم:\n";
    echo "      - معرف المعلم في جدول teachers: {$fahdTeacher->id}\n";
    echo "      - الاسم: {$fahdTeacher->name}\n";
    echo "      - معرف المستخدم المرتبط (user_id): " . ($fahdTeacher->user_id ?? 'غير محدد') . "\n";
    
    if ($fahdTeacher->user_id) {
        $user = User::find($fahdTeacher->user_id);
        if ($user) {
            echo "      - اسم المستخدم المرتبط: {$user->name}\n";
            echo "      - بريد المستخدم: {$user->email}\n";
        }
    }
} else {
    echo "   ❌ لم يتم العثور على المعلم 'فهد5416'\n";
}

echo "\n";

// فحص جميع المعلمين الذين user_id = 89
echo "4️⃣ فحص جميع المعلمين الذين لديهم user_id = 89:\n";
$teachersWithUser89 = Teacher::where('user_id', 89)->get();

if ($teachersWithUser89->count() > 0) {
    echo "   تم العثور على " . $teachersWithUser89->count() . " معلم:\n";
    foreach ($teachersWithUser89 as $teacher) {
        echo "      - Teacher ID: {$teacher->id}, الاسم: {$teacher->name}\n";
    }
} else {
    echo "   ❌ لا يوجد معلمين مرتبطين بـ user_id = 89\n";
}

echo "\n";

// فحص الاستعلام الذي يستخدمه API
echo "5️⃣ محاكاة استعلام API عند إنشاء جلسة تسميع:\n";
$teacherId = 89; // القيمة المُرسلة من Frontend

// هذا ما يحدث في API عند استخدام teacher_id = 89
echo "   🔍 البحث عن معلم بـ teacher_id = {$teacherId}...\n";

// الطريقة الخاطئة (البحث في جدول teachers بـ ID = 89)
$wrongTeacher = Teacher::find($teacherId);
if ($wrongTeacher) {
    echo "   ❌ البحث الخاطئ في جدول teachers:\n";
    echo "      - Teacher ID: {$wrongTeacher->id}\n";
    echo "      - الاسم: {$wrongTeacher->name}\n";
    echo "      - هذا سبب ظهور '{$wrongTeacher->name}' بدلاً من 'عبدالله الشنقيطي'\n";
}

// الطريقة الصحيحة (البحث عن معلم بـ user_id = 89)
$correctTeacher = Teacher::where('user_id', $teacherId)->first();
if ($correctTeacher) {
    echo "   ✅ البحث الصحيح بـ user_id:\n";
    echo "      - Teacher ID: {$correctTeacher->id}\n";
    echo "      - الاسم: {$correctTeacher->name}\n";
    echo "      - هذا هو المعلم الصحيح الذي يجب أن يظهر\n";
} else {
    echo "   ⚠️ لا يوجد معلم مرتبط بـ user_id = {$teacherId}\n";
}

echo "\n";

// جدول مقارنة
echo "6️⃣ جدول المقارنة:\n";
echo "+----------------+-------------------------+------------------+\n";
echo "| النوع           | المعرف                 | الاسم             |\n";
echo "+----------------+-------------------------+------------------+\n";
echo "| المُرسل من API | teacher_id = 89         | عبدالله الشنقيطي |\n";
echo "| البحث الخاطئ   | Teacher::find(89)       | " . ($wrongTeacher ? $wrongTeacher->name : 'غير موجود') . " |\n";
echo "| البحث الصحيح   | Teacher::where('user_id', 89) | " . ($correctTeacher ? $correctTeacher->name : 'غير موجود') . " |\n";
echo "+----------------+-------------------------+------------------+\n";

echo "\n";

// التوصيات
echo "7️⃣ التوصيات لحل المشكلة:\n";
echo "   1. تعديل API ليستخدم البحث بـ user_id بدلاً من id\n";
echo "   2. أو تعديل Frontend ليرسل teacher_id الحقيقي بدلاً من user_id\n";
echo "   3. أو إنشاء دالة تحويل من user_id إلى teacher_id\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "انتهى التشخيص\n";
