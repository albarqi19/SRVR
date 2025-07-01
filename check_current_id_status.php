<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 فحص الوضع الحالي للمعرفات\n";
echo str_repeat("=", 60) . "\n\n";

echo "📊 الوضع الحالي:\n";
$teachers = Teacher::with('user')->limit(10)->get();

echo "+--------+------------------------+----------+----------+----------+\n";
echo "| الاسم   | Teacher ID             | User ID  | متطابق؟  | الحل الحالي  |\n";
echo "+--------+------------------------+----------+----------+----------+\n";

foreach ($teachers as $teacher) {
    $name = substr($teacher->name, 0, 20);
    $isMatched = ($teacher->id === $teacher->user_id) ? 'نعم ✅' : 'لا ❌';
    $currentSolution = 'تحويل في API';
    
    echo sprintf("| %-20s | %-10s | %-8s | %-8s | %-12s |\n", 
        $name, 
        $teacher->id, 
        $teacher->user_id, 
        $isMatched,
        $currentSolution
    );
}
echo "+--------+------------------------+----------+----------+----------+\n";

echo "\n";

// فحص حالة محددة
echo "🎯 مثال: معلم رقم 55\n";
$teacher55 = Teacher::find(55);
if ($teacher55) {
    echo "   ✅ موجود:\n";
    echo "      - Teacher ID: 55\n";
    echo "      - User ID: {$teacher55->user_id}\n";
    echo "      - متطابق؟ " . (55 === $teacher55->user_id ? 'نعم ✅' : 'لا ❌') . "\n";
} else {
    echo "   ❌ غير موجود\n";
}

echo "\n";

// إحصائيات التطابق
$totalTeachers = Teacher::count();
$matchedCount = Teacher::whereRaw('id = user_id')->count();
$unmatchedCount = $totalTeachers - $matchedCount;

echo "📈 إحصائيات التطابق:\n";
echo "   📊 إجمالي المعلمين: {$totalTeachers}\n";
echo "   ✅ متطابقين (Teacher ID = User ID): {$matchedCount}\n";
echo "   ❌ غير متطابقين: {$unmatchedCount}\n";
echo "   📊 نسبة التطابق: " . round(($matchedCount / $totalTeachers) * 100, 1) . "%\n";

echo "\n";

echo "💡 اقتراحك الأصلي كان:\n";
echo "   🎯 توحيد الأرقام: Teacher ID = User ID\n";
echo "   📝 مثال: المعلم رقم 55 → User ID يصبح 55\n";
echo "   ✅ فائدة: Frontend يرسل رقم واحد، لا تعقيدات\n";

echo "\n";

echo "🔧 الحل الحالي:\n";
echo "   📤 Frontend يرسل: teacher_id = 55\n";
echo "   🔄 API يجد Teacher[55] ويحفظ بـ user_id المرتبط\n";
echo "   📺 العرض يظهر الاسم الصحيح\n";
echo "   ⚙️ التعقيد: يحتاج تحويل في API\n";

echo "\n";

echo "❓ السؤال: هل تريد التوحيد الفعلي؟\n";
echo "   ✅ مزايا التوحيد:\n";
echo "      - بساطة مطلقة\n";
echo "      - لا حاجة لتحويلات\n";
echo "      - معرف واحد لكل شخص\n";
echo "   ⚠️ تحديات التوحيد:\n";
echo "      - تعديل قاعدة البيانات\n";
echo "      - التعامل مع Foreign Keys\n";
echo "      - احتمالية تعارض الأرقام\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "الخلاصة: الحل الحالي يعمل، لكن التوحيد أفضل للمستقبل\n";
