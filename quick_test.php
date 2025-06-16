<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\QuranService;

echo "=== اختبار سريع للوظائف الأساسية ===\n\n";

$quranService = app(QuranService::class);

// Test basic functionality
echo "1. حساب الفاتحة كاملة: ";
$result1 = $quranService->calculateMultiSurahVerseCount(1, 1, 1, null);
echo "{$result1} آية\n";

echo "2. حساب من الفاتحة إلى البقرة: ";
$result2 = $quranService->calculateMultiSurahVerseCount(1, 1, 2, null);
echo "{$result2} آية\n";

echo "3. تنسيق الفاتحة كاملة: ";
$format1 = $quranService->formatMultiSurahContent(1, 1, 1, null);
echo "{$format1}\n";

echo "4. تحقق صحة النطاق: ";
$valid = $quranService->validateMultiSurahRange(1, 1, 2, null);
echo ($valid ? 'صحيح' : 'خطأ') . "\n";

echo "\n=== تم ===\n";
