<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 البحث عن كيفية ربط المعلمين بالطلاب:\n";
echo str_repeat('=', 50) . "\n";

// فحص جدول students للبحث عن معرف المعلم
echo "1️⃣ فحص جدول students:\n";
$studentsColumns = DB::select("DESCRIBE students");
echo "الأعمدة: ";
foreach ($studentsColumns as $column) {
    echo $column->Field . ", ";
    if (stripos($column->Field, 'teacher') !== false) {
        echo "\n✅ عمود معلم موجود: $column->Field\n";
    }
}

echo "\n\n2️⃣ فحص المجموعات الفرعية:\n";
$circleGroupSamples = DB::table('students')
    ->select('id', 'name', 'circle_group_id', 'quran_circle_id')
    ->whereNotNull('circle_group_id')
    ->take(10)
    ->get();

if ($circleGroupSamples->count() > 0) {
    echo "✅ الطلاب لديهم مجموعات فرعية:\n";
    foreach ($circleGroupSamples as $student) {
        echo "   - $student->name: مجموعة=$student->circle_group_id, حلقة=$student->quran_circle_id\n";
    }
} else {
    echo "❌ لا توجد مجموعات فرعية\n";
}

echo "\n3️⃣ فحص توزيع الطلاب حسب المجموعات:\n";
$groupDistribution = DB::table('students')
    ->select('circle_group_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('circle_group_id')
    ->where('is_active', true)
    ->groupBy('circle_group_id')
    ->orderBy('count', 'desc')
    ->get();

if ($groupDistribution->count() > 0) {
    echo "توزيع الطلاب على المجموعات:\n";
    foreach ($groupDistribution as $group) {
        echo "   مجموعة $group->circle_group_id: $group->count طالب\n";
    }
} else {
    echo "❌ لا يوجد توزيع على مجموعات\n";
}

echo "\n4️⃣ فحص ربط المعلمين بالمجموعات:\n";
// البحث في جداول أخرى عن ربط المعلم بالمجموعة
try {
    $teacherGroups = DB::table('teachers')
        ->select('id', 'name', 'quran_circle_id', 'circle_group_id')
        ->where('is_active_user', true)
        ->get();
    
    $groupCounts = [];
    foreach ($teacherGroups as $teacher) {
        if (isset($teacher->circle_group_id) && $teacher->circle_group_id) {
            $groupCounts[$teacher->circle_group_id] = ($groupCounts[$teacher->circle_group_id] ?? 0) + 1;
            echo "   معلم: $teacher->name (ID: $teacher->id) -> مجموعة: $teacher->circle_group_id\n";
        }
    }
} catch (Exception $e) {
    echo "❌ لا يوجد عمود circle_group_id في جدول teachers\n";
}

echo "\n✅ انتهى الفحص!\n";
?>
