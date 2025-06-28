<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Teacher;
use App\Models\CircleGroup;

echo "البحث عن المعلم 'أحمد علي':\n";
echo "===========================\n\n";

$teachers = Teacher::where('name', 'like', '%أحمد علي%')->get();
echo "المعلمين الذين يحتوون على 'أحمد علي':\n";
foreach ($teachers as $teacher) {
    echo "- ID: {$teacher->id}, الاسم: {$teacher->name}\n";
}

echo "\nالبحث في جميع الحلقات الفرعية:\n";
$circleGroups = CircleGroup::with(['teacher', 'quranCircle'])->get();

foreach ($circleGroups as $group) {
    if ($group->teacher && strpos($group->teacher->name, 'أحمد علي') !== false) {
        echo "- الحلقة الفرعية: {$group->name}\n";
        echo "  المعلم: {$group->teacher->name} (ID: {$group->teacher->id})\n";
        echo "  الحلقة الرئيسية: {$group->quranCircle->name}\n\n";
    }
}

echo "\nالبحث عن الحلقة الفرعية 'تجربة653':\n";
$targetGroup = CircleGroup::where('name', 'تجربة653')->with(['teacher', 'quranCircle'])->first();
if ($targetGroup) {
    echo "- الحلقة الفرعية: {$targetGroup->name}\n";
    echo "  المعلم: " . ($targetGroup->teacher ? $targetGroup->teacher->name : 'غير محدد') . "\n";
    echo "  ID المعلم: " . ($targetGroup->teacher ? $targetGroup->teacher->id : 'غير محدد') . "\n";
    echo "  الحلقة الرئيسية: {$targetGroup->quranCircle->name}\n";
}
