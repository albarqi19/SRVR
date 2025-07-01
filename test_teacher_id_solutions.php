<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 حلول مشكلة خلط معرف المعلم\n";
echo str_repeat("=", 50) . "\n\n";

echo "📋 الحالة الحالية:\n";
echo "   - Frontend يرسل: user_id (معرف تسجيل الدخول)\n";
echo "   - API يحتاج: teacher_id (معرف المعلم في جدول teachers)\n";
echo "   - النتيجة: يتم عرض معلم خاطئ\n\n";

echo "💡 الحلول المتاحة:\n\n";

// الحل الأول: دالة تحويل في Controller
echo "🔧 الحل الأول: دالة تحويل في RecitationSessionController\n";
echo "   الكود المطلوب إضافته:\n\n";
echo "   ```php\n";
echo "   private function getTeacherIdFromUserId(\$userId): ?int\n";
echo "   {\n";
echo "       \$teacher = Teacher::where('user_id', \$userId)->first();\n";
echo "       return \$teacher ? \$teacher->id : null;\n";
echo "   }\n";
echo "   ```\n\n";

// الحل الثاني: تعديل ValidTeacherId rule
echo "🔧 الحل الثاني: تحديث ValidTeacherId rule (موجود مسبقاً)\n";
echo "   ✅ تم تطبيقه بالفعل - يقبل كلاً من teacher_id و user_id\n\n";

// الحل الثالث: Middleware
echo "🔧 الحل الثالث: إنشاء Middleware لتحويل user_id تلقائياً\n";
echo "   الفائدة: تحويل تلقائي في جميع API endpoints\n\n";

// الحل الرابع: تعديل Frontend
echo "🔧 الحل الرابع: تعديل Frontend ليرسل teacher_id الحقيقي\n";
echo "   الكود المطلوب في Frontend:\n\n";
echo "   ```javascript\n";
echo "   // بدلاً من:\n";
echo "   teacher_id: user?.id\n";
echo "   \n";
echo "   // استخدم:\n";
echo "   teacher_id: user?.teacher_id || user?.id\n";
echo "   ```\n\n";

// اختبار الحلول
echo "🧪 اختبار الحلول:\n\n";

// اختبار الحل الأول
echo "1️⃣ اختبار دالة التحويل:\n";
function getTeacherIdFromUserId($userId) {
    $teacher = Teacher::where('user_id', $userId)->first();
    return $teacher ? $teacher->id : null;
}

$testUserId = 34; // معرف مستخدم عبدالله الشنقيطي
$convertedTeacherId = getTeacherIdFromUserId($testUserId);

echo "   Input: user_id = {$testUserId}\n";
echo "   Output: teacher_id = " . ($convertedTeacherId ?? 'null') . "\n";

if ($convertedTeacherId) {
    $teacher = Teacher::find($convertedTeacherId);
    echo "   المعلم: {$teacher->name}\n";
}

echo "\n";

// اختبار ValidTeacherId rule مع user_id
echo "2️⃣ اختبار ValidTeacherId rule:\n";
$rule = new App\Rules\ValidTeacherId();
$passes = $rule->passes('teacher_id', $testUserId);
echo "   Input: teacher_id = {$testUserId} (في الحقيقة user_id)\n";
echo "   Valid: " . ($passes ? 'نعم' : 'لا') . "\n";
echo "   Found user_id: " . ($rule->getFoundUserId() ?? 'null') . "\n";

echo "\n";

// توصية الحل الأمثل
echo "🎯 الحل الموصى به:\n";
echo "   1. ✅ ValidTeacherId rule محدث بالفعل\n";
echo "   2. 🔧 إضافة دالة تحويل في Controller\n";
echo "   3. 📝 تحديث Frontend ليرسل البيانات الصحيحة\n";
echo "   4. 📚 توثيق الاستخدام الصحيح للـ API\n\n";

echo str_repeat("=", 50) . "\n";
echo "انتهت معاينة الحلول\n";
