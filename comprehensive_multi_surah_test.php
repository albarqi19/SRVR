<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QuranService;

echo "=== اختبار شامل لوظائف السور المتعددة ===\n\n";

$quranService = app(QuranService::class);

// Test 1: Single surah (Al-Fatiha complete)
echo "1. اختبار السورة الواحدة (الفاتحة كاملة):\n";
$verses1 = $quranService->calculateMultiSurahVerseCount(1, 1, 1, null);
$formatted1 = $quranService->formatMultiSurahContent(1, 1, 1, null);
echo "العدد: {$verses1} آية\n";
echo "التنسيق: {$formatted1}\n\n";

// Test 2: Multiple surahs from Al-Fatiha to Al-Baqarah
echo "2. اختبار السور المتعددة (الفاتحة إلى البقرة):\n";
$verses2 = $quranService->calculateMultiSurahVerseCount(1, 1, 2, null);
$formatted2 = $quranService->formatMultiSurahContent(1, 1, 2, null);
echo "العدد: {$verses2} آية\n";
echo "التنسيق: {$formatted2}\n\n";

// Test 3: Partial range across multiple surahs
echo "3. اختبار نطاق جزئي عبر سور متعددة (الفاتحة آية 3 إلى البقرة آية 100):\n";
$verses3 = $quranService->calculateMultiSurahVerseCount(1, 3, 2, 100);
$formatted3 = $quranService->formatMultiSurahContent(1, 3, 2, 100);
echo "العدد: {$verses3} آية\n";
echo "التنسيق: {$formatted3}\n\n";

// Test 4: Last 3 surahs
echo "4. اختبار السور الثلاث الأخيرة:\n";
$verses4 = $quranService->calculateMultiSurahVerseCount(112, 1, 114, null);
$formatted4 = $quranService->formatMultiSurahContent(112, 1, 114, null);
echo "العدد: {$verses4} آية\n";
echo "التنسيق: {$formatted4}\n\n";

// Test 5: Validation tests
echo "5. اختبارات التحقق من صحة البيانات:\n";

// Valid ranges
$valid1 = $quranService->validateMultiSurahRange(1, 1, 3, null);
echo "نطاق صحيح (1:1 إلى 3:نهاية): " . ($valid1 ? 'صحيح' : 'خطأ') . "\n";

// Invalid ranges
$invalid1 = $quranService->validateMultiSurahRange(3, 1, 1, null); // End before start
echo "نطاق خطأ (3:1 إلى 1:نهاية): " . ($invalid1 ? 'صحيح' : 'خطأ') . "\n";

$invalid2 = $quranService->validateMultiSurahRange(1, 0, 2, null); // Invalid start verse
echo "نطاق خطأ (1:0 إلى 2:نهاية): " . ($invalid2 ? 'صحيح' : 'خطأ') . "\n";

$invalid3 = $quranService->validateMultiSurahRange(1, 1, 115, null); // Invalid surah number
echo "نطاق خطأ (1:1 إلى 115:نهاية): " . ($invalid3 ? 'صحيح' : 'خطأ') . "\n\n";

// Test 6: Range summary
echo "6. اختبار ملخص النطاق التفصيلي:\n";
$summary = $quranService->getMultiSurahRangeSummary(110, 1, 114, null);
echo "من سورة: {$summary['start_surah']['name']} ({$summary['start_surah']['number']})\n";
echo "إلى سورة: {$summary['end_surah']['name']} ({$summary['end_surah']['number']})\n";
echo "عدد السور في النطاق: {$summary['surahs_count']}\n";
echo "إجمالي الآيات: {$summary['total_verses']}\n";
echo "صحة النطاق: " . ($summary['is_valid'] ? 'صحيح' : 'خطأ') . "\n";
echo "المحتوى المنسق: {$summary['formatted_content']}\n\n";

// Manual verification for last test
echo "7. التحقق اليدوي للسور الأخيرة:\n";
$surahList = $quranService->getSurahList();
$manualCount = 0;
for ($i = 110; $i <= 114; $i++) {
    $verses = $surahList[$i]['verses'];
    echo "سورة {$surahList[$i]['name']} ({$i}): {$verses} آية\n";
    $manualCount += $verses;
}
echo "المجموع اليدوي: {$manualCount} آية\n";
echo "المجموع المحسوب: {$summary['total_verses']} آية\n";
echo "التطابق: " . ($manualCount === $summary['total_verses'] ? 'نعم' : 'لا') . "\n\n";

echo "=== انتهى الاختبار الشامل ===\n";
