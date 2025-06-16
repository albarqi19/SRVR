<?php

require_once 'vendor/autoload.php';

use App\Models\QuranCircle;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\CircleGroup;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 اختبار الفرق بين الحلقات الفرعية والرئيسية في API\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// البحث عن حلقة جماعية لديها حلقات فرعية
$groupCircle = QuranCircle::where('circle_type', 'حلقة جماعية')
    ->whereHas('circleGroups')
    ->with(['circleGroups.teacher', 'circleGroups.students'])
    ->first();

if (!$groupCircle) {
    echo "❌ لا توجد حلقة جماعية لديها حلقات فرعية\n";
    echo "دعني أنشئ مثال للتوضيح...\n\n";
    
    // إنشاء مثال سريع
    $circle = QuranCircle::first();
    if ($circle) {
        echo "📋 إنشاء حلقة فرعية كمثال:\n";
        $teacher = Teacher::first();
        
        if ($teacher) {
            // إنشاء حلقة فرعية
            $circleGroup = CircleGroup::create([
                'name' => 'حلقة فرعية تجريبية',
                'quran_circle_id' => $circle->id,
                'teacher_id' => $teacher->id,
                'status' => 'نشطة'
            ]);
            
            echo "✅ تم إنشاء حلقة فرعية: {$circleGroup->name}\n";
            echo "🔗 مرتبطة بالحلقة الرئيسية: {$circle->name}\n";
            echo "👨‍🏫 المعلم: {$teacher->name}\n\n";
            
            $groupCircle = $circle->fresh(['circleGroups.teacher', 'circleGroups.students']);
        }
    }
}

if ($groupCircle) {
    echo "📋 اختبار الحلقة: {$groupCircle->name}\n";
    echo "🏛️ المسجد: " . ($groupCircle->mosque->name ?? 'غير محدد') . "\n";
    echo "📊 نوع الحلقة: {$groupCircle->circle_type}\n\n";
    
    // عرض الحلقات الفرعية
    echo "🔍 الحلقات الفرعية:\n";
    foreach ($groupCircle->circleGroups as $subGroup) {
        echo "   📌 {$subGroup->name}\n";
        echo "      👨‍🏫 المعلم: " . ($subGroup->teacher->name ?? 'غير محدد') . "\n";
        echo "      👥 عدد الطلاب: " . $subGroup->students->count() . "\n";
        
        // عرض الطلاب في هذه الحلقة الفرعية
        foreach ($subGroup->students as $student) {
            echo "         - {$student->name} (circle_group_id: {$student->circle_group_id})\n";
        }
        echo "\n";
    }
    
    // الآن لنختبر كيف يعمل API الحالي
    echo "🧪 اختبار API الحالي (قبل الإصلاح):\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    // الطريقة القديمة - فقط الطلاب المرتبطين مباشرة بالحلقة الرئيسية
    $directStudents = Student::where('quran_circle_id', $groupCircle->id)->get();
    echo "📊 الطلاب المرتبطين مباشرة بالحلقة الرئيسية: " . $directStudents->count() . "\n";
    
    // الطريقة الجديدة - تشمل الطلاب من الحلقات الفرعية
    $allStudents = Student::where(function($query) use ($groupCircle) {
        // الطلاب المرتبطين مباشرة بالحلقة الرئيسية
        $query->where('quran_circle_id', $groupCircle->id)
              // أو الطلاب المرتبطين بالحلقات الفرعية
              ->orWhereHas('circleGroup', function($subQuery) use ($groupCircle) {
                  $subQuery->where('quran_circle_id', $groupCircle->id);
              });
    })->get();
    
    echo "📊 إجمالي الطلاب (مع الحلقات الفرعية): " . $allStudents->count() . "\n\n";
    
    echo "🎯 النتيجة:\n";
    if ($allStudents->count() > $directStudents->count()) {
        echo "✅ هناك فرق! الحلقات الفرعية تحتوي على طلاب إضافيين\n";
        echo "📈 عدد الطلاب المفقودين في API القديم: " . ($allStudents->count() - $directStudents->count()) . "\n";
    } else {
        echo "ℹ️ لا يوجد فرق في هذا المثال - لا توجد طلاب في الحلقات الفرعية\n";
    }
    
    // مثال عملي: لنجرب مع معلم محدد
    echo "\n🧪 اختبار مع معلم محدد:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $teacher = $groupCircle->circleGroups->first()?->teacher;
    if ($teacher) {
        echo "👨‍🏫 المعلم: {$teacher->name}\n";
        
        // API القديم - فقط من الحلقة الرئيسية
        $oldApiStudents = Student::where('quran_circle_id', $teacher->quran_circle_id)->get();
        echo "📊 API القديم - طلاب الحلقة الرئيسية: " . $oldApiStudents->count() . "\n";
        
        // API الجديد - يشمل الحلقات الفرعية
        $newApiStudents = Student::where(function($query) use ($teacher) {
            if ($teacher->quran_circle_id) {
                $query->where('quran_circle_id', $teacher->quran_circle_id)
                      ->orWhereHas('circleGroup', function($subQuery) use ($teacher) {
                          $subQuery->where('quran_circle_id', $teacher->quran_circle_id)
                                   ->where('teacher_id', $teacher->id);
                      });
            }
        })->get();
        
        echo "📊 API الجديد - مع الحلقات الفرعية: " . $newApiStudents->count() . "\n";
        
        if ($newApiStudents->count() > $oldApiStudents->count()) {
            echo "✅ الإصلاح مطلوب! هناك طلاب مفقودين في API القديم\n";
        } else {
            echo "ℹ️ في هذا المثال لا يوجد فرق\n";
        }
    }
    
} else {
    echo "❌ لم أجد حلقة جماعية مناسبة للاختبار\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 انتهى الاختبار\n";
