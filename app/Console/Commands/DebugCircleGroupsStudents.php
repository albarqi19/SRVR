<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DebugCircleGroupsStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:circle-groups-students {circle_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص مفصل لمشكلة الطلاب في الحلقات الفرعية';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $circleId = $this->argument('circle_id');
        
        $this->info("🔍 فحص مفصل للحلقة رقم: {$circleId}");
        $this->newLine();
        
        // 1. فحص الحلقة الأساسية
        $this->checkMainCircle($circleId);
        
        // 2. فحص الحلقات الفرعية
        $this->checkCircleGroups($circleId);
        
        // 3. فحص العلاقات بشكل مباشر
        $this->checkRelationshipDirectly($circleId);
        
        // 4. فحص الـ Query مثل API
        $this->simulateApiQuery($circleId);
        
        // 5. فحص قاعدة البيانات مباشرة
        $this->checkDatabaseDirectly($circleId);
        
        $this->newLine();
        $this->info("✅ انتهى الفحص!");
    }
    
    private function checkMainCircle($circleId)
    {
        $this->info("📊 1. فحص الحلقة الأساسية:");
        $this->line("========================");
        
        $circle = QuranCircle::find($circleId);
        
        if (!$circle) {
            $this->error("❌ الحلقة غير موجودة!");
            return;
        }
        
        $this->line("✅ اسم الحلقة: {$circle->name}");
        $this->line("✅ ID الحلقة: {$circle->id}");
        $this->newLine();
    }
    
    private function checkCircleGroups($circleId)
    {
        $this->info("📚 2. فحص الحلقات الفرعية:");
        $this->line("==========================");
        
        $circleGroups = CircleGroup::where('quran_circle_id', $circleId)->get();
        
        $this->line("عدد الحلقات الفرعية: " . $circleGroups->count());
        
        foreach ($circleGroups as $group) {
            $this->line("  📖 الحلقة الفرعية: {$group->name} (ID: {$group->id})");
            $this->line("     quran_circle_id: {$group->quran_circle_id}");
            
            // فحص الطلاب في هذه الحلقة الفرعية
            $students = $group->students;
            $this->line("     عدد الطلاب: " . $students->count());
            
            foreach ($students as $student) {
                $this->line("       - {$student->name} (ID: {$student->id})");
                $this->line("         circle_group_id: {$student->circle_group_id}");
                $this->line("         quran_circle_id: {$student->quran_circle_id}");
            }
            $this->newLine();
        }
    }
    
    private function checkRelationshipDirectly($circleId)
    {
        $this->info("🔗 3. فحص العلاقات مباشرة:");
        $this->line("=========================");
        
        // فحص العلاقة من CircleGroup إلى Students
        $this->line("📝 فحص علاقة CircleGroup -> Students:");
        
        $circleGroups = CircleGroup::where('quran_circle_id', $circleId)->get();
        
        foreach ($circleGroups as $group) {
            $this->line("  الحلقة الفرعية: {$group->name}");
            
            // طريقة 1: استخدام العلاقة
            $studentsViaRelation = $group->students()->get();
            $this->line("    عبر العلاقة: " . $studentsViaRelation->count() . " طالب");
            
            // طريقة 2: استعلام مباشر
            $studentsViaDirect = Student::where('circle_group_id', $group->id)->get();
            $this->line("    استعلام مباشر: " . $studentsViaDirect->count() . " طالب");
            
            if ($studentsViaRelation->count() != $studentsViaDirect->count()) {
                $this->error("    ❌ تضارب في النتائج!");
            } else {
                $this->info("    ✅ النتائج متطابقة");
            }
            
            // عرض أسماء الطلاب
            foreach ($studentsViaRelation as $student) {
                $this->line("      - {$student->name}");
            }
        }
        $this->newLine();
    }
    
    private function simulateApiQuery($circleId)
    {
        $this->info("🌐 4. محاكاة استعلام API:");
        $this->line("======================");
        
        try {
            // محاكاة الاستعلام المستخدم في API
            $circle = QuranCircle::with([
                'circleGroups.students:id,name,phone,enrollment_date'
            ])->find($circleId);
            
            $this->line("✅ تم تحميل الحلقة بنجاح");
            $this->line("عدد الحلقات الفرعية المحملة: " . $circle->circleGroups->count());
            
            foreach ($circle->circleGroups as $group) {
                $this->line("  📖 {$group->name}:");
                $this->line("     عدد الطلاب المحملين: " . $group->students->count());
                
                foreach ($group->students as $student) {
                    $this->line("       - {$student->name} (ID: {$student->id})");
                    
                    // فحص البيانات المحملة
                    $this->line("         البيانات المحملة:");
                    $this->line("           name: " . ($student->name ?? 'NULL'));
                    $this->line("           phone: " . ($student->phone ?? 'NULL'));
                    $this->line("           enrollment_date: " . ($student->enrollment_date ?? 'NULL'));
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في محاكاة API: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function checkDatabaseDirectly($circleId)
    {
        $this->info("🗃️ 5. فحص قاعدة البيانات مباشرة:");
        $this->line("==============================");
        
        // فحص الحلقات الفرعية
        $circleGroupsData = DB::table('circle_groups')
            ->where('quran_circle_id', $circleId)
            ->get(['id', 'name', 'quran_circle_id']);
            
        $this->line("عدد الحلقات الفرعية في قاعدة البيانات: " . $circleGroupsData->count());
        
        foreach ($circleGroupsData as $group) {
            $this->line("  📖 {$group->name} (ID: {$group->id})");
            
            // فحص الطلاب في هذه الحلقة الفرعية
            $studentsData = DB::table('students')
                ->where('circle_group_id', $group->id)
                ->get(['id', 'name', 'circle_group_id', 'quran_circle_id', 'is_active']);
                
            $this->line("     عدد الطلاب في قاعدة البيانات: " . $studentsData->count());
            
            foreach ($studentsData as $student) {
                $this->line("       - {$student->name} (ID: {$student->id})");
                $this->line("         circle_group_id: {$student->circle_group_id}");
                $this->line("         quran_circle_id: {$student->quran_circle_id}");
                $this->line("         is_active: " . ($student->is_active ? 'true' : 'false'));
            }
        }
        
        // إحصائيات شاملة
        $this->newLine();
        $this->line("📊 إحصائيات شاملة:");
        
        $totalStudentsInCircle = DB::table('students')
            ->where('quran_circle_id', $circleId)
            ->count();
        $this->line("  إجمالي الطلاب في الحلقة {$circleId}: {$totalStudentsInCircle}");
        
        $studentsInGroups = DB::table('students')
            ->whereIn('circle_group_id', function($query) use ($circleId) {
                $query->select('id')
                      ->from('circle_groups')
                      ->where('quran_circle_id', $circleId);
            })
            ->count();
        $this->line("  الطلاب في الحلقات الفرعية: {$studentsInGroups}");
        
        $studentsDirectlyInCircle = DB::table('students')
            ->where('quran_circle_id', $circleId)
            ->whereNull('circle_group_id')
            ->count();
        $this->line("  الطلاب مباشرة في الحلقة: {$studentsDirectlyInCircle}");
    }
}
