<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// فحص البيانات
echo "=== فحص جدول student_curricula ===\n";

$studentCurricula = \App\Models\StudentCurriculum::all();

echo "عدد السجلات: " . $studentCurricula->count() . "\n\n";

if ($studentCurricula->count() > 0) {
    $first = $studentCurricula->first();
    echo "أول سجل:\n";
    echo "ID: " . $first->id . "\n";
    echo "Student ID: " . $first->student_id . "\n";
    echo "Curriculum ID: " . $first->curriculum_id . "\n";
    echo "Daily Memorization Pages: " . ($first->daily_memorization_pages ?? 'NULL') . "\n";
    echo "Daily Minor Review Pages: " . ($first->daily_minor_review_pages ?? 'NULL') . "\n";
    echo "Daily Major Review Pages: " . ($first->daily_major_review_pages ?? 'NULL') . "\n";
    echo "Current Page: " . ($first->current_page ?? 'NULL') . "\n";
    echo "Current Surah: " . ($first->current_surah ?? 'NULL') . "\n";
    echo "Current Ayah: " . ($first->current_ayah ?? 'NULL') . "\n";
    echo "Status: " . ($first->status ?? 'NULL') . "\n";
    echo "Is Active: " . ($first->is_active ? 'true' : 'false') . "\n";
    echo "Start Date: " . ($first->start_date ?? 'NULL') . "\n";
    echo "Notes: " . ($first->notes ?? 'NULL') . "\n";
    
    echo "\n=== اختبار خدمة DailyCurriculumTrackingService ===\n";
    
    $service = new \App\Services\DailyCurriculumTrackingService();
    $dailyCurriculum = $service->getDailyCurriculum($first->student_id);
    
    if ($dailyCurriculum) {
        echo "تم الحصول على المنهج اليومي بنجاح!\n";
        echo "اسم الطالب: " . $dailyCurriculum['student_name'] . "\n";
        echo "اسم المنهج: " . $dailyCurriculum['curriculum_name'] . "\n";
        echo "الصفحة الحالية: " . $dailyCurriculum['current_page'] . "\n";
        echo "نسبة التقدم: " . $dailyCurriculum['progress_percentage'] . "%\n";
    } else {
        echo "فشل في الحصول على المنهج اليومي\n";
    }
} else {
    echo "لا توجد بيانات في الجدول\n";
}

echo "\n=== اختبار العلاقات ===\n";

// فحص وجود الطلاب
$students = \App\Models\Student::count();
echo "عدد الطلاب: " . $students . "\n";

// فحص وجود المناهج
$curricula = \App\Models\Curriculum::count();
echo "عدد المناهج: " . $curricula . "\n";

echo "\nانتهى الفحص!\n";
