<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "🔍 فحص بنية جدول quran_circles...\n\n";
    
    // الحصول على أعمدة الجدول
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('quran_circles');
    
    echo "📋 أعمدة جدول quran_circles:\n";
    echo "================================\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\n";
    
    // فحص عينة من البيانات
    $circles = \App\Models\QuranCircle::take(3)->get();
    echo "📊 عينة من البيانات:\n";
    echo "===================\n";
    
    if ($circles->count() > 0) {
        foreach ($circles as $circle) {
            echo "ID: {$circle->id}\n";
            echo "Name: {$circle->name}\n";
            echo "Time Period: " . ($circle->time_period ?? 'غير محدد') . "\n";
            echo "---\n";
        }
    } else {
        echo "لا توجد بيانات في الجدول\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
