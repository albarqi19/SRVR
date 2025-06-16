<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use App\Models\Mosque;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 اختبار بيانات المعلمين والطلاب\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. عرض المعلمين المتاحين
echo "👨‍🏫 المعلمين المتاحين:\n";
echo "-" . str_repeat("-", 30) . "\n";

$teachers = Teacher::with(['mosque:id,name', 'quranCircle:id,name'])
    ->select('id', 'name', 'mosque_id', 'quran_circle_id')
    ->take(10)
    ->get();

if ($teachers->isEmpty()) {
    echo "❌ لا يوجد معلمين في النظام\n\n";
} else {
    foreach ($teachers as $teacher) {
        echo "ID: {$teacher->id}\n";
        echo "  الاسم: {$teacher->name}\n";
        echo "  المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد') . " (ID: {$teacher->mosque_id})\n";
        echo "  الحلقة: " . ($teacher->quranCircle ? $teacher->quranCircle->name : 'غير محدد') . " (ID: {$teacher->quran_circle_id})\n";
        echo "  ---\n";
    }
}

// 2. اختيار معلم للاختبار
$testTeacher = $teachers->first();
if (!$testTeacher) {
    echo "❌ لا يمكن العثور على معلم للاختبار\n";
    exit;
}

echo "\n🎯 معلم الاختبار: {$testTeacher->name} (ID: {$testTeacher->id})\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 3. فحص الطلاب المرتبطين بالمعلم
echo "📊 إحصائيات الطلاب:\n";
echo "-" . str_repeat("-", 20) . "\n";

// الطلاب من الحلقة الأساسية
$mainCircleStudents = collect();
if ($testTeacher->quran_circle_id) {
    $mainCircleStudents = Student::where('quran_circle_id', $testTeacher->quran_circle_id)
        ->with(['circleGroup:id,name'])
        ->get();
    echo "طلاب الحلقة الأساسية: " . $mainCircleStudents->count() . "\n";
}

// الطلاب من الحلقات الفرعية
$circleGroupStudents = collect();
if ($testTeacher->quran_circle_id) {
    $circleGroups = CircleGroup::where('quran_circle_id', $testTeacher->quran_circle_id)
        ->where('teacher_id', $testTeacher->id)
        ->get();
    
    if ($circleGroups->isNotEmpty()) {
        echo "الحلقات الفرعية للمعلم: " . $circleGroups->count() . "\n";
        foreach ($circleGroups as $group) {
            $groupStudents = Student::where('circle_group_id', $group->id)->get();
            $circleGroupStudents = $circleGroupStudents->merge($groupStudents);
            echo "  - {$group->name}: " . $groupStudents->count() . " طلاب\n";
        }
    }
}

echo "إجمالي الطلاب: " . ($mainCircleStudents->count() + $circleGroupStudents->count()) . "\n\n";

// 4. عرض تفاصيل الطلاب
if ($mainCircleStudents->isNotEmpty() || $circleGroupStudents->isNotEmpty()) {
    echo "📋 تفاصيل الطلاب:\n";
    echo "-" . str_repeat("-", 15) . "\n";
    
    // طلاب الحلقة الأساسية
    if ($mainCircleStudents->isNotEmpty()) {
        echo "\n🔵 طلاب الحلقة الأساسية:\n";
        foreach ($mainCircleStudents->take(5) as $student) {
            echo "  - {$student->name} (ID: {$student->id})";
            if ($student->circleGroup) {
                echo " - حلقة فرعية: {$student->circleGroup->name}";
            }
            echo "\n";
        }
    }
    
    // طلاب الحلقات الفرعية
    if ($circleGroupStudents->isNotEmpty()) {
        echo "\n🟢 طلاب الحلقات الفرعية:\n";
        foreach ($circleGroupStudents->take(5) as $student) {
            echo "  - {$student->name} (ID: {$student->id})\n";
        }
    }
}

// 5. اختبار استدعاء API محلياً
echo "\n🌐 اختبار API محلياً:\n";
echo "-" . str_repeat("-", 20) . "\n";

try {
    // إنشاء طلب HTTP داخلي لاختبار API
    $baseUrl = 'http://localhost:8000/api'; // أو أي URL محلي آخر
    
    echo "URL للاختبار: {$baseUrl}/teachers/{$testTeacher->id}/students\n";
    echo "يمكنك تشغيل هذا الأمر في المتصفح أو باستخدام curl:\n";
    echo "curl -X GET \"{$baseUrl}/teachers/{$testTeacher->id}/students\"\n\n";
    
    // إذا كان هناك مسجد محدد، اختبر API المسجد أيضاً
    if ($testTeacher->mosque_id) {
        echo "URL لطلاب المعلم من مسجد محدد:\n";
        echo "{$baseUrl}/teachers/{$testTeacher->id}/mosques/{$testTeacher->mosque_id}/students\n";
        echo "curl -X GET \"{$baseUrl}/teachers/{$testTeacher->id}/mosques/{$testTeacher->mosque_id}/students\"\n";
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ في اختبار API: " . $e->getMessage() . "\n";
}

echo "\n✅ انتهى الاختبار\n";
