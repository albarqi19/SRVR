<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->boot();

echo "عدد الحلقات الفرعية: " . App\Models\CircleGroup::count() . "\n";
echo "عدد الحلقات القرآنية: " . App\Models\QuranCircle::count() . "\n";

// فحص أول حلقة قرآنية وحلقاتها الفرعية
$firstCircle = App\Models\QuranCircle::with('circleGroups')->first();
if ($firstCircle) {
    echo "الحلقة الأولى: " . $firstCircle->name . "\n";
    echo "عدد الحلقات الفرعية فيها: " . $firstCircle->circleGroups->count() . "\n";
    
    foreach ($firstCircle->circleGroups as $group) {
        echo "- " . $group->name . " (المعلم: " . ($group->teacher->name ?? 'غير محدد') . ")\n";
    }
}
