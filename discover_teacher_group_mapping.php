<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 اكتشاف الربط الصحيح بين المعلمين والمجموعات:\n";
echo str_repeat('=', 60) . "\n\n";

// 1. الحصول على ربط المعلمين بالمجموعات
echo "1️⃣ ربط المعلمين بالمجموعات من جدول circle_groups:\n";
$teacherGroups = DB::table('circle_groups')
    ->join('teachers', 'circle_groups.teacher_id', '=', 'teachers.id')
    ->select('circle_groups.id as group_id', 'circle_groups.name as group_name', 
             'teachers.id as teacher_id', 'teachers.name as teacher_name')
    ->orderBy('circle_groups.id')
    ->get();

if ($teacherGroups->count() > 0) {
    foreach ($teacherGroups as $tg) {
        echo "   📋 مجموعة '$tg->group_name' (ID: $tg->group_id) ← معلم: $tg->teacher_name (ID: $tg->teacher_id)\n";
    }
} else {
    echo "   ❌ لا يوجد ربط في جدول circle_groups\n";
}

// 2. حساب عدد الطلاب لكل معلم حسب المجموعة
echo "\n2️⃣ عدد الطلاب لكل معلم:\n";
foreach ($teacherGroups as $tg) {
    $studentCount = DB::table('students')
        ->where('circle_group_id', $tg->group_id)
        ->where('is_active', true)
        ->count();
    
    echo "   👤 $tg->teacher_name: $studentCount طالب (مجموعة: $tg->group_name)\n";
}

// 3. مقارنة مع البيانات من الواجهة
echo "\n3️⃣ مقارنة مع بيانات الواجهة:\n";
$interfaceData = [
    'الشنقيطي' => 7,
    'الشهاب' => 6,
    'محمدين' => 4,
    'السبيعي' => 2,
    'ايمن' => 1,
    'بليدي' => 14
];

foreach ($interfaceData as $teacherPart => $expectedCount) {
    // البحث عن المعلم
    $foundTeacher = $teacherGroups->filter(function($tg) use ($teacherPart) {
        return stripos($tg->teacher_name, $teacherPart) !== false;
    })->first();
    
    if ($foundTeacher) {
        $actualCount = DB::table('students')
            ->where('circle_group_id', $foundTeacher->group_id)
            ->where('is_active', true)
            ->count();
        
        $status = ($actualCount == $expectedCount) ? '✅' : '❌';
        echo "   $status $teacherPart: متوقع $expectedCount, فعلي $actualCount\n";
    } else {
        echo "   ❌ $teacherPart: غير موجود في الربط\n";
    }
}

echo "\n4️⃣ الحل المطلوب:\n";
echo "بدلاً من البحث في الحلقة الرئيسية، يجب البحث في المجموعة الفرعية للمعلم!\n";
echo "التعديل المطلوب في الكود:\n";
echo "- استخدام circle_groups.teacher_id بدلاً من quran_circle_id\n";
echo "- البحث عن الطلاب في circle_group_id بدلاً من quran_circle_id\n";

echo "\n✅ انتهى الاكتشاف!\n";
?>
