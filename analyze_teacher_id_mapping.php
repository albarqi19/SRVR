<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 تحليل دقيق لمشكلة معرف المعلم\n";
echo str_repeat("=", 60) . "\n\n";

// البحث عن المعلم "عبدالله الشنقيطي"
echo "1️⃣ تحليل معلم 'عبدالله الشنقيطي':\n";
$abdullah = Teacher::where('name', 'like', '%عبدالله الشنقيطي%')->first();

if ($abdullah) {
    echo "   📋 بيانات المعلم:\n";
    echo "      - Teacher ID: {$abdullah->id}\n";
    echo "      - الاسم: {$abdullah->name}\n";
    echo "      - User ID المرتبط: " . ($abdullah->user_id ?? 'غير محدد') . "\n";
    
    if ($abdullah->user_id) {
        $user = User::find($abdullah->user_id);
        echo "      - اسم المستخدم: " . ($user ? $user->name : 'غير موجود') . "\n";
        echo "      - بريد المستخدم: " . ($user ? $user->email : 'غير موجود') . "\n";
    }
} else {
    echo "   ❌ لم يتم العثور على المعلم\n";
}

echo "\n";

// فهم ما يرسله Frontend
echo "2️⃣ ما يرسله Frontend:\n";
echo "   🎯 السيناريو:\n";
echo "      - المعلم المسجل دخوله: عبدالله الشنقيطي\n";
echo "      - Frontend يرسل: user?.id\n";
echo "      - القيمة المرسلة: 89 (حسب المثال)\n\n";

// التحقق من المستخدم 89
echo "3️⃣ فحص User ID = 89:\n";
$user89 = User::find(89);
if ($user89) {
    echo "   ✅ المستخدم موجود:\n";
    echo "      - الاسم: {$user89->name}\n";
    echo "      - البريد: {$user89->email}\n";
    
    // البحث عن المعلم المرتبط
    $teacherLinked = Teacher::where('user_id', 89)->first();
    if ($teacherLinked) {
        echo "      - المعلم المرتبط: {$teacherLinked->name} (Teacher ID: {$teacherLinked->id})\n";
    } else {
        echo "      - لا يوجد معلم مرتبط بهذا المستخدم\n";
    }
} else {
    echo "   ❌ المستخدم غير موجود\n";
}

echo "\n";

// التحقق من Teacher ID = 89
echo "4️⃣ فحص Teacher ID = 89:\n";
$teacher89 = Teacher::find(89);
if ($teacher89) {
    echo "   ✅ المعلم موجود:\n";
    echo "      - الاسم: {$teacher89->name}\n";
    echo "      - User ID المرتبط: " . ($teacher89->user_id ?? 'غير محدد') . "\n";
} else {
    echo "   ❌ المعلم غير موجود\n";
}

echo "\n";

// الحل المطلوب
echo "5️⃣ الحل المطلوب:\n";
echo "   🎯 هدف:\n";
echo "      - Frontend: يرسل معرف المستخدم المسجل دخوله\n";
echo "      - API: يجب أن يحفظ باستخدام هذا المعرف في teacher_id\n";
echo "      - العرض: يجب أن يظهر اسم المعلم الصحيح\n\n";

// جدول mapping صحيح
echo "6️⃣ Mapping الصحيح:\n";
$allTeachers = Teacher::with('user')->limit(10)->get();
echo "   📋 Teacher -> User Mapping:\n";
foreach ($allTeachers as $teacher) {
    $userName = $teacher->user ? $teacher->user->name : 'لا يوجد';
    echo "      Teacher[{$teacher->id}] '{$teacher->name}' -> User[{$teacher->user_id}] '{$userName}'\n";
}

echo "\n";

// الحل المقترح
echo "7️⃣ الحل المقترح:\n";
echo "   Option A: Frontend يرسل user_id، API يحفظه مباشرة في teacher_id\n";
echo "   Option B: Frontend يرسل user_id، API يبحث عن teacher_id المقابل\n";
echo "   Option C: Frontend يتم تعديله ليرسل teacher_id الحقيقي\n\n";

// اختبار Option A
echo "🧪 اختبار Option A (الحل الحالي):\n";
if ($user89) {
    echo "   Input: user_id = 89\n";
    echo "   API يحفظ: teacher_id = 89\n";
    echo "   عند العرض: Teacher::find(89) = " . ($teacher89 ? $teacher89->name : 'غير موجود') . "\n";
    echo "   النتيجة: " . ($teacher89 && $teacher89->name === 'عبدالله الشنقيطي' ? '✅ صحيح' : '❌ خطأ') . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "انتهى التحليل\n";
