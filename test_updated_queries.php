<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 اختبار الاستعلامات المحدثة:\n";
echo str_repeat("=", 50) . "\n\n";

$supervisorId = 1;

// 1. فحص الحلقات المشرف عليها
$supervisedCircleIds = DB::table('circle_supervisors')
    ->where('supervisor_id', $supervisorId)
    ->pluck('quran_circle_id');

echo "1️⃣ الحلقات المشرف عليها:\n";
echo "العدد: " . $supervisedCircleIds->count() . "\n";
echo "IDs: " . $supervisedCircleIds->implode(', ') . "\n\n";

// 2. المعلمين الذين لديهم حلقات فرعية نشطة
$teachersWithActiveGroups = DB::table('teachers')
    ->join('circle_groups', 'teachers.id', '=', 'circle_groups.teacher_id')
    ->where('circle_groups.status', 'نشطة')
    ->whereIn('teachers.quran_circle_id', $supervisedCircleIds)
    ->where('teachers.is_active_user', true)
    ->select('teachers.id', 'teachers.name', 'circle_groups.name as group_name')
    ->get();

echo "2️⃣ المعلمين مع حلقات فرعية نشطة:\n";
echo "العدد: " . $teachersWithActiveGroups->count() . "\n";
foreach ($teachersWithActiveGroups as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id}) - حلقة فرعية: {$teacher->group_name}\n";
}

echo "\n";

// 3. المعلمين الذين لديهم طلاب مباشرة
$teachersWithStudents = DB::table('teachers')
    ->join('students', 'teachers.quran_circle_id', '=', 'students.quran_circle_id')
    ->whereIn('teachers.quran_circle_id', $supervisedCircleIds)
    ->where('teachers.is_active_user', true)
    ->where('students.is_active', true)
    ->whereNull('students.circle_group_id') // طلاب غير منتمين لحلقات فرعية
    ->select('teachers.id', 'teachers.name')
    ->distinct()
    ->get();

echo "3️⃣ المعلمين مع طلاب مباشرين:\n";
echo "العدد: " . $teachersWithStudents->count() . "\n";
foreach ($teachersWithStudents as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id})\n";
}

echo "\n";

// 4. دمج النتائج
$allRelevantTeacherIds = $teachersWithActiveGroups->pluck('id')
    ->merge($teachersWithStudents->pluck('id'))
    ->unique();

echo "4️⃣ إجمالي المعلمين ذوي الصلة:\n";
echo "العدد النهائي: " . $allRelevantTeacherIds->count() . "\n";
echo "IDs: " . $allRelevantTeacherIds->implode(', ') . "\n\n";

// 5. مقارنة مع الطريقة القديمة
$oldWayTeachers = DB::table('teachers')
    ->whereIn('quran_circle_id', $supervisedCircleIds)
    ->where('is_active_user', true)
    ->count();

echo "5️⃣ مقارنة:\n";
echo "الطريقة القديمة (جميع المعلمين): $oldWayTeachers\n";
echo "الطريقة الجديدة (ذوي الصلة فقط): " . $allRelevantTeacherIds->count() . "\n";
echo "التوفير: " . ($oldWayTeachers - $allRelevantTeacherIds->count()) . " معلم\n";

?>
