<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 فحص الحلقات الفرعية والمعلمين النشطين:\n";
echo str_repeat("=", 60) . "\n\n";

// 1. فحص جميع الحلقات الفرعية
echo "1️⃣ جميع الحلقات الفرعية:\n";
$allGroups = DB::table('circle_groups')
    ->select('id', 'name', 'status', 'teacher_id', 'quran_circle_id')
    ->get();

echo "العدد الإجمالي: " . $allGroups->count() . "\n";
foreach ($allGroups as $group) {
    $teacherName = DB::table('teachers')->where('id', $group->teacher_id)->value('name') ?? 'غير محدد';
    echo "   - ID: {$group->id}, الاسم: {$group->name}, الحالة: {$group->status}, المعلم: {$teacherName}\n";
}

echo "\n";

// 2. فحص الحلقات الفرعية النشطة فقط
echo "2️⃣ الحلقات الفرعية النشطة فقط:\n";
$activeGroups = DB::table('circle_groups')
    ->where('status', 'active')
    ->select('id', 'name', 'teacher_id', 'quran_circle_id')
    ->get();

echo "العدد: " . $activeGroups->count() . "\n";
foreach ($activeGroups as $group) {
    $teacherName = DB::table('teachers')->where('id', $group->teacher_id)->value('name') ?? 'غير محدد';
    $circleName = DB::table('quran_circles')->where('id', $group->quran_circle_id)->value('name') ?? 'غير محدد';
    echo "   - الحلقة الفرعية: {$group->name}, المعلم: {$teacherName}, الحلقة الرئيسية: {$circleName}\n";
}

echo "\n";

// 3. فحص المعلمين الذين لديهم حلقات فرعية نشطة
echo "3️⃣ المعلمين الذين لديهم حلقات فرعية نشطة:\n";
$teachersWithActiveGroups = DB::table('teachers')
    ->join('circle_groups', 'teachers.id', '=', 'circle_groups.teacher_id')
    ->where('circle_groups.status', 'active')
    ->where('teachers.is_active_user', true)
    ->select('teachers.id', 'teachers.name', 'circle_groups.name as group_name')
    ->get();

echo "العدد: " . $teachersWithActiveGroups->count() . "\n";
foreach ($teachersWithActiveGroups as $teacher) {
    echo "   - المعلم: {$teacher->name} (ID: {$teacher->id}), الحلقة الفرعية: {$teacher->group_name}\n";
}

echo "\n";

// 4. فحص المعلمين في النظام حالياً
echo "4️⃣ جميع المعلمين النشطين:\n";
$allActiveTeachers = DB::table('teachers')
    ->where('is_active_user', true)
    ->select('id', 'name', 'quran_circle_id')
    ->get();

echo "العدد الإجمالي: " . $allActiveTeachers->count() . "\n";

// تجميع حسب الحلقة
$teachersByCircle = $allActiveTeachers->groupBy('quran_circle_id');
foreach ($teachersByCircle as $circleId => $teachers) {
    $circleName = DB::table('quran_circles')->where('id', $circleId)->value('name') ?? 'غير محدد';
    echo "   📚 حلقة {$circleName}: " . $teachers->count() . " معلم\n";
    foreach ($teachers->take(3) as $teacher) {
        echo "      - {$teacher->name}\n";
    }
    if ($teachers->count() > 3) {
        echo "      ... و " . ($teachers->count() - 3) . " معلم آخر\n";
    }
}

echo "\n";

// 5. التوصية للتحسين
echo "💡 التوصية:\n";
echo "المفترض أن يعرض API فقط:\n";
echo "✅ المعلمين الذين لديهم حلقات فرعية نشطة\n";
echo "✅ أو المعلمين الذين لديهم طلاب فعلاً\n";
echo "❌ وليس جميع المعلمين المسجلين في النظام\n";

?>
