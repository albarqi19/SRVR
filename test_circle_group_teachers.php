<?php

require_once 'vendor/autoload.php';

use App\Models\QuranCircle;
use App\Models\Teacher;
use App\Models\CircleGroup;
use App\Models\TeacherCircleAssignment;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 اختبار عرض المعلمين في الحلقات الفرعية\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// الحصول على حلقة رئيسية
$quranCircle = QuranCircle::first();
if (!$quranCircle) {
    echo "❌ لا توجد حلقات\n";
    exit;
}

echo "📋 الحلقة الرئيسية: {$quranCircle->name} (ID: {$quranCircle->id})\n";
echo "🕌 المسجد: {$quranCircle->mosque_id}\n\n";

// اختبار المعلمين المتاحين كما في CircleGroupsRelationManager
echo "🔍 اختبار Logic للمعلمين المتاحين:\n";
echo "-" . str_repeat("-", 40) . "\n";

// النظام القديم: المعلمين المرتبطين بنفس المسجد أو الحلقة
$oldSystemTeachers = Teacher::where(function($query) use ($quranCircle) {
    $query->where('mosque_id', $quranCircle->mosque_id)
          ->orWhere('quran_circle_id', $quranCircle->id);
})->get();

echo "👥 النظام القديم - معلمين المسجد/الحلقة:\n";
foreach ($oldSystemTeachers as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id}) - مسجد: {$teacher->mosque_id}, حلقة: {$teacher->quran_circle_id}\n";
}

// النظام الجديد: المعلمين النشطين في هذه الحلقة
$newSystemTeachers = Teacher::whereHas('activeCircles', function($query) use ($quranCircle) {
    $query->where('quran_circle_id', $quranCircle->id);
})->get();

echo "\n👥 النظام الجديد - المعلمين النشطين في هذه الحلقة:\n";
foreach ($newSystemTeachers as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id})\n";
}

// المعلمين من نفس المسجد في النظام الجديد
$newSystemMosqueTeachers = Teacher::whereHas('activeCircles', function($query) use ($quranCircle) {
    $query->whereHas('mosque', function($subQuery) use ($quranCircle) {
        $subQuery->where('id', $quranCircle->mosque_id);
    });
})->get();

echo "\n👥 النظام الجديد - معلمين المسجد:\n";
foreach ($newSystemMosqueTeachers as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id})\n";
}

// الجمع بينهم كما في الكود الفعلي
$allTeachers = Teacher::where(function($query) use ($quranCircle) {
    // النظام القديم
    $query->where('mosque_id', $quranCircle->mosque_id)
          ->orWhere('quran_circle_id', $quranCircle->id);
})
// النظام الجديد: المعلمين النشطين في هذه الحلقة
->orWhereHas('activeCircles', function($query) use ($quranCircle) {
    $query->where('quran_circle_id', $quranCircle->id);
})
// يمكن إضافة المعلمين من نفس المسجد في النظام الجديد
->orWhereHas('activeCircles', function($query) use ($quranCircle) {
    $query->whereHas('mosque', function($subQuery) use ($quranCircle) {
        $subQuery->where('id', $quranCircle->mosque_id);
    });
})
->distinct()
->get();

echo "\n🎯 النتيجة النهائية - جميع المعلمين المتاحين:\n";
foreach ($allTeachers as $teacher) {
    echo "   - {$teacher->name} (ID: {$teacher->id})\n";
}

echo "\n📊 الإحصائيات:\n";
echo "   - النظام القديم: " . $oldSystemTeachers->count() . " معلمين\n";
echo "   - النظام الجديد (هذه الحلقة): " . $newSystemTeachers->count() . " معلمين\n";
echo "   - النظام الجديد (المسجد): " . $newSystemMosqueTeachers->count() . " معلمين\n";
echo "   - الإجمالي: " . $allTeachers->count() . " معلمين\n";

// فحص التكليفات النشطة
echo "\n📋 التكليفات النشطة في هذه الحلقة:\n";
$assignments = TeacherCircleAssignment::where('quran_circle_id', $quranCircle->id)
    ->where('is_active', true)
    ->with('teacher')
    ->get();

foreach ($assignments as $assignment) {
    echo "   - {$assignment->teacher->name} (مكلف من: {$assignment->start_date})\n";
}

echo "\n✅ انتهى الاختبار\n";
