<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\Curriculum;
use App\Models\CurriculumLevel;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;
use App\Models\CurriculumPlan;

try {
    echo "=== اختبار إضافة منهج لطالب ===\n\n";
    
    // التحقق من البيانات الموجودة
    $student = Student::first();
    $curriculum = Curriculum::first();
    
    echo "الطالب: " . ($student ? $student->name : 'غير موجود') . "\n";
    echo "المنهج: " . ($curriculum ? $curriculum->name : 'غير موجود') . "\n";
    
    if (!$student || !$curriculum) {
        echo "خطأ: لا توجد بيانات كافية للاختبار\n";
        exit;
    }
    
    // التحقق من مستويات المنهج
    $curriculumLevel = CurriculumLevel::where('curriculum_id', $curriculum->id)->first();
    echo "مستوى المنهج: " . ($curriculumLevel ? $curriculumLevel->name : 'غير موجود') . "\n";
    
    if (!$curriculumLevel) {
        echo "خطأ: لا يوجد مستوى للمنهج\n";
        exit;
    }
    
    // التحقق من خطط المنهج
    $curriculumPlan = CurriculumPlan::where('curriculum_level_id', $curriculumLevel->id)->first();
    echo "خطة المنهج: " . ($curriculumPlan ? $curriculumPlan->name : 'غير موجودة') . "\n";
    
    if (!$curriculumPlan) {
        // إنشاء خطة تجريبية
        echo "إنشاء خطة منهج تجريبية...\n";
        $curriculumPlan = CurriculumPlan::create([
            'curriculum_level_id' => $curriculumLevel->id,
            'name' => 'خطة تجريبية',
            'description' => 'خطة للاختبار',
            'sequence_order' => 1,
            'surah_names' => ['الفاتحة'],
            'from_verse' => 1,
            'to_verse' => 7,
            'duration_weeks' => 1,
            'difficulty_level' => 'سهل'
        ]);
        echo "تم إنشاء خطة المنهج: " . $curriculumPlan->name . "\n";
    }
    
    echo "\n=== محاولة إضافة منهج للطالب ===\n";
    
    // التحقق من وجود منهج للطالب مسبقاً
    $existingCurriculum = StudentCurriculum::where('student_id', $student->id)
                                         ->where('curriculum_id', $curriculum->id)
                                         ->first();
    
    if ($existingCurriculum) {
        echo "الطالب لديه منهج مسبقاً، سنستخدمه...\n";
        $studentCurriculum = $existingCurriculum;
    } else {
        // إنشاء منهج جديد للطالب
        echo "إنشاء منهج جديد للطالب...\n";
        $studentCurriculum = StudentCurriculum::create([
            'student_id' => $student->id,
            'curriculum_id' => $curriculum->id,
            'curriculum_level_id' => $curriculumLevel->id,
            'teacher_id' => 1, // افتراض وجود معلم برقم 1
            'start_date' => now(),
            'status' => 'قيد التنفيذ',
            'completion_percentage' => 0,
            'notes' => 'منهج اختبار'
        ]);
        echo "تم إنشاء منهج الطالب بنجاح!\n";
    }
    
    echo "\n=== محاولة إضافة تقدم المنهج ===\n";
    
    // التحقق من وجود تقدم مسبق
    $existingProgress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)
                                                ->where('curriculum_plan_id', $curriculumPlan->id)
                                                ->first();
    
    if ($existingProgress) {
        echo "يوجد تقدم مسبق للطالب في هذه الخطة\n";
        echo "حالة التقدم: " . $existingProgress->status . "\n";
        echo "نسبة الإكمال: " . $existingProgress->completion_percentage . "%\n";
    } else {
        // إنشاء تقدم جديد
        echo "إنشاء تقدم جديد للطالب...\n";
        $progress = StudentCurriculumProgress::create([
            'student_curriculum_id' => $studentCurriculum->id,
            'curriculum_plan_id' => $curriculumPlan->id,
            'start_date' => now(),
            'status' => 'قيد التنفيذ',
            'completion_percentage' => 0,
            'teacher_notes' => 'بدء تقدم جديد'
        ]);
        
        echo "تم إنشاء تقدم المنهج بنجاح!\n";
        echo "معرف التقدم: " . $progress->id . "\n";
        echo "حالة التقدم: " . $progress->status . "\n";
    }
    
    echo "\n=== نتائج الاختبار ===\n";
    echo "✅ تم إضافة المنهج للطالب بنجاح دون أخطاء!\n";
    echo "عدد مناهج الطالب: " . StudentCurriculum::where('student_id', $student->id)->count() . "\n";
    echo "عدد تقدم المناهج: " . StudentCurriculumProgress::count() . "\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "السطر: " . $e->getLine() . "\n";
    echo "الملف: " . $e->getFile() . "\n";
    echo "\nتفاصيل الخطأ:\n" . $e->getTraceAsString() . "\n";
}
