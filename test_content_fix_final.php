<?php

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CurriculumPlan;

echo "===== اختبار إصلاح مشكلة حقل content في الخطط التعليمية =====\n\n";

// اختبار 1: إنشاء خطة بالسور المتعددة
echo "1. اختبار إنشاء خطة بالسور المتعددة:\n";
try {
    $multiSurahPlan = new CurriculumPlan();
    $multiSurahPlan->curriculum_id = 1;
    $multiSurahPlan->curriculum_level_id = 1;
    $multiSurahPlan->plan_number = 'TEST-MULTI-001';
    $multiSurahPlan->title = 'اختبار السور المتعددة';
    $multiSurahPlan->plan_type = 'حفظ';
    $multiSurahPlan->content_type = 'quran';
    $multiSurahPlan->range_type = 'multi_surah';
    $multiSurahPlan->start_surah_number = 1;
    $multiSurahPlan->start_surah_verse = 1;
    $multiSurahPlan->end_surah_number = 2;
    $multiSurahPlan->end_surah_verse = 10;
    
    echo "   - قبل الحفظ: content = " . ($multiSurahPlan->content ?? 'NULL') . "\n";
    
    $multiSurahPlan->save();
    
    echo "   ✅ تم حفظ خطة السور المتعددة بنجاح!\n";
    echo "   - بعد الحفظ: content = " . ($multiSurahPlan->content ? 'موجود' : 'NULL') . "\n";
    echo "   - ID: " . $multiSurahPlan->id . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء خطة السور المتعددة: " . $e->getMessage() . "\n\n";
}

// اختبار 2: إنشاء خطة بالسورة الواحدة
echo "2. اختبار إنشاء خطة بالسورة الواحدة:\n";
try {
    $singleSurahPlan = new CurriculumPlan();
    $singleSurahPlan->curriculum_id = 1;
    $singleSurahPlan->curriculum_level_id = 1;
    $singleSurahPlan->plan_number = 'TEST-SINGLE-001';
    $singleSurahPlan->title = 'اختبار السورة الواحدة';
    $singleSurahPlan->plan_type = 'حفظ';
    $singleSurahPlan->content_type = 'quran';
    $singleSurahPlan->range_type = 'single_surah';
    $singleSurahPlan->surah_number = 2;
    $singleSurahPlan->start_verse = 1;
    $singleSurahPlan->end_verse = 50;
    
    echo "   - قبل الحفظ: content = " . ($singleSurahPlan->content ?? 'NULL') . "\n";
    
    $singleSurahPlan->save();
    
    echo "   ✅ تم حفظ خطة السورة الواحدة بنجاح!\n";
    echo "   - بعد الحفظ: content = " . ($singleSurahPlan->content ? 'موجود' : 'NULL') . "\n";
    echo "   - ID: " . $singleSurahPlan->id . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء خطة السورة الواحدة: " . $e->getMessage() . "\n\n";
}

// اختبار 3: إنشاء خطة باستخدام create (مثل BulkPlansCreator)
echo "3. اختبار إنشاء خطة باستخدام create (مثل BulkPlansCreator):\n";
try {
    $bulkPlan = CurriculumPlan::create([
        'curriculum_id' => 1,
        'curriculum_level_id' => 1,
        'plan_number' => 'TEST-BULK-001',
        'title' => 'اختبار الإنشاء المجمع',
        'plan_type' => 'حفظ',
        'content_type' => 'text',
        'content' => 'هذا محتوى تجريبي',
    ]);
    
    echo "   ✅ تم إنشاء الخطة بالطريقة المجمعة بنجاح!\n";
    echo "   - ID: " . $bulkPlan->id . "\n";
    echo "   - content: " . $bulkPlan->content . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في الإنشاء المجمع: " . $e->getMessage() . "\n\n";
}

echo "===== انتهى الاختبار =====\n";
