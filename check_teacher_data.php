<?php
require_once 'vendor/autoload.php';

// تحميل إعدادات Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// الاتصال بقاعدة البيانات
use Illuminate\Support\Facades\DB;

echo "=== فحص بيانات المعلم رقم 1 ===\n\n";

// 1. فحص معلومات المعلم الأساسية
echo "1. معلومات المعلم:\n";
$teacher = DB::table('teachers')->where('id', 1)->first();
if ($teacher) {
    echo "- ID: " . $teacher->id . "\n";
    echo "- الاسم: " . $teacher->name . "\n";
    echo "- المسجد ID: " . $teacher->mosque_id . "\n";
    echo "- الحلقة ID: " . $teacher->quran_circle_id . "\n";
    echo "- نشط: " . ($teacher->is_active_user ? 'نعم' : 'لا') . "\n\n";
} else {
    echo "المعلم غير موجود!\n\n";
}

// 2. فحص الحلقة المرتبطة
echo "2. فحص الحلقة المرتبطة:\n";
if ($teacher && $teacher->quran_circle_id) {
    $circle = DB::table('quran_circles')->where('id', $teacher->quran_circle_id)->first();
    if ($circle) {
        echo "- ID الحلقة: " . $circle->id . "\n";
        echo "- اسم الحلقة: " . $circle->name . "\n";
        echo "- المستوى: " . $circle->grade_level . "\n";
        echo "- نشطة: " . ($circle->is_active ? 'نعم' : 'لا') . "\n\n";
    } else {
        echo "الحلقة غير موجودة في الجدول!\n\n";
    }
} else {
    echo "المعلم لا يملك حلقة مرتبطة!\n\n";
}

// 3. فحص الطلاب المرتبطين بالحلقة
echo "3. فحص الطلاب:\n";
if ($teacher && $teacher->quran_circle_id) {
    $students = DB::table('students')->where('quran_circle_id', $teacher->quran_circle_id)->get();
    echo "عدد الطلاب في الحلقة: " . $students->count() . "\n";
    
    if ($students->count() > 0) {
        echo "قائمة الطلاب:\n";
        foreach ($students as $student) {
            echo "  - ID: " . $student->id . ", الاسم: " . $student->name . ", نشط: " . ($student->is_active ? 'نعم' : 'لا') . "\n";
        }
    }
    echo "\n";
} else {
    echo "لا يمكن فحص الطلاب لأن المعلم لا يملك حلقة\n\n";
}

// 4. فحص جميع الحلقات الموجودة
echo "4. جميع الحلقات الموجودة:\n";
$allCircles = DB::table('quran_circles')->get();
echo "إجمالي عدد الحلقات: " . $allCircles->count() . "\n";
if ($allCircles->count() > 0) {
    foreach ($allCircles as $circle) {
        $studentsCount = DB::table('students')->where('quran_circle_id', $circle->id)->count();
        echo "  - ID: " . $circle->id . ", الاسم: " . $circle->name . ", عدد الطلاب: " . $studentsCount . "\n";
    }
}
echo "\n";

// 5. فحص جميع المعلمين
echo "5. جميع المعلمين:\n";
$allTeachers = DB::table('teachers')->get();
echo "إجمالي عدد المعلمين: " . $allTeachers->count() . "\n";
foreach ($allTeachers as $t) {
    echo "  - ID: " . $t->id . ", الاسم: " . $t->name . ", الحلقة ID: " . ($t->quran_circle_id ?? 'لا يوجد') . "\n";
}
echo "\n";

// 6. اقتراح حل
echo "6. اقتراح للحل:\n";
if ($allCircles->count() > 0 && $teacher && !$teacher->quran_circle_id) {
    $firstCircle = $allCircles->first();
    echo "يمكن ربط المعلم بالحلقة الأولى (ID: " . $firstCircle->id . ")\n";
    echo "الأمر: UPDATE teachers SET quran_circle_id = " . $firstCircle->id . " WHERE id = 1;\n";
}
