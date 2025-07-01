<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔍 فحص الحلقات ومعرف المسجد...\n\n";
    
    // جميع الحلقات
    $circles = \App\Models\QuranCircle::all(['id', 'name', 'mosque_id']);
    
    echo "📊 جميع الحلقات:\n";
    echo "==================\n";
    
    foreach ($circles as $circle) {
        echo "ID: {$circle->id}\n";
        echo "الاسم: {$circle->name}\n";
        echo "معرف المسجد: " . ($circle->mosque_id ?: 'NULL') . "\n";
        echo "---\n";
    }
    
    echo "\n🎯 الحلقات التي تنتمي للمسجد رقم 1:\n";
    echo "=====================================\n";
    
    $mosque1Circles = \App\Models\QuranCircle::where('mosque_id', 1)->get(['id', 'name']);
    
    if ($mosque1Circles->count() > 0) {
        foreach ($mosque1Circles as $circle) {
            echo "ID: {$circle->id} - {$circle->name}\n";
        }
    } else {
        echo "❌ لا توجد حلقات مرتبطة بالمسجد رقم 1\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
