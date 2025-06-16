<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QuranService;
use App\Models\CurriculumPlan;

echo "=== اختبار وظائف السور المتعددة ===\n\n";

$quranService = app(QuranService::class);

// اختبار 1: حساب عدد الآيات لنطاق سور متعددة
echo "1. اختبار حساب عدد الآيات للسور المتعددة:\n";
$totalVerses = $quranService->calculateMultiSurahVerseCount(1, 3, 1, null);
echo "من سورة الفاتحة إلى سورة آل عمران: {$totalVerses} آية\n\n";

// اختبار 2: تنسيق محتوى السور المتعددة
echo "2. اختبار تنسيق المحتوى:\n";
$formatted = $quranService->formatMultiSurahContent(1, 3, 1, null);
echo "المحتوى المنسق: {$formatted}\n\n";

// اختبار 3: التحقق من صحة النطاق
echo "3. اختبار التحقق من صحة النطاق:\n";
$isValid = $quranService->validateMultiSurahRange(1, 3, 1, null);
echo "هل النطاق صحيح؟ " . ($isValid ? 'نعم' : 'لا') . "\n\n";

// اختبار 4: الحصول على ملخص النطاق
echo "4. اختبار ملخص النطاق:\n";
$summary = $quranService->getMultiSurahRangeSummary(1, 3, 1, null);
echo "عدد السور: " . $summary['surahs_count'] . "\n";
echo "إجمالي الآيات: " . $summary['total_verses'] . "\n";
echo "صحيح: " . ($summary['is_valid'] ? 'نعم' : 'لا') . "\n\n";

// اختبار 5: إنشاء خطة منهج جديدة باستخدام السور المتعددة
echo "5. اختبار إنشاء خطة منهج جديدة:\n";
try {
    $plan = new CurriculumPlan([
        'name' => 'اختبار السور المتعددة',
        'content' => 'خطة اختبار',
        'content_type' => 'quran',
        'range_type' => 'multi_surah',
        'start_surah_number' => 1,
        'end_surah_number' => 3,
        'start_surah_verse' => 1,
        'end_surah_verse' => null,
        'plan_type' => 'الدرس',
        'is_active' => true,
        'curriculum_id' => 1, // افتراض وجود منهج بالرقم 1
    ]);
    
    // حساب القيم مباشرة من الخدمة
    $directCalculation = $quranService->calculateMultiSurahVerseCount(1, 1, 3, null);
    $directFormatting = $quranService->formatMultiSurahContent(1, 1, 3, null);
    
    echo "الحساب المباشر من الخدمة: {$directCalculation} آية\n";
    echo "التنسيق المباشر: {$directFormatting}\n";
    
    // التحقق من الحقول المحسوبة دون حفظ (ستكون فارغة)
    echo "عدد الآيات من النموذج (قبل الحفظ): " . ($plan->total_verses ?? 'غير محسوب') . "\n";
    echo "المحتوى المنسق من النموذج (قبل الحفظ): " . ($plan->multi_surah_formatted_content ?? 'غير منسق') . "\n";
    echo "تم إنشاء النموذج بنجاح (بدون حفظ)\n\n";
    
} catch (Exception $e) {
    echo "خطأ في إنشاء الخطة: " . $e->getMessage() . "\n\n";
}

echo "=== انتهى الاختبار ===\n";
