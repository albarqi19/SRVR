<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحديد Laravel بشكل صحيح
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->bootstrap();

use App\Models\QuranCircle;

echo "🔍 فحص الحلقات القرآنية في قاعدة البيانات...\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    $circles = QuranCircle::select('id', 'name', 'circle_type', 'circle_status')
        ->take(10)
        ->get();
    
    if ($circles->isEmpty()) {
        echo "❌ لا توجد حلقات قرآنية في قاعدة البيانات\n";
    } else {
        echo "✅ تم العثور على {$circles->count()} حلقات قرآنية:\n\n";
        
        foreach ($circles as $circle) {
            echo "ID: {$circle->id}\n";
            echo "الاسم: {$circle->name}\n";
            echo "النوع: {$circle->circle_type}\n";
            echo "الحالة: {$circle->circle_status}\n";
            echo str_repeat("-", 30) . "\n";
        }
        
        // البحث عن مدرسة قرآنية محددة
        $quranSchool = $circles->where('circle_type', 'مدرسة قرآنية')->first();
        
        if ($quranSchool) {
            echo "\n✅ تم العثور على مدرسة قرآنية: {$quranSchool->name} (ID: {$quranSchool->id})\n";
        } else {
            echo "\n❌ لم يتم العثور على أي مدرسة قرآنية\n";
            echo "الأنواع المتاحة:\n";
            $types = $circles->pluck('circle_type')->unique()->values();
            foreach ($types as $type) {
                echo "- {$type}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage() . "\n";
}
