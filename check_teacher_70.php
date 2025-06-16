<?php
// فحص سريع للمعلم
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 فحص المعلم ID: 70\n";
echo str_repeat('=', 30) . "\n";

$teacher = \App\Models\Teacher::find(70);
if (!$teacher) {
    echo "❌ المعلم غير موجود!\n";
    exit;
}

echo "📋 بيانات المعلم:\n";
echo "   - ID: {$teacher->id}\n";
echo "   - الاسم: {$teacher->name}\n";
echo "   - المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد') . "\n";
echo "   - mosque_id: {$teacher->mosque_id}\n";
echo "   - quran_circle_id: {$teacher->quran_circle_id}\n";
echo "   - نشط: " . ($teacher->is_active ? 'نعم' : 'لا') . "\n\n";

echo "🕌 فحص المسجد ID: 16\n";
$mosque = \App\Models\Mosque::find(16);
if ($mosque) {
    echo "   - اسم المسجد: {$mosque->name}\n";
} else {
    echo "   ❌ المسجد غير موجود!\n";
}

echo "\n📚 الحلقات في المسجد 16:\n";
$circles = \App\Models\QuranCircle::where('mosque_id', 16)->get();
foreach ($circles as $circle) {
    echo "   - {$circle->name} (ID: {$circle->id}) - {$circle->period}\n";
}

echo "\n📋 تكليفات المعلم:\n";
$assignments = \App\Models\TeacherCircleAssignment::where('teacher_id', 70)->get();
if ($assignments->count() > 0) {
    foreach ($assignments as $assignment) {
        $circle = $assignment->quranCircle;
        echo "   - {$circle->name} (ID: {$circle->id}) - " . ($assignment->is_active ? 'نشط' : 'غير نشط') . "\n";
    }
} else {
    echo "   ⚠️ لا توجد تكليفات\n";
}

echo "\n📖 الحلقات الفرعية للمعلم:\n";
$subCircles = \App\Models\SubCircle::where('teacher_id', 70)->get();
if ($subCircles->count() > 0) {
    foreach ($subCircles as $subCircle) {
        $mainCircle = $subCircle->quranCircle;
        echo "   - {$subCircle->name} (الحلقة الرئيسية: {$mainCircle->name})\n";
        echo "     * مسجد الحلقة الرئيسية: {$mainCircle->mosque_id}\n";
    }
} else {
    echo "   ⚠️ لا توجد حلقات فرعية\n";
}
