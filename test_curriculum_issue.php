<?php

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "اختبار إنشاء خطة منهج للسور المتعددة...\n";
    
    // إنشاء خطة منهج جديدة للسور المتعددة
    $plan = new \App\Models\CurriculumPlan();
    $plan->content_type = 'quran';
    $plan->range_type = 'multi_surah';
    $plan->start_surah_number = 2; // سورة البقرة
    $plan->start_surah_verse = 1;
    $plan->end_surah_number = 3; // سورة آل عمران
    $plan->end_surah_verse = 10;
    $plan->plan_type = 'حفظ';
    $plan->student_id = 1; // افتراض وجود طالب بـ ID = 1
    
    echo "البيانات قبل الحفظ:\n";
    echo "content: " . ($plan->content ?? 'NULL') . "\n";
    echo "multi_surah_formatted_content: " . ($plan->multi_surah_formatted_content ?? 'NULL') . "\n";
    
    // محاولة حفظ الخطة
    $plan->save();
    
    echo "تم حفظ الخطة بنجاح!\n";
    echo "content بعد الحفظ: " . ($plan->content ?? 'NULL') . "\n";
    echo "multi_surah_formatted_content بعد الحفظ: " . ($plan->multi_surah_formatted_content ?? 'NULL') . "\n";
    
} catch (\Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}
