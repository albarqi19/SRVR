<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 فحص الحلقات الفرعية في النظام:\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// فحص الحلقات الفرعية
$circleGroups = \App\Models\CircleGroup::with(['quranCircle', 'teacher'])->get();

if ($circleGroups->count() > 0) {
    echo "📋 الحلقات الفرعية الموجودة:\n";
    foreach ($circleGroups as $cg) {
        echo "- ID: {$cg->id}\n";
        echo "  الاسم: {$cg->name}\n";
        echo "  الحلقة الرئيسية: " . ($cg->quranCircle->name ?? 'غير محدد') . "\n";
        echo "  المعلم: " . ($cg->teacher->name ?? 'غير محدد') . "\n";
        echo "  الوصف: " . ($cg->description ?? 'غير محدد') . "\n";
        echo "  الحالة: " . ($cg->status ?? 'غير محدد') . "\n\n";
    }
} else {
    echo "❌ لا توجد حلقات فرعية في النظام\n\n";
}

// فحص الطلاب في الحلقات الفرعية
echo "👥 الطلاب في الحلقات الفرعية:\n";
$studentsInGroups = \App\Models\Student::whereNotNull('circle_group_id')
    ->with(['circleGroup', 'quranCircle'])
    ->get();

if ($studentsInGroups->count() > 0) {
    foreach ($studentsInGroups as $student) {
        echo "- {$student->name} (ID: {$student->id})\n";
        echo "  الحلقة الفرعية: " . ($student->circleGroup->name ?? 'غير محدد') . "\n";
        echo "  الحلقة الرئيسية: " . ($student->quranCircle->name ?? 'غير محدد') . "\n\n";
    }
} else {
    echo "❌ لا يوجد طلاب في الحلقات الفرعية\n\n";
}

// إنشاء بيانات تجريبية إذا لم تكن موجودة
if ($circleGroups->count() == 0) {
    echo "🔨 إنشاء بيانات تجريبية للحلقات الفرعية...\n";
    
    // البحث عن حلقة جماعية
    $groupCircle = \App\Models\QuranCircle::where('circle_type', 'حلقة جماعية')->first();
    
    if ($groupCircle) {
        // إنشاء حلقة فرعية
        $circleGroup = \App\Models\CircleGroup::create([
            'quran_circle_id' => $groupCircle->id,
            'name' => 'المجموعة الأولى',
            'teacher_id' => 1, // معلم تجريبي
            'status' => 'نشطة',
            'description' => 'حلقة فرعية تجريبية للاختبار'
        ]);
        
        echo "✅ تم إنشاء حلقة فرعية تجريبية: {$circleGroup->name}\n";
        
        // نقل بعض الطلاب للحلقة الفرعية
        $students = \App\Models\Student::where('quran_circle_id', $groupCircle->id)
            ->limit(2)
            ->get();
            
        foreach ($students as $student) {
            $student->update(['circle_group_id' => $circleGroup->id]);
            echo "✅ تم نقل الطالب {$student->name} للحلقة الفرعية\n";
        }
    } else {
        echo "❌ لم يتم العثور على حلقة جماعية لإنشاء حلقات فرعية\n";
    }
}

echo "\n✅ انتهى الفحص\n";
