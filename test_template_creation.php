<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CurriculumTemplateService;

echo "=== اختبار إنشاء القوالب الجاهزة ===\n\n";

try {
    echo "1. اختبار إنشاء قالب ختم القرآن في سنة:\n";
    $curriculum1 = CurriculumTemplateService::createFromTemplate('yearly_completion');
    echo "   ✅ تم إنشاء القالب بنجاح!\n";
    echo "   - الاسم: " . $curriculum1->name . "\n";
    echo "   - المعرف: " . $curriculum1->id . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n\n";
}

try {
    echo "2. اختبار إنشاء قالب الحفظ السريع:\n";
    $curriculum2 = CurriculumTemplateService::createFromTemplate('fast_memorization');
    echo "   ✅ تم إنشاء القالب بنجاح!\n";
    echo "   - الاسم: " . $curriculum2->name . "\n";
    echo "   - المعرف: " . $curriculum2->id . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n\n";
}

try {
    echo "3. اختبار إنشاء قالب بـ customName:\n";
    $curriculum3 = CurriculumTemplateService::createFromTemplate('yearly_completion', 'منهج مخصص للاختبار');
    echo "   ✅ تم إنشاء القالب المخصص بنجاح!\n";
    echo "   - الاسم: " . $curriculum3->name . "\n";
    echo "   - المعرف: " . $curriculum3->id . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ خطأ: " . $e->getMessage() . "\n\n";
}

echo "=== انتهى الاختبار ===\n";
