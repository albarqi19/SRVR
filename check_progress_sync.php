<?php
require_once 'vendor/autoload.php';

use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 فحص تزامن التقدم للطالب ID: 1\n";
echo str_repeat('=', 60) . "\n";

// فحص جدول student_curricula
$studentCurriculum = StudentCurriculum::where('student_id', 1)->first();

if (!$studentCurriculum) {
    echo "❌ لا يوجد منهج للطالب في جدول student_curricula\n";
    exit(1);
}

echo "📊 جدول student_curricula:\n";
echo "ID: " . $studentCurriculum->id . "\n";
echo "progress_percentage: " . ($studentCurriculum->progress_percentage ?? 'NULL') . "\n";
echo "completion_percentage: " . ($studentCurriculum->completion_percentage ?? 'NULL') . "\n";
echo "آخر تحديث: " . $studentCurriculum->updated_at . "\n\n";

// فحص جدول student_curriculum_progress
$progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)->first();

if (!$progress) {
    echo "⚠️ لا يوجد تقدم في جدول student_curriculum_progress\n";
} else {
    echo "📈 جدول student_curriculum_progress:\n";
    echo "ID: " . $progress->id . "\n";
    echo "completion_percentage: " . $progress->completion_percentage . "\n";
    echo "status: " . $progress->status . "\n";
    echo "آخر تحديث: " . $progress->updated_at . "\n\n";
}

// اقتراح الحل
echo "💡 الحل المطلوب:\n";
echo "يجب تحديث progress_percentage في جدول student_curricula\n";
echo "ليُطابق completion_percentage في جدول student_curriculum_progress\n\n";

if ($progress && $studentCurriculum->progress_percentage != $progress->completion_percentage) {
    echo "⚠️ التقدم غير متزامن!\n";
    echo "student_curricula.progress_percentage: " . ($studentCurriculum->progress_percentage ?? 'NULL') . "\n";
    echo "student_curriculum_progress.completion_percentage: " . $progress->completion_percentage . "\n\n";
    
    echo "🔧 تطبيق الإصلاح...\n";
    $studentCurriculum->update([
        'progress_percentage' => $progress->completion_percentage,
        'updated_at' => now(),
    ]);
    
    echo "✅ تم تحديث التقدم بنجاح!\n";
    echo "القيمة الجديدة: " . $progress->completion_percentage . "%\n";
} else {
    echo "✅ التقدم متزامن!\n";
}
