<?php

require_once 'vendor/autoload.php';

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CurriculumTemplateService;

echo "===== اختبار إنشاء القوالب الجاهزة =====\n\n";

// اختبار 1: إنشاء منهج ختم القرآن في سنة
echo "1. اختبار إنشاء منهج ختم القرآن في سنة:\n";
try {
    $yearlyTemplate = CurriculumTemplateService::createFromTemplate('yearly_completion');
    echo "   ✅ تم إنشاء منهج ختم القرآن بنجاح!\n";
    echo "   - ID: " . $yearlyTemplate->id . "\n";
    echo "   - الاسم: " . $yearlyTemplate->name . "\n";
    echo "   - النوع: " . $yearlyTemplate->type . "\n";
    echo "   - مدة المنهج: " . $yearlyTemplate->duration_months . " شهر\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء منهج ختم القرآن: " . $e->getMessage() . "\n\n";
}

// اختبار 2: إنشاء منهج الحفظ السريع باسم مخصص
echo "2. اختبار إنشاء منهج الحفظ السريع باسم مخصص:\n";
try {
    $fastTemplate = CurriculumTemplateService::createFromTemplate('fast_memorization', 'منهج الحفظ المتقدم');
    echo "   ✅ تم إنشاء منهج الحفظ السريع بنجاح!\n";
    echo "   - ID: " . $fastTemplate->id . "\n";
    echo "   - الاسم: " . $fastTemplate->name . "\n";
    echo "   - النوع: " . $fastTemplate->type . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء منهج الحفظ السريع: " . $e->getMessage() . "\n\n";
}

// اختبار 3: إنشاء منهج المراجعة المكثفة
echo "3. اختبار إنشاء منهج المراجعة المكثفة:\n";
try {
    $intensiveTemplate = CurriculumTemplateService::createFromTemplate('intensive_review');
    echo "   ✅ تم إنشاء منهج المراجعة المكثفة بنجاح!\n";
    echo "   - ID: " . $intensiveTemplate->id . "\n";
    echo "   - الاسم: " . $intensiveTemplate->name . "\n";
    echo "   - النوع: " . $intensiveTemplate->type . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ فشل في إنشاء منهج المراجعة المكثفة: " . $e->getMessage() . "\n\n";
}

// اختبار 4: اختبار نوع قالب غير موجود
echo "4. اختبار نوع قالب غير موجود:\n";
try {
    $invalidTemplate = CurriculumTemplateService::createFromTemplate('invalid_type');
    echo "   ❌ لم يحدث خطأ كما متوقع!\n\n";
    
} catch (Exception $e) {
    echo "   ✅ تم اكتشاف النوع غير الصحيح بنجاح: " . $e->getMessage() . "\n\n";
}

echo "===== انتهى اختبار القوالب =====\n";
