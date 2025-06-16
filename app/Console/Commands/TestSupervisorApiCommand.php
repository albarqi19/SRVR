<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\Mosque;
use App\Models\CircleSupervisor;
use App\Models\User;

class TestSupervisorApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:supervisor-api {supervisor_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار شامل لـ API المشرف مع فحص بيانات الطلاب';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $supervisorId = $this->argument('supervisor_id');
        
        $this->info("🔍 بدء اختبار شامل لـ API المشرف رقم: {$supervisorId}");
        $this->newLine();
        
        // 1. فحص البيانات في قاعدة البيانات
        $this->checkDatabaseData();
        
        // 2. اختبار API
        $this->testSupervisorApi($supervisorId);
        
        $this->newLine();
        $this->info("✅ انتهى الاختبار بنجاح!");
    }
    
    private function checkDatabaseData()
    {
        $this->info("📊 فحص البيانات في قاعدة البيانات:");
        $this->line("=====================================");
        
        // عدد الطلاب
        $totalStudents = Student::count();
        $activeStudents = Student::where('is_active', true)->count();
        $studentsWithCircles = Student::whereNotNull('quran_circle_id')->count();
        
        $this->line("👥 الطلاب:");
        $this->line("   - إجمالي الطلاب: {$totalStudents}");
        $this->line("   - الطلاب النشطين: {$activeStudents}");
        $this->line("   - الطلاب المرتبطين بحلقات: {$studentsWithCircles}");
        
        // عدد المعلمين
        $totalTeachers = Teacher::count();
        // $activeTeachers = Teacher::where('is_active', true)->count(); // تعطيل هذا السطر لأن العمود غير موجود
        
        $this->line("👨‍🏫 المعلمين:");
        $this->line("   - إجمالي المعلمين: {$totalTeachers}");
        
        // عدد الحلقات
        $totalCircles = QuranCircle::count();
        // $activeCircles = QuranCircle::where('is_active', true)->count(); // تعطيل هذا السطر لأن العمود قد يكون غير موجود
        
        $this->line("🕌 الحلقات القرآنية:");
        $this->line("   - إجمالي الحلقات: {$totalCircles}");
        
        // عدد المساجد
        $totalMosques = Mosque::count();
        
        $this->line("🏛️ المساجد:");
        $this->line("   - إجمالي المساجد: {$totalMosques}");
        
        // تفاصيل كل حلقة
        $this->newLine();
        $this->line("📋 تفاصيل الحلقات:");
        $circles = QuranCircle::with(['students', 'mosque', 'circleGroups.students'])->get();
        
        foreach ($circles as $circle) {
            $studentsCount = $circle->students()->count();
            $mosqueName = $circle->mosque ? $circle->mosque->name : 'غير محدد';
            
            $this->line("   - الحلقة: {$circle->name} (المسجد: {$mosqueName})");
            $this->line("     عدد الطلاب المباشرين: {$studentsCount}");
            
            // فحص الحلقات الفرعية
            if ($circle->circleGroups->count() > 0) {
                $this->line("     الحلقات الفرعية:");
                foreach ($circle->circleGroups as $group) {
                    $groupStudentsCount = $group->students()->count();
                    $this->line("       - {$group->name}: {$groupStudentsCount} طالب");
                }
            }
        }
        
        // فحص توزيع الطلاب
        $this->checkStudentDistribution();
        
        $this->newLine();
    }
    
    private function testSupervisorApi($supervisorId)
    {
        $this->info("🌐 اختبار API المشرف الشامل:");
        $this->line("==============================");
        
        try {
            // استدعاء API
            $response = Http::accept('application/json')
                ->get("http://localhost:8000/api/supervisor/comprehensive-overview", [
                    'supervisor_id' => $supervisorId
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                $this->info("✅ API يعمل بنجاح!");
                $this->newLine();
                
                // عرض معلومات المشرف
                if (isset($data['data']['supervisor'])) {
                    $supervisor = $data['data']['supervisor'];
                    $this->line("👤 معلومات المشرف:");
                    $this->line("   - الاسم: " . ($supervisor['name'] ?? 'غير محدد'));
                    $this->line("   - البريد: " . ($supervisor['email'] ?? 'غير محدد'));
                }
                
                // عرض إحصائيات عامة
                if (isset($data['data']['summary'])) {
                    $summary = $data['data']['summary'];
                    $this->line("📈 الإحصائيات العامة:");
                    $this->line("   - المساجد: " . ($summary['total_mosques'] ?? 0));
                    $this->line("   - الحلقات: " . ($summary['total_circles'] ?? 0));
                    $this->line("   - المعلمين: " . ($summary['total_teachers'] ?? 0));
                    $this->line("   - الطلاب: " . ($summary['total_students'] ?? 0));
                }
                
                // فحص بيانات المساجد والحلقات
                if (isset($data['data']['mosques'])) {
                    $this->newLine();
                    $this->line("🏛️ تفاصيل المساجد والحلقات:");
                    
                    foreach ($data['data']['mosques'] as $mosqueData) {
                        $mosque = $mosqueData['mosque'];
                        $mosqueSummary = $mosqueData['mosque_summary'];
                        
                        $this->line("   📍 المسجد: " . $mosque['name']);
                        $this->line("      - الحي: " . ($mosque['neighborhood'] ?? 'غير محدد'));
                        $this->line("      - عدد الحلقات: " . $mosqueSummary['circles_count']);
                        $this->line("      - عدد المعلمين: " . $mosqueSummary['teachers_count']);
                        $this->line("      - عدد الطلاب: " . $mosqueSummary['students_count']);
                        
                        // فحص كل حلقة
                        foreach ($mosqueData['circles'] as $circle) {
                            $this->line("      🔵 الحلقة: " . $circle['name']);
                            $this->line("         - النوع: " . ($circle['circle_type'] ?? 'غير محدد'));
                            $this->line("         - عدد المعلمين: " . count($circle['teachers']));
                            $this->line("         - عدد الطلاب: " . count($circle['students']));
                            
                            // إذا كان عدد الطلاب صفر، نتحقق من السبب
                            if (count($circle['students']) === 0) {
                                $this->warn("         ⚠️  تحذير: لا يوجد طلاب في هذه الحلقة!");
                                
                                // فحص مباشر من قاعدة البيانات
                                $dbStudentsCount = Student::where('quran_circle_id', $circle['id'])->count();
                                $this->line("         🔍 فحص قاعدة البيانات: {$dbStudentsCount} طالب");
                                
                                if ($dbStudentsCount > 0) {
                                    $this->error("         ❌ مشكلة: API لا يجلب الطلاب الموجودين!");
                                    $this->analyzeStudentsProblem($circle['id']);
                                }
                            } else {
                                $this->info("         ✅ تم جلب " . count($circle['students']) . " طالب بنجاح");
                            }
                            
                            // فحص الحلقات الفرعية
                            if (isset($circle['groups']) && count($circle['groups']) > 0) {
                                $this->line("         📚 الحلقات الفرعية:");
                                foreach ($circle['groups'] as $group) {
                                    $groupStudentsCount = count($group['students'] ?? []);
                                    $this->line("           - {$group['name']}: {$groupStudentsCount} طالب");
                                    
                                    if ($groupStudentsCount === 0) {
                                        // فحص قاعدة البيانات للحلقة الفرعية
                                        $dbGroupStudentsCount = Student::where('circle_group_id', $group['id'])->count();
                                        if ($dbGroupStudentsCount > 0) {
                                            $this->error("           ❌ مشكلة في الحلقة الفرعية: {$dbGroupStudentsCount} طالب في قاعدة البيانات لكن API يُظهر 0");
                                        }
                                    }
                                }
                            }
                        }
                        $this->newLine();
                    }
                }
                
                // عرض JSON كامل للمراجعة (اختياري)
                if ($this->confirm('هل تريد عرض البيانات الكاملة بصيغة JSON؟', false)) {
                    $this->newLine();
                    $this->line("📄 البيانات الكاملة:");
                    $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                
            } else {
                $this->error("❌ فشل API! كود الحالة: " . $response->status());
                $this->line("الرسالة: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاستدعاء: " . $e->getMessage());
        }
    }
    
    /**
     * فحص توزيع الطلاب بالتفصيل
     */
    private function checkStudentDistribution()
    {
        $this->newLine();
        $this->info("🔍 فحص تفصيلي لتوزيع الطلاب:");
        $this->line("================================");
        
        // الطلاب حسب الحلقات الرئيسية
        $studentsInMainCircles = Student::whereNotNull('quran_circle_id')
            ->whereNull('circle_group_id')
            ->count();
        $this->line("👥 الطلاب في الحلقات الرئيسية: {$studentsInMainCircles}");
        
        // الطلاب حسب الحلقات الفرعية
        $studentsInSubCircles = Student::whereNotNull('circle_group_id')->count();
        $this->line("👥 الطلاب في الحلقات الفرعية: {$studentsInSubCircles}");
        
        // الطلاب غير المرتبطين بأي حلقة
        $studentsWithoutCircles = Student::whereNull('quran_circle_id')
            ->whereNull('circle_group_id')
            ->count();
        $this->line("❓ الطلاب غير المرتبطين بحلقة: {$studentsWithoutCircles}");
        
        // تفاصيل كل طالب في الحلقات
        $this->newLine();
        $this->line("📋 تفاصيل الطلاب بالحلقات:");
        
        $students = Student::with(['quranCircle', 'circleGroup'])
            ->whereNotNull('quran_circle_id')
            ->get();
            
        foreach ($students as $student) {
            $circleName = $student->quranCircle ? $student->quranCircle->name : 'غير محدد';
            $groupName = $student->circleGroup ? $student->circleGroup->name : 'بدون حلقة فرعية';
            
            $this->line("   - {$student->name} → الحلقة: {$circleName} | المجموعة: {$groupName}");
        }
        
        // فحص البيانات المطلوبة للـ API
        $this->checkStudentsDataForApi();
    }
    
    /**
     * فحص البيانات المطلوبة للطلاب في الـ API
     */
    private function checkStudentsDataForApi()
    {
        $this->newLine();
        $this->info("🔧 فحص بيانات الطلاب المطلوبة للـ API:");
        $this->line("==========================================");
        
        $students = Student::all();
        $missingData = [];
        
        foreach ($students as $student) {
            $issues = [];
            
            if (empty($student->name)) $issues[] = 'اسم مفقود';
            if (empty($student->phone)) $issues[] = 'رقم هاتف مفقود';
            if (empty($student->guardian_phone)) $issues[] = 'رقم ولي الأمر مفقود';
            if (empty($student->enrollment_date)) $issues[] = 'تاريخ التسجيل مفقود';
            
            if (!empty($issues)) {
                $missingData[] = [
                    'student' => $student->name ?: "طالب رقم {$student->id}",
                    'issues' => $issues
                ];
            }
        }
        
        if (empty($missingData)) {
            $this->info("✅ جميع بيانات الطلاب مكتملة للـ API");
        } else {
            $this->warn("⚠️  يوجد بيانات ناقصة في الطلاب:");
            foreach ($missingData as $item) {
                $this->line("   - {$item['student']}: " . implode(', ', $item['issues']));
            }
        }
        
        // فحص الأعمدة المطلوبة في جدول الطلاب
        $this->checkStudentsTableStructure();
    }
    
    /**
     * فحص بنية جدول الطلاب
     */
    private function checkStudentsTableStructure()
    {
        $this->newLine();
        $this->info("🗃️ فحص بنية جدول الطلاب:");
        $this->line("===========================");
        
        try {
            $columns = DB::select("SHOW COLUMNS FROM students");
            $columnNames = array_column($columns, 'Field');
            
            $requiredColumns = ['id', 'name', 'phone', 'guardian_phone', 'enrollment_date', 'quran_circle_id', 'circle_group_id'];
            $missingColumns = [];
            
            foreach ($requiredColumns as $col) {
                if (in_array($col, $columnNames)) {
                    $this->info("   ✅ العمود {$col} موجود");
                } else {
                    $this->error("   ❌ العمود {$col} مفقود");
                    $missingColumns[] = $col;
                }
            }
            
            if (empty($missingColumns)) {
                $this->info("✅ جميع الأعمدة المطلوبة موجودة");
            } else {
                $this->error("❌ أعمدة مفقودة: " . implode(', ', $missingColumns));
            }
            
        } catch (\Exception $e) {
            $this->error("خطأ في فحص بنية الجدول: " . $e->getMessage());
        }
    }
    
    /**
     * تحليل مشكلة عدم ظهور الطلاب في API
     */
    private function analyzeStudentsProblem($circleId)
    {
        $this->newLine();
        $this->warn("🔍 تحليل مشكلة الطلاب في الحلقة {$circleId}:");
        $this->line("============================================");
        
        // فحص العلاقات
        $circle = QuranCircle::find($circleId);
        if (!$circle) {
            $this->error("❌ الحلقة غير موجودة!");
            return;
        }
        
        // فحص الطلاب المباشرين
        $directStudents = $circle->students()->get();
        $this->line("👥 الطلاب المباشرين في الحلقة: " . $directStudents->count());
        
        foreach ($directStudents as $student) {
            $this->line("   - {$student->name} (ID: {$student->id})");
            $this->line("     quran_circle_id: {$student->quran_circle_id}");
            $this->line("     circle_group_id: " . ($student->circle_group_id ?: 'null'));
            $this->line("     is_active: " . ($student->is_active ? 'true' : 'false'));
        }
        
        // فحص الحلقات الفرعية
        $circleGroups = $circle->circleGroups()->get();
        $this->line("📚 الحلقات الفرعية: " . $circleGroups->count());
        
        foreach ($circleGroups as $group) {
            $groupStudents = $group->students()->get();
            $this->line("   - {$group->name}: {$groupStudents->count()} طالب");
            
            foreach ($groupStudents as $student) {
                $this->line("     * {$student->name} (ID: {$student->id})");
            }
        }
        
        // فحص استعلام API
        $this->line("🔍 محاكاة استعلام API:");
        try {
            $apiQuery = QuranCircle::with([
                'students:id,name,phone,guardian_phone,enrollment_date'
            ])->find($circleId);
            
            $this->line("عدد الطلاب من استعلام API: " . $apiQuery->students->count());
            
            if ($apiQuery->students->count() === 0 && $directStudents->count() > 0) {
                $this->error("❌ مشكلة في استعلام API! يجب التحقق من:");
                $this->line("   1. صحة العلاقة في Model");
                $this->line("   2. وجود الأعمدة المطلوبة");
                $this->line("   3. قيود قاعدة البيانات");
            }
            
        } catch (\Exception $e) {
            $this->error("خطأ في محاكاة استعلام API: " . $e->getMessage());
        }
    }
}
