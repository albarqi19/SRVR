<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CurriculumPlan;

echo "التحقق من السجلات التي لا تحتوي على content_type...\n";

// تحقق من السجلات التي لا تحتوي على content_type
$plansWithoutContentType = CurriculumPlan::whereNull('content_type')->count();
echo "عدد الخطط بدون content_type: {$plansWithoutContentType}\n";

if ($plansWithoutContentType > 0) {
    // تحديث جميع السجلات التي لا تحتوي على content_type
    $updated = CurriculumPlan::whereNull('content_type')->update([
        'content_type' => 'text',
        'range_type' => 'single_surah'
    ]);
    
    echo "تم تحديث {$updated} سجل بنجاح\n";
} else {
    echo "جميع السجلات تحتوي على content_type\n";
}

// تحقق من السجلات التي لا تحتوي على range_type
$plansWithoutRangeType = CurriculumPlan::whereNull('range_type')->count();
echo "عدد الخطط بدون range_type: {$plansWithoutRangeType}\n";

if ($plansWithoutRangeType > 0) {
    $updated = CurriculumPlan::whereNull('range_type')->update([
        'range_type' => 'single_surah'
    ]);
    
    echo "تم تحديث {$updated} سجل بـ range_type\n";
}

echo "تم الانتهاء من إصلاح البيانات\n";
