<?php

// Minimal test without Laravel bootstrap
echo "=== Test Multi-Surah Calculations ===\n\n";

// Simulate QuranService calculations manually
$surahs = [
    1 => ['name' => 'الفاتحة', 'verses' => 7],
    2 => ['name' => 'البقرة', 'verses' => 286],
    3 => ['name' => 'آل عمران', 'verses' => 200],
    // Add more as needed
];

function calculateMultiSurahVerses($startSurah, $startVerse, $endSurah, $endVerse, $surahs) {
    if ($endVerse === null) {
        $endVerse = $surahs[$endSurah]['verses'];
    }
    
    if ($startSurah === $endSurah) {
        return $endVerse - $startVerse + 1;
    }
    
    $totalVerses = 0;
    
    // First surah (from start verse to end)
    $totalVerses += $surahs[$startSurah]['verses'] - $startVerse + 1;
    
    // Middle surahs (complete)
    for ($i = $startSurah + 1; $i < $endSurah; $i++) {
        $totalVerses += $surahs[$i]['verses'];
    }
    
    // Last surah (from start to end verse)
    $totalVerses += $endVerse;
    
    return $totalVerses;
}

// Test cases
echo "1. Al-Fatiha complete (1:1 to 1:end): ";
$result1 = calculateMultiSurahVerses(1, 1, 1, null, $surahs);
echo "$result1 verses\n";

echo "2. Al-Fatiha to Al-Baqarah (1:1 to 2:end): ";
$result2 = calculateMultiSurahVerses(1, 1, 2, null, $surahs);
echo "$result2 verses\n";

echo "3. Al-Fatiha to Al-Imran (1:1 to 3:end): ";
$result3 = calculateMultiSurahVerses(1, 1, 3, null, $surahs);
echo "$result3 verses\n";

echo "4. Partial range (1:3 to 2:100): ";
$result4 = calculateMultiSurahVerses(1, 3, 2, 100, $surahs);
echo "$result4 verses\n";

echo "\nExpected calculations:\n";
echo "- Al-Fatiha complete: 7 verses\n";
echo "- Al-Fatiha + Al-Baqarah: 7 + 286 = 293 verses\n";
echo "- Al-Fatiha + Al-Baqarah + Al-Imran: 7 + 286 + 200 = 493 verses\n";
echo "- Partial (1:3 to 2:100): (7-3+1) + 100 = 5 + 100 = 105 verses\n";

echo "\n=== Manual verification ===\n";
echo "Results match expected: " . (
    $result1 === 7 && 
    $result2 === 293 && 
    $result3 === 493 && 
    $result4 === 105 ? "YES" : "NO"
) . "\n";

echo "\n=== Test Complete ===\n";
