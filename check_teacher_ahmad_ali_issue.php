<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use App\Models\Mosque;

echo "🔍 فحص مشكلة المعلم 'أحمد علي' وطلابه:\n";
echo "===================================================\n\n";

try {
    // البحث عن المعلم "أحمد علي"
    $teachers = Teacher::where('name', 'LIKE', '%أحمد علي%')->get();
    
    echo "👨‍🏫 المعلمين المطابقين لـ 'أحمد علي':\n";
    foreach ($teachers as $teacher) {
        echo "- ID: {$teacher->id}\n";
        echo "  الاسم: {$teacher->name}\n";
        echo "  رقم الهوية: {$teacher->identity_number}\n";
        echo "  المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد') . "\n";
        echo "  الحلقة الرئيسية: " . ($teacher->quranCircle ? $teacher->quranCircle->name : 'غير محدد') . "\n";
        echo "\n";
    }
    
    // البحث عن الحلقة الفرعية "تجربة653"
    echo "🔍 البحث عن الحلقة الفرعية 'تجربة653':\n";
    $circleGroup = CircleGroup::where('name', 'تجربة653')->first();
    
    if ($circleGroup) {
        echo "✅ تم العثور على الحلقة الفرعية:\n";
        echo "- ID: {$circleGroup->id}\n";
        echo "- الاسم: {$circleGroup->name}\n";
        echo "- المعلم: " . ($circleGroup->teacher ? $circleGroup->teacher->name : 'غير محدد') . "\n";
        echo "- الحلقة الرئيسية: " . ($circleGroup->quranCircle ? $circleGroup->quranCircle->name : 'غير محدد') . "\n";
        echo "- المسجد: " . ($circleGroup->quranCircle && $circleGroup->quranCircle->mosque ? $circleGroup->quranCircle->mosque->name : 'غير محدد') . "\n";
        echo "\n";
        
        // البحث عن الطلاب في هذه الحلقة الفرعية
        echo "👥 الطلاب في الحلقة الفرعية 'تجربة653':\n";
        $students = Student::where('circle_group_id', $circleGroup->id)->get();
        
        if ($students->count() > 0) {
            foreach ($students as $student) {
                echo "- ID: {$student->id}\n";
                echo "  الاسم: {$student->name}\n";
                echo "  رقم الهوية: {$student->identity_number}\n";
                echo "  نشط: " . ($student->is_active ? 'نعم' : 'لا') . "\n";
                echo "  تاريخ التسجيل: " . ($student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : 'غير محدد') . "\n";
                echo "\n";
            }
        } else {
            echo "❌ لا يوجد طلاب في هذه الحلقة الفرعية\n\n";
        }
        
        // فحص المعلم المسؤول عن هذه الحلقة الفرعية
        if ($circleGroup->teacher) {
            $teacherId = $circleGroup->teacher->id;
            echo "🔍 فحص جميع الحلقات الفرعية للمعلم '{$circleGroup->teacher->name}' (ID: {$teacherId}):\n";
            
            $allCircleGroups = CircleGroup::where('teacher_id', $teacherId)->with(['quranCircle.mosque', 'students'])->get();
            
            foreach ($allCircleGroups as $group) {
                echo "- الحلقة الفرعية: {$group->name} (ID: {$group->id})\n";
                echo "  الحلقة الرئيسية: " . ($group->quranCircle ? $group->quranCircle->name : 'غير محدد') . "\n";
                echo "  المسجد: " . ($group->quranCircle && $group->quranCircle->mosque ? $group->quranCircle->mosque->name : 'غير محدد') . "\n";
                echo "  عدد الطلاب: {$group->students->count()}\n";
                
                if ($group->students->count() > 0) {
                    echo "  الطلاب:\n";
                    foreach ($group->students as $student) {
                        echo "    * {$student->name} (ID: {$student->id})\n";
                    }
                }
                echo "\n";
            }
        }
        
    } else {
        echo "❌ لم يتم العثور على الحلقة الفرعية 'تجربة653'\n\n";
    }
    
    // فحص المسجد "سعد"
    echo "🕌 فحص مسجد 'سعد':\n";
    $mosque = Mosque::where('name', 'سعد')->first();
    
    if ($mosque) {
        echo "✅ تم العثور على المسجد:\n";
        echo "- ID: {$mosque->id}\n";
        echo "- الاسم: {$mosque->name}\n";
        echo "- الحي: {$mosque->neighborhood}\n";
        echo "\n";
        
        // جلب الحلقات في هذا المسجد
        echo "📋 الحلقات في مسجد 'سعد':\n";
        $circles = QuranCircle::where('mosque_id', $mosque->id)->with(['students', 'circleGroups.teacher', 'circleGroups.students'])->get();
        
        foreach ($circles as $circle) {
            echo "- الحلقة: {$circle->name} (ID: {$circle->id})\n";
            echo "  عدد الطلاب المباشرين: {$circle->students->count()}\n";
            echo "  عدد الحلقات الفرعية: {$circle->circleGroups->count()}\n";
            
            if ($circle->circleGroups->count() > 0) {
                echo "  الحلقات الفرعية:\n";
                foreach ($circle->circleGroups as $group) {
                    echo "    * {$group->name} - المعلم: " . ($group->teacher ? $group->teacher->name : 'غير محدد') . " - الطلاب: {$group->students->count()}\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "❌ لم يتم العثور على المسجد 'سعد'\n\n";
    }
    
    // اختبار API الحالي
    echo "🧪 اختبار API المعلم:\n";
    echo "الأمر المطلوب:\n";
    echo "curl.exe -X GET \"https://inviting-pleasantly-barnacle.ngrok-free.app/api/teachers/{teacher_id}/mosques/{mosque_id}/students\" -H \"Accept: application/json\"\n\n";
    
    if ($circleGroup && $circleGroup->teacher && $mosque) {
        $teacherId = $circleGroup->teacher->id;
        $mosqueId = $mosque->id;
        echo "للمعلم 'أحمد علي' في مسجد 'سعد':\n";
        echo "curl.exe -X GET \"https://inviting-pleasantly-barnacle.ngrok-free.app/api/teachers/{$teacherId}/mosques/{$mosqueId}/students\" -H \"Accept: application/json\"\n\n";
    }
    
    echo "✅ انتهى الفحص\n";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage() . "\n";
    echo "في الملف: " . $e->getFile() . "\n";
    echo "في السطر: " . $e->getLine() . "\n";
}
