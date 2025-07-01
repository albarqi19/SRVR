<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 فحص الحلقات الفعلية والطلاب المرتبطين بها:\n";
echo str_repeat("=", 70) . "\n\n";

// 1. الحصول على جميع الحلقات مع عدد الطلاب الفعلي
echo "1️⃣ الحلقات مع عدد الطلاب الفعلي:\n";
$circles = DB::table('quran_circles')
    ->leftJoin('students', 'quran_circles.id', '=', 'students.quran_circle_id')
    ->select('quran_circles.id', 'quran_circles.name', DB::raw('COUNT(students.id) as student_count'))
    ->where('students.is_active', true)
    ->groupBy('quran_circles.id', 'quran_circles.name')
    ->orderBy('quran_circles.name')
    ->get();

foreach ($circles as $circle) {
    echo "   📍 $circle->name (ID: $circle->id): $circle->student_count طالب\n";
}

// 2. فحص الحلقة ID=1 (تجربة) 
echo "\n2️⃣ فحص تفصيلي للحلقة ID=1 (تجربة):\n";
$circle1Students = DB::table('students')
    ->where('quran_circle_id', 1)
    ->where('is_active', true)
    ->select('id', 'name', 'is_active')
    ->get();

echo "   عدد الطلاب النشطين في حلقة 'تجربة': " . $circle1Students->count() . "\n";
if ($circle1Students->count() > 0) {
    echo "   الطلاب:\n";
    foreach ($circle1Students->take(10) as $student) {
        echo "     - $student->name (ID: $student->id, نشط: " . ($student->is_active ? 'نعم' : 'لا') . ")\n";
    }
}

// 3. فحص المعلمين في الحلقة ID=1
echo "\n3️⃣ المعلمين في حلقة 'تجربة' (ID=1):\n";
$circle1Teachers = DB::table('teachers')
    ->where('quran_circle_id', 1)
    ->where('is_active_user', true)
    ->select('id', 'name', 'is_active_user')
    ->get();

echo "   عدد المعلمين النشطين: " . $circle1Teachers->count() . "\n";
foreach ($circle1Teachers as $teacher) {
    echo "   - $teacher->name (ID: $teacher->id)\n";
}

// 4. فحص جميع الطلاب غير المرتبطين بحلقات
echo "\n4️⃣ الطلاب غير المرتبطين بحلقات:\n";
$unassignedStudents = DB::table('students')
    ->whereNull('quran_circle_id')
    ->orWhere('quran_circle_id', 0)
    ->where('is_active', true)
    ->count();

echo "   عدد الطلاب غير المرتبطين بحلقات: $unassignedStudents\n";

// 5. إجمالي الطلاب
echo "\n5️⃣ إجمالي الطلاب:\n";
$totalStudents = DB::table('students')->where('is_active', true)->count();
$assignedStudents = DB::table('students')
    ->where('is_active', true)
    ->whereNotNull('quran_circle_id')
    ->where('quran_circle_id', '>', 0)
    ->count();

echo "   إجمالي الطلاب النشطين: $totalStudents\n";
echo "   الطلاب المرتبطين بحلقات: $assignedStudents\n";
echo "   الطلاب غير المرتبطين: " . ($totalStudents - $assignedStudents) . "\n";

// 6. فحص المعلم أحمد علي
echo "\n6️⃣ البحث عن المعلم 'أحمد علي':\n";
$ahmadTeachers = DB::table('teachers')
    ->where('name', 'LIKE', '%أحمد علي%')
    ->orWhere('name', 'LIKE', '%احمد علي%')
    ->get();

if ($ahmadTeachers->count() > 0) {
    foreach ($ahmadTeachers as $teacher) {
        echo "   - $teacher->name (ID: $teacher->id, حلقة: $teacher->quran_circle_id, نشط: " . ($teacher->is_active_user ? 'نعم' : 'لا') . ")\n";
        
        // عدد الطلاب الفعلي للمعلم
        $realStudentCount = DB::table('students')
            ->where('quran_circle_id', $teacher->quran_circle_id)
            ->where('is_active', true)
            ->count();
        echo "     عدد الطلاب الفعلي في حلقته: $realStudentCount\n";
    }
} else {
    echo "   ❌ لم يتم العثور على معلم بهذا الاسم\n";
}

echo "\n✅ انتهى الفحص!\n";
?>
