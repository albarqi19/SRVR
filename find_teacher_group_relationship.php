<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 البحث عن جداول ربط المعلمين بالمجموعات:\n";
echo str_repeat("=", 60) . "\n\n";

// 1. البحث في جميع الجداول عن كلمات مفتاحية
echo "1️⃣ البحث عن الجداول المتعلقة بالمعلمين والمجموعات:\n";
$tables = DB::select('SHOW TABLES');
$tableNames = array_map(function($table) {
    return array_values((array)$table)[0];
}, $tables);

$relevantTables = array_filter($tableNames, function($table) {
    return strpos($table, 'teacher') !== false || 
           strpos($table, 'group') !== false || 
           strpos($table, 'circle') !== false ||
           strpos($table, 'assignment') !== false;
});

foreach ($relevantTables as $table) {
    echo "   📋 $table\n";
}

// 2. فحص جدول circle_groups إذا كان موجود
echo "\n2️⃣ فحص جدول circle_groups:\n";
try {
    $circleGroups = DB::table('circle_groups')
        ->select('*')
        ->limit(10)
        ->get();
    
    if ($circleGroups->count() > 0) {
        echo "✅ جدول circle_groups موجود:\n";
        $first = $circleGroups->first();
        $columns = array_keys((array)$first);
        echo "   الأعمدة: " . implode(', ', $columns) . "\n";
        
        foreach ($circleGroups as $group) {
            echo "   - ID: $group->id, الاسم: " . ($group->name ?? 'غير محدد') . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ جدول circle_groups غير موجود\n";
}

// 3. فحص جدول teacher_assignments أو ما شابه
echo "\n3️⃣ البحث عن جداول التخصيص:\n";
$assignmentTables = ['teacher_assignments', 'teacher_groups', 'group_teachers', 'circle_group_teachers'];

foreach ($assignmentTables as $table) {
    try {
        $data = DB::table($table)->limit(5)->get();
        echo "✅ جدول $table موجود مع " . $data->count() . " سجل\n";
        if ($data->count() > 0) {
            $first = $data->first();
            $columns = array_keys((array)$first);
            echo "   الأعمدة: " . implode(', ', $columns) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ جدول $table غير موجود\n";
    }
}

// 4. فحص إذا كان في جدول المعلمين عمود مخفي
echo "\n4️⃣ فحص جميع أعمدة جدول teachers:\n";
$teacherColumns = DB::select("DESCRIBE teachers");
foreach ($teacherColumns as $column) {
    echo "   - $column->Field ($column->Type)\n";
}

// 5. فحص العلاقة من خلال البيانات الموجودة
echo "\n5️⃣ محاولة اكتشاف العلاقة من البيانات:\n";
echo "مقارنة أسماء المعلمين مع أرقام المجموعات:\n";

$teachers = DB::table('teachers')
    ->where('quran_circle_id', 1)
    ->where('is_active_user', true)
    ->select('id', 'name')
    ->get();

$groups = DB::table('students')
    ->select('circle_group_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('circle_group_id')
    ->groupBy('circle_group_id')
    ->orderBy('count', 'desc')
    ->get();

echo "المعلمين:\n";
foreach ($teachers as $teacher) {
    echo "   👤 $teacher->name (ID: $teacher->id)\n";
}

echo "\nالمجموعات وأعدادها:\n";
foreach ($groups as $group) {
    echo "   📊 مجموعة $group->circle_group_id: $group->count طالب\n";
}

echo "\n✅ انتهى البحث!\n";
?>
