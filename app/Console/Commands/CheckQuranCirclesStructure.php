<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\QuranCircle;
use App\Models\Student;

class CheckQuranCirclesStructure extends Command
{
    protected $signature = 'check:quran-circles';
    protected $description = 'فحص بنية جدول quran_circles';

    public function handle()
    {
        $this->info('=== فحص بنية جدول quran_circles ===');
        
        try {
            // الحصول على قائمة الأعمدة
            $columns = Schema::getColumnListing('quran_circles');
            
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
            $circles = QuranCircle::take(3)->get();
            
            foreach ($circles as $circle) {
                $this->line("ID: {$circle->id} | Name: {$circle->name}");
                
                // عرض جميع الأعمدة
                foreach ($columns as $column) {
                    if (isset($circle->$column)) {
                        $this->line("  {$column}: {$circle->$column}");
                    }
                }
                $this->line("---");
            }
            
            $this->info("\n=== فحص علاقة الطلاب مع الحلقات ===");
            $studentColumns = Schema::getColumnListing('students');
            $relevantColumns = array_filter($studentColumns, function($column) {
                return str_contains(strtolower($column), 'circle') || 
                       str_contains(strtolower($column), 'teacher');
            });
            
            $this->info('أعمدة مرتبطة في جدول students:');
            foreach ($relevantColumns as $column) {
                $this->line("- {$column}");
            }
            
            // عينة من الطلاب
            $this->info("\nعينة من الطلاب:");
            $students = Student::with('quranCircle')->take(3)->get();
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
    }
}
