<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CircleSupervisor;
use App\Models\User;
use App\Models\QuranCircle;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\CircleGroup;
use App\Models\Mosque;
use Spatie\Permission\Models\Role;

class CheckSupervisorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:supervisors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص المشرفين المتواجدين في النظام';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 فحص المشرفين في النظام...');
        $this->newLine();

        // 1. التحقق من المستخدمين الذين لديهم دور مشرف
        $this->info('📋 1. المستخدمون الذين لديهم دور "supervisor":');
        
        try {
            $supervisorUsers = User::role('supervisor')->get();
            
            if ($supervisorUsers->count() > 0) {
                $this->table(
                    ['ID', 'الاسم', 'البريد الإلكتروني', 'نشط', 'تاريخ الإنشاء'],
                    $supervisorUsers->map(function ($user) {
                        return [
                            $user->id,
                            $user->name,
                            $user->email,
                            $user->is_active ? '✅ نشط' : '❌ غير نشط',
                            $user->created_at->format('Y-m-d H:i:s'),
                        ];
                    })
                );
            } else {
                $this->warn('❌ لا يوجد مستخدمون لديهم دور "supervisor"');
            }
        } catch (\Exception $e) {
            $this->error('خطأ في جلب المستخدمين: ' . $e->getMessage());
        }

        $this->newLine();

        // 2. التحقق من جميع المستخدمين وأدوارهم
        $this->info('📋 2. جميع المستخدمين وأدوارهم:');
        
        try {
            $allUsers = User::with('roles')->get();
            
            if ($allUsers->count() > 0) {
                $this->table(
                    ['ID', 'الاسم', 'البريد الإلكتروني', 'الأدوار', 'نشط'],
                    $allUsers->map(function ($user) {
                        return [
                            $user->id,
                            $user->name,
                            $user->email,
                            $user->roles->pluck('name')->implode(', ') ?: 'لا يوجد أدوار',
                            $user->is_active ? '✅ نشط' : '❌ غير نشط',
                        ];
                    })
                );
            } else {
                $this->warn('❌ لا يوجد مستخدمون في النظام');
            }
        } catch (\Exception $e) {
            $this->error('خطأ في جلب المستخدمين: ' . $e->getMessage());
        }

        $this->newLine();

        // 3. التحقق من تعيينات المشرفين على الحلقات
        $this->info('📋 3. تعيينات المشرفين على الحلقات:');
        
        try {
            $supervisorAssignments = CircleSupervisor::with(['supervisor', 'quranCircle.mosque'])->get();
            
            if ($supervisorAssignments->count() > 0) {
                $this->table(
                    ['ID', 'المشرف', 'الحلقة', 'المسجد', 'تاريخ التكليف', 'تاريخ الانتهاء', 'نشط'],
                    $supervisorAssignments->map(function ($assignment) {
                        return [
                            $assignment->id,
                            $assignment->supervisor->name ?? 'غير محدد',
                            $assignment->quranCircle->name ?? 'غير محدد',
                            $assignment->quranCircle->mosque->name ?? 'غير محدد',
                            $assignment->assignment_date,
                            $assignment->end_date ?? 'مستمر',
                            $assignment->is_active ? '✅ نشط' : '❌ غير نشط',
                        ];
                    })
                );
            } else {
                $this->warn('❌ لا توجد تعيينات مشرفين على الحلقات');
            }
        } catch (\Exception $e) {
            $this->error('خطأ في جلب تعيينات المشرفين: ' . $e->getMessage());
        }

        $this->newLine();

        // 4. التحقق من الحلقات التي لها مشرف مباشر
        $this->info('📋 4. الحلقات التي لها مشرف مباشر (supervisor_id):');
        
        try {
            $circlesWithSupervisors = QuranCircle::with(['supervisor', 'mosque'])
                ->whereNotNull('supervisor_id')
                ->get();
            
            if ($circlesWithSupervisors->count() > 0) {
                $this->table(
                    ['ID', 'اسم الحلقة', 'المسجد', 'المشرف', 'نوع الحلقة', 'حالة الحلقة'],
                    $circlesWithSupervisors->map(function ($circle) {
                        return [
                            $circle->id,
                            $circle->name,
                            $circle->mosque->name ?? 'غير محدد',
                            $circle->supervisor->name ?? 'غير محدد',
                            $circle->circle_type,
                            $circle->circle_status,
                        ];
                    })
                );
            } else {
                $this->warn('❌ لا توجد حلقات لها مشرف مباشر');
            }
        } catch (\Exception $e) {
            $this->error('خطأ في جلب الحلقات: ' . $e->getMessage());
        }

        $this->newLine();

        // 5. إحصائيات عامة
        $this->info('📊 الإحصائيات العامة:');
        try {
            $supervisorUsers = User::role('supervisor')->get();
            $supervisorAssignments = CircleSupervisor::all();
            $circlesWithSupervisors = QuranCircle::whereNotNull('supervisor_id')->get();
            
            $this->line('👥 عدد المستخدمين الذين لديهم دور مشرف: ' . $supervisorUsers->count());
            $this->line('📋 عدد تعيينات المشرفين: ' . $supervisorAssignments->count());
            $this->line('✅ عدد التعيينات النشطة: ' . $supervisorAssignments->where('is_active', true)->count());
            $this->line('❌ عدد التعيينات غير النشطة: ' . $supervisorAssignments->where('is_active', false)->count());
            $this->line('🏫 عدد الحلقات التي لها مشرف مباشر: ' . $circlesWithSupervisors->count());
        } catch (\Exception $e) {
            $this->error('خطأ في حساب الإحصائيات: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('✅ تم الانتهاء من فحص المشرفين!');

        // 6. تفاصيل ما يمكن للمشرف الإشراف عليه
        $this->newLine();
        $this->info('🔍 6. تفاصيل ما يشرف عليه كل مشرف:');
        $this->showSupervisorDetails();

        return Command::SUCCESS;
    }

    /**
     * عرض تفاصيل ما يشرف عليه كل مشرف
     */
    private function showSupervisorDetails()
    {
        try {
            $supervisorAssignments = CircleSupervisor::with([
                'supervisor', 
                'quranCircle.mosque',
                'quranCircle.teachers',
                'quranCircle.students',
                'quranCircle.circleGroups.students'
            ])->where('is_active', true)->get();

            if ($supervisorAssignments->count() === 0) {
                $this->warn('❌ لا توجد تعيينات مشرفين نشطة');
                return;
            }            foreach ($supervisorAssignments as $assignment) {
                $this->newLine();
                $this->info("👨‍💼 المشرف: {$assignment->supervisor->name}");
                $this->info("🏫 الحلقة: {$assignment->quranCircle->name}");
                $this->info("🕌 المسجد: " . ($assignment->quranCircle->mosque->name ?? 'غير محدد'));

                // عرض المعلمين في هذه الحلقة
                $this->showTeachersForCircle($assignment->quranCircle);
                
                // عرض الطلاب في هذه الحلقة  
                $this->showStudentsForCircle($assignment->quranCircle);
                
                // عرض المجموعات الفرعية في هذه الحلقة
                $this->showCircleGroups($assignment->quranCircle);
                
                // عرض إحصائيات الحلقة
                $this->showCircleStats($assignment->quranCircle);
                
                $this->line('═══════════════════════════════════════════════════════');
            }

        } catch (\Exception $e) {
            $this->error('خطأ في عرض تفاصيل المشرفين: ' . $e->getMessage());
        }
    }

    /**
     * عرض المعلمين في الحلقة
     */
    private function showTeachersForCircle($circle)
    {
        $this->newLine();
        $this->info('👨‍🏫 المعلمون في هذه الحلقة:');
        
        $teachers = $circle->teachers()->get();
        
        if ($teachers->count() > 0) {
            $this->table(
                ['ID', 'اسم المعلم', 'رقم الهوية', 'الهاتف', 'حالة المعلم'],
                $teachers->map(function ($teacher) {
                    return [
                        $teacher->id,
                        $teacher->name ?? 'غير محدد',
                        $teacher->identity_number ?? 'غير محدد',
                        $teacher->phone ?? 'غير محدد',
                        $teacher->is_active_user ? 'نشط' : 'غير نشط',
                    ];
                })
            );
        } else {
            $this->warn('   ❌ لا يوجد معلمون مسجلون في هذه الحلقة');
        }
    }

    /**
     * عرض الطلاب في الحلقة
     */
    private function showStudentsForCircle($circle)
    {
        $this->newLine();
        $this->info('👨‍🎓 الطلاب في هذه الحلقة:');
        
        $students = $circle->students()->get();
        
        if ($students->count() > 0) {
            $this->table(
                ['ID', 'اسم الطالب', 'العمر', 'حالة الطالب', 'تاريخ الالتحاق'],
                $students->take(10)->map(function ($student) {
                    return [
                        $student->id,
                        $student->name,
                        $student->age ?? 'غير محدد',
                        $student->is_active ? 'نشط' : 'غير نشط',
                        $student->enrollment_date ?? 'غير محدد',
                    ];
                })
            );
            
            if ($students->count() > 10) {
                $this->info("   📝 يوجد {$students->count()} طالب إجمالاً (تم عرض أول 10 طلاب فقط)");
            }
        } else {
            $this->warn('   ❌ لا يوجد طلاب مسجلون في هذه الحلقة');
        }
    }

    /**
     * عرض المجموعات الفرعية في الحلقة
     */
    private function showCircleGroups($circle)
    {
        $this->newLine();
        $this->info('👥 المجموعات الفرعية في هذه الحلقة:');
        
        $groups = $circle->circleGroups()->with('students')->get();
        
        if ($groups->count() > 0) {
            $this->table(
                ['ID', 'اسم المجموعة', 'عدد الطلاب', 'المستوى', 'حالة المجموعة'],
                $groups->map(function ($group) {
                    return [
                        $group->id,
                        $group->name ?? 'غير محدد',
                        $group->students->count(),
                        $group->level ?? 'غير محدد',
                        $group->group_status ?? 'غير محدد',
                    ];
                })
            );
        } else {
            $this->warn('   ❌ لا توجد مجموعات فرعية في هذه الحلقة');
        }
    }

    /**
     * عرض إحصائيات الحلقة
     */
    private function showCircleStats($circle)
    {
        $this->newLine();
        $this->info('📊 إحصائيات الحلقة:');
        
        $teachersCount = $circle->teachers()->count();
        $studentsCount = $circle->students()->count();
        $groupsCount = $circle->circleGroups()->count();
        $activeStudentsCount = $circle->students()->where('is_active', true)->count();
        
        $this->line("   👨‍🏫 عدد المعلمين: {$teachersCount}");
        $this->line("   👨‍🎓 إجمالي الطلاب: {$studentsCount}");
        $this->line("   ✅ الطلاب النشطون: {$activeStudentsCount}");
        $this->line("   👥 عدد المجموعات: {$groupsCount}");
        $this->line("   📅 نوع الحلقة: {$circle->circle_type}");
        $this->line("   📊 حالة الحلقة: {$circle->circle_status}");
    }
}
