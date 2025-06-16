<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\StudentCurriculumProgress;
use Illuminate\Support\Facades\DB;

class CleanFakeProgressData extends Command
{
    protected $signature = 'curriculum:clean-fake-data {--confirm : تأكيد الحذف}';
    protected $description = 'تنظيف البيانات المزيفة التي أنشأها الأمر السابق';

    public function handle()
    {
        $this->info('🧹 تنظيف البيانات المزيفة من النظام');
        $this->line('=====================================');

        // عرض البيانات المزيفة
        $fakeSessions = RecitationSession::where('teacher_notes', 'like', '%اختبار%')
            ->orWhere('session_id', 'like', 'auto_test_%')
            ->get();

        $this->info("📊 عدد جلسات التسميع المزيفة: " . $fakeSessions->count());

        if ($fakeSessions->count() > 0) {
            $this->table(
                ['ID', 'الطالب', 'النوع', 'الدرجة', 'ملاحظات', 'التاريخ'],
                $fakeSessions->take(10)->map(function ($session) {
                    return [
                        $session->id,
                        $session->student_id,
                        $session->recitation_type,
                        $session->grade,
                        substr($session->teacher_notes, 0, 30) . '...',
                        $session->created_at->format('Y-m-d H:i')
                    ];
                })
            );

            if ($fakeSessions->count() > 10) {
                $this->line("... و " . ($fakeSessions->count() - 10) . " جلسة أخرى");
            }
        }

        if (!$this->option('confirm')) {
            $this->warn('⚠️ هذا مجرد عرض للبيانات. لحذف البيانات المزيفة، استخدم --confirm');
            $this->info('المثال: php artisan curriculum:clean-fake-data --confirm');
            return 0;
        }

        if (!$this->confirm('هل أنت متأكد من حذف جميع البيانات المزيفة؟')) {
            $this->info('تم إلغاء العملية.');
            return 0;
        }

        DB::beginTransaction();
        try {
            // حذف جلسات التسميع المزيفة
            $deletedSessions = RecitationSession::where('teacher_notes', 'like', '%اختبار%')
                ->orWhere('session_id', 'like', 'auto_test_%')
                ->delete();

            // إعادة تعيين نسب التقدم للوضع الطبيعي
            $resetProgress = StudentCurriculumProgress::where('completion_percentage', 100)
                ->where('status', 'قيد التنفيذ')
                ->update([
                    'completion_percentage' => 0,
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->info("✅ تم حذف {$deletedSessions} جلسة تسميع مزيفة");
            $this->info("✅ تم إعادة تعيين {$resetProgress} سجل تقدم");
            $this->info('🎉 تم تنظيف النظام بنجاح!');

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ حدث خطأ أثناء التنظيف: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
