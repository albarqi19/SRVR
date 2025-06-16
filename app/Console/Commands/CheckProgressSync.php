<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;

class CheckProgressSync extends Command
{
    protected $signature = 'check:progress-sync {--student=1} {--circles}';
    protected $description = 'فحص تزامن التقدم بين الجدولين';

    public function handle()
    {
        if ($this->option('circles')) {
            return $this->checkCirclesStructure();
        }
        
        $studentId = $this->option('student');
        
        $this->info("🔍 فحص تزامن التقدم للطالب ID: {$studentId}");
        $this->line(str_repeat('=', 60));

        // فحص جدول student_curricula
        $studentCurriculum = StudentCurriculum::where('student_id', $studentId)->first();
        
        if (!$studentCurriculum) {
            $this->error("❌ لا يوجد منهج للطالب في جدول student_curricula");
            return 1;
        }

        $this->info("📊 جدول student_curricula:");
        $this->table(
            ['الحقل', 'القيمة'],
            [
                ['ID', $studentCurriculum->id],
                ['progress_percentage', $studentCurriculum->progress_percentage ?? 'NULL'],
                ['completion_percentage', $studentCurriculum->completion_percentage ?? 'NULL'],
                ['آخر تحديث', $studentCurriculum->updated_at],
            ]
        );

        // فحص جدول student_curriculum_progress
        $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)->first();
        
        if (!$progress) {
            $this->warn("⚠️ لا يوجد تقدم في جدول student_curriculum_progress");
        } else {
            $this->info("\n📈 جدول student_curriculum_progress:");
            $this->table(
                ['الحقل', 'القيمة'],
                [
                    ['ID', $progress->id],
                    ['completion_percentage', $progress->completion_percentage],
                    ['status', $progress->status],
                    ['آخر تحديث', $progress->updated_at],
                ]
            );
        }

        // اقتراح الحل
        $this->info("\n💡 الحل المطلوب:");
        $this->line("يجب تحديث progress_percentage في جدول student_curricula");
        $this->line("ليُطابق completion_percentage في جدول student_curriculum_progress");

        if ($progress && $studentCurriculum->progress_percentage != $progress->completion_percentage) {
            $this->warn("\n⚠️ التقدم غير متزامن!");
            $this->line("student_curricula.progress_percentage: " . ($studentCurriculum->progress_percentage ?? 'NULL'));
            $this->line("student_curriculum_progress.completion_percentage: " . $progress->completion_percentage);
            
            $this->info("\n🔧 تطبيق الإصلاح...");
            $studentCurriculum->update([
                'progress_percentage' => $progress->completion_percentage,
                'updated_at' => now(),
            ]);
            
            $this->info("✅ تم تحديث التقدم بنجاح!");
            $this->line("القيمة الجديدة: " . $progress->completion_percentage . "%");
        } else {
            $this->info("✅ التقدم متزامن!");
        }

        return 0;
    }
    
    private function checkCirclesStructure()
    {
        $this->info('=== فحص بنية جدول quran_circles ===');
        
        try {
            // الحصول على قائمة الأعمدة
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('quran_circles');
            
            $this->info('أعمدة جدول quran_circles:');
            foreach ($columns as $column) {
                $this->line("- {$column}");
            }
            
            $this->info("\n=== البحث عن أعمدة المعلم ===");
            $teacherColumns = array_filter($columns, function($column) {
                return str_contains(strtolower($column), 'teacher');
            });
            
            if (!empty($teacherColumns)) {
                foreach ($teacherColumns as $column) {
                    $this->info("عمود معلم موجود: {$column}");
                }
            } else {
                $this->warn('لا يوجد عمود معلم مباشر في جدول quran_circles');
            }
            
            $this->info("\n=== عينة من البيانات ===");
            $circles = \App\Models\QuranCircle::take(3)->get();
            
            foreach ($circles as $circle) {
                $this->line("ID: {$circle->id} | Name: {$circle->name}");
                
                // عرض الأعمدة المهمة فقط
                foreach (['teacher_id', 'assigned_teacher_id', 'primary_teacher_id'] as $field) {
                    if (in_array($field, $columns) && isset($circle->$field)) {
                        $this->line("  {$field}: {$circle->$field}");
                    }
                }
                $this->line("---");
            }
            
            $this->info("\n=== فحص علاقة الطلاب مع الحلقات ===");
            $students = \App\Models\Student::with('quranCircle')->take(3)->get();
            foreach ($students as $student) {
                $this->line("Student: {$student->name}");
                $this->line("Circle ID: " . ($student->quran_circle_id ?? 'NULL'));
                if ($student->quranCircle) {
                    $this->line("Circle Name: {$student->quranCircle->name}");
                }
                $this->line("---");
            }
            
        } catch (\Exception $e) {
            $this->error("خطأ: " . $e->getMessage());
        }
        
        return 0;
    }
}
