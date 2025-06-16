<?php

require 'vendor/autoload.php';

use App\Services\QuranService;
use App\Models\CurriculumPlan;

// Create Laravel app instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Multi-Surah Support...\n\n";

$quranService = new QuranService();

// Test 1: Multi-surah verse calculation
echo "Test 1: Multi-surah verse calculation\n";
echo "من سورة الفاتحة إلى سورة البقرة:\n";
$totalVerses = $quranService->calculateMultiSurahVerseCount(1, 2, 1, null);
echo "Total verses: $totalVerses\n\n";

// Test 2: Multi-surah content formatting
echo "Test 2: Multi-surah content formatting\n";
$formatted = $quranService->formatMultiSurahContent(1, 2, 1, null);
echo "Formatted: $formatted\n\n";

// Test 3: Multi-surah range validation
echo "Test 3: Multi-surah range validation\n";
$isValid = $quranService->validateMultiSurahRange(1, 2, 1, null);
echo "Valid range: " . ($isValid ? 'Yes' : 'No') . "\n\n";

// Test 4: Multi-surah range summary
echo "Test 4: Multi-surah range summary\n";
$summary = $quranService->getMultiSurahRangeSummary(1, 3, 1, null);
print_r($summary);

// Test 5: Creating a test curriculum plan with multi-surah
echo "\nTest 5: Creating curriculum plan with multi-surah support\n";
try {
    $plan = new CurriculumPlan();
    $plan->curriculum_id = 1; // Assuming curriculum exists
    $plan->name = 'Test Multi-Surah Plan';
    $plan->plan_type = 'الدرس';
    $plan->content_type = 'quran';
    $plan->range_type = 'multi_surah';
    $plan->start_surah_number = 1;
    $plan->end_surah_number = 2;
    $plan->start_surah_verse = 1;
    $plan->end_surah_verse = null;
    $plan->content = 'Test multi-surah content';
    $plan->is_active = true;
    
    // This should trigger the boot method to calculate verses automatically
    echo "Plan total verses (before save): " . ($plan->total_verses_calculated ?? 'null') . "\n";
    
    // We won't actually save to avoid DB issues in testing
    // $plan->save();
    
    echo "Multi-surah plan created successfully (test mode)\n";
    
} catch (Exception $e) {
    echo "Error creating plan: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
