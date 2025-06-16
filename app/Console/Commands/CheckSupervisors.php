<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CircleSupervisor;
use App\Models\User;
use App\Models\QuranCircle;

class CheckSupervisors extends Command
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

        $this->newLine();

        // 2. التحقق من تعيينات المشرفين على الحلقات
        $this->info('📋 2. تعيينات المشرفين على الحلقات:');
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

        $this->newLine();

        // 3. التحقق من الحلقات التي لها مشرف مباشر
        $this->info('📋 3. الحلقات التي لها مشرف مباشر (supervisor_id):');
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

        $this->newLine();

        // 4. إحصائيات عامة
        $this->info('📊 الإحصائيات العامة:');
        $this->line('👥 عدد المستخدمين الذين لديهم دور مشرف: ' . $supervisorUsers->count());
        $this->line('📋 عدد تعيينات المشرفين: ' . $supervisorAssignments->count());
        $this->line('✅ عدد التعيينات النشطة: ' . $supervisorAssignments->where('is_active', true)->count());
        $this->line('❌ عدد التعيينات غير النشطة: ' . $supervisorAssignments->where('is_active', false)->count());
        $this->line('🏫 عدد الحلقات التي لها مشرف مباشر: ' . $circlesWithSupervisors->count());

        $this->newLine();
        $this->info('✅ تم الانتهاء من فحص المشرفين!');

        return Command::SUCCESS;
    }
}
