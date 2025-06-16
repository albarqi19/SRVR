<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\QuranCircle;

echo "🔍 فحص مشكلة المعلم ID: 70" . PHP_EOL;
echo str_repeat("=", 50) . PHP_EOL;

// فحص المعلم
$teacher = Teacher::find(70);
if ($teacher) {
    echo "✅ تم العثور على المعلم:" . PHP_EOL;
    echo "   - ID: {$teacher->id}" . PHP_EOL;
    echo "   - الاسم: {$teacher->name}" . PHP_EOL;
    echo "   - المسجد ID: {$teacher->mosque_id}" . PHP_EOL;
    echo "   - الحلقة ID: {$teacher->quran_circle_id}" . PHP_EOL;
    echo "   - نشط: " . ($teacher->is_active ? 'نعم' : 'لا') . PHP_EOL;
    echo "   - نوع المهمة: {$teacher->task_type}" . PHP_EOL;
    echo "   - نوع الحلقة: {$teacher->circle_type}" . PHP_EOL;
    echo "   - وقت العمل: {$teacher->work_time}" . PHP_EOL;
    
    // فحص المسجد
    if ($teacher->mosque) {
        echo "✅ معلومات المسجد:" . PHP_EOL;
        echo "   - اسم المسجد: {$teacher->mosque->name}" . PHP_EOL;
        echo "   - المنطقة: {$teacher->mosque->neighborhood}" . PHP_EOL;
    } else {
        echo "❌ المسجد غير موجود للمعلم" . PHP_EOL;
    }
    
    // فحص الحلقة
    if ($teacher->quranCircle) {
        echo "✅ معلومات الحلقة:" . PHP_EOL;
        echo "   - اسم الحلقة: {$teacher->quranCircle->name}" . PHP_EOL;
        echo "   - الفترة: {$teacher->quranCircle->period}" . PHP_EOL;
        echo "   - السعة: {$teacher->quranCircle->capacity}" . PHP_EOL;
        echo "   - الطلاب الحاليين: {$teacher->quranCircle->current_students}" . PHP_EOL;
        echo "   - نشطة: " . ($teacher->quranCircle->is_active ? 'نعم' : 'لا') . PHP_EOL;
        echo "   - مسجد الحلقة ID: {$teacher->quranCircle->mosque_id}" . PHP_EOL;
        
        // التحقق من تطابق المسجد
        if ($teacher->mosque_id == $teacher->quranCircle->mosque_id) {
            echo "✅ المسجد متطابق بين المعلم والحلقة" . PHP_EOL;
        } else {
            echo "❌ عدم تطابق المسجد! معلم: {$teacher->mosque_id}, حلقة: {$teacher->quranCircle->mosque_id}" . PHP_EOL;
        }
    } else {
        echo "❌ الحلقة غير موجودة للمعلم" . PHP_EOL;
    }
    
    echo PHP_EOL . "🔍 فحص جميع الحلقات في المسجد ID: {$teacher->mosque_id}" . PHP_EOL;
    $circles = QuranCircle::where('mosque_id', $teacher->mosque_id)->get();
    
    foreach ($circles as $circle) {
        echo "   - حلقة ID: {$circle->id}, الاسم: {$circle->name}, نشطة: " . ($circle->is_active ? 'نعم' : 'لا') . PHP_EOL;
        
        // فحص المعلمين في هذه الحلقة
        $teachers = Teacher::where('quran_circle_id', $circle->id)
                          ->where('is_active', true)
                          ->get();
        
        echo "     معلمين في هذه الحلقة: " . $teachers->count() . PHP_EOL;
        foreach ($teachers as $t) {
            echo "       - معلم ID: {$t->id}, الاسم: {$t->name}" . PHP_EOL;
        }
    }
    
} else {
    echo "❌ المعلم ID: 70 غير موجود" . PHP_EOL;
}

echo PHP_EOL . "🔍 فحص المسجد ID: 16" . PHP_EOL;
$mosque = Mosque::find(16);
if ($mosque) {
    echo "✅ معلومات المسجد:" . PHP_EOL;
    echo "   - اسم المسجد: {$mosque->name}" . PHP_EOL;
    echo "   - المنطقة: {$mosque->neighborhood}" . PHP_EOL;
    
    // فحص جميع المعلمين في هذا المسجد
    $teachersInMosque = Teacher::where('mosque_id', 16)->where('is_active', true)->get();
    echo "   - عدد المعلمين النشطين: " . $teachersInMosque->count() . PHP_EOL;
    
    foreach ($teachersInMosque as $t) {
        echo "     - معلم ID: {$t->id}, الاسم: {$t->name}, حلقة ID: {$t->quran_circle_id}" . PHP_EOL;
    }
} else {
    echo "❌ المسجد ID: 16 غير موجود" . PHP_EOL;
}
