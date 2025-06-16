<?php

use Illuminate\Http\Request;
use App\Models\QuranCircle;
use App\Models\Teacher;

// تشخيص مباشر لمشكلة Filament
$circleId = 1;
$quranCircle = QuranCircle::find($circleId);

if (!$quranCircle) {
    echo "❌ الحلقة غير موجودة!\n";
    exit;
}

echo "🔍 تشخيص مشكلة واجهة Filament\n";
echo "📊 الحلقة: {$quranCircle->name}\n\n";

// تطبيق نفس المنطق المستخدم في CircleGroupsRelationManager
echo "1️⃣ اختبار المنطق الجديد:\n";

$options = [];

// 1. جلب المعلمين المكلفين نشطين
$assignedTeachers = $quranCircle->activeTeachers;
echo "   📊 المعلمون المكلفون: " . $assignedTeachers->count() . "\n";

if ($assignedTeachers->isNotEmpty()) {
    foreach ($assignedTeachers as $teacher) {
        $options[$teacher->id] = $teacher->name . ' (مكلف)';
        echo "   ✅ {$teacher->name} (ID: {$teacher->id}) - مكلف\n";
    }
}

// 2. جلب معلمي المسجد
if ($quranCircle->mosque_id) {
    $mosqueTeachers = Teacher::where('mosque_id', $quranCircle->mosque_id)
        ->orderBy('name')
        ->get();
    
    echo "   📊 معلمو المسجد: " . $mosqueTeachers->count() . "\n";
    
    foreach ($mosqueTeachers as $teacher) {
        if (!isset($options[$teacher->id])) {
            $options[$teacher->id] = $teacher->name;
            echo "   ✅ {$teacher->name} (ID: {$teacher->id}) - من المسجد\n";
        }
    }
}

// 3. خيار احتياطي
if (empty($options)) {
    echo "   ⚠️ لا توجد خيارات، جلب جميع المعلمين...\n";
    $allTeachers = Teacher::orderBy('name')->get();
    foreach ($allTeachers as $teacher) {
        $options[$teacher->id] = $teacher->name;
    }
}

echo "\n🎯 النتيجة النهائية:\n";
foreach ($options as $id => $name) {
    echo "   ID: {$id} => {$name}\n";
}

echo "\n✅ إجمالي الخيارات المتاحة: " . count($options) . "\n";

// اختبار JSON للتأكد من التوافق مع Filament
echo "\n🔧 اختبار JSON:\n";
echo json_encode($options, JSON_UNESCAPED_UNICODE) . "\n";

echo "\n✅ انتهى التشخيص\n";
