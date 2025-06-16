<?php

require_once 'vendor/autoload.php';

use App\Models\CurriculumPlan;
use App\Models\Student;
use App\Models\QuranCircle;
use Illuminate\Support\Facades\DB;

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "اختبار إصلاح حقل content في الخطط التعليمية...\n\n";

try {
    // إنشاء خطة تعليمية جديدة للسور المتعددة
    $curriculumPlan = CurriculumPlan::create([
        'student_id' => 1, // افتراض وجود طالب برقم 1
        'range_type' => 'multi_surah',
        'start_surah_number' => 1,
        'end_surah_number' => 3,
        'start_surah_verse' => 1,
        'end_surah_verse' => 10,
        'plan_type' => 'memorization',
        'target_date' => now()->addMonth(),
        'notes' => 'اختبار السور المتعددة'
    ]);

    echo "✅ تم إنشاء الخطة بنجاح!\n";
    echo "معرف الخطة: " . $curriculumPlan->id . "\n";
    echo "نوع النطاق: " . $curriculumPlan->range_type . "\n";
    echo "المحتوى: " . ($curriculumPlan->content ? "موجود" : "غير موجود") . "\n";
    echo "المحتوى المنسق للسور المتعددة: " . ($curriculumPlan->multi_surah_formatted_content ? "موجود" : "غير موجود") . "\n";
    
    if ($curriculumPlan->content) {
        echo "محتوى الحقل content: " . $curriculumPlan->content . "\n";
    }
    
    if ($curriculumPlan->multi_surah_formatted_content) {
        echo "محتوى الحقل multi_surah_formatted_content: " . $curriculumPlan->multi_surah_formatted_content . "\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الخطة: " . $e->getMessage() . "\n";
    echo "تفاصيل الخطأ: " . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// اختبار السورة الواحدة أيضاً
try {
    $singleSurahPlan = CurriculumPlan::create([
        'student_id' => 1,
        'range_type' => 'single_surah',
        'surah_number' => 2,
        'from_verse' => 1,
        'to_verse' => 50,
        'plan_type' => 'memorization',
        'target_date' => now()->addMonth(),
        'notes' => 'اختبار السورة الواحدة'
    ]);

    echo "✅ تم إنشاء خطة السورة الواحدة بنجاح!\n";
    echo "معرف الخطة: " . $singleSurahPlan->id . "\n";
    echo "نوع النطاق: " . $singleSurahPlan->range_type . "\n";
    echo "المحتوى: " . ($singleSurahPlan->content ? "موجود" : "غير موجود") . "\n";
    
    if ($singleSurahPlan->content) {
        echo "محتوى الحقل content: " . $singleSurahPlan->content . "\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ في إنشاء خطة السورة الواحدة: " . $e->getMessage() . "\n";
}

echo "\nانتهى الاختبار.\n";
