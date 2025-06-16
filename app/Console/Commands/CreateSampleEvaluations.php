<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\TeacherEvaluation;
use App\Models\User;
use Illuminate\Support\Carbon;

class CreateSampleEvaluations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluations:create-sample {--count=10 : عدد التقييمات المراد إنشاؤها}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء بيانات تجريبية لتقييمات المعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء إنشاء بيانات تجريبية لتقييمات المعلمين...');

        // التحقق من وجود معلمين في النظام
        $teachers = Teacher::all();
        if ($teachers->isEmpty()) {
            $this->error('❌ لا يوجد معلمين في النظام. يرجى إضافة معلمين أولاً.');
            return;
        }

        // التحقق من وجود مستخدمين كمقيمين
        $evaluators = User::all();
        if ($evaluators->isEmpty()) {
            $this->error('❌ لا يوجد مستخدمين في النظام. يرجى إضافة مستخدمين أولاً.');
            return;
        }

        $count = $this->option('count');
        $this->info("📝 سيتم إنشاء {$count} تقييمات تجريبية...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $evaluationPeriods = ['شهري', 'فصلي', 'نصف سنوي', 'سنوي', 'تقييم خاص'];
        $statuses = ['مسودة', 'مكتمل', 'معتمد', 'مراجعة'];
        $evaluatorRoles = ['مدير', 'مشرف', 'مشرف تربوي', 'معلم أول'];

        for ($i = 0; $i < $count; $i++) {
            // اختيار معلم عشوائي
            $teacher = $teachers->random();
            
            // اختيار مقيم عشوائي
            $evaluator = $evaluators->random();

            // إنشاء درجات عشوائية واقعية
            $performanceScore = $this->generateRealisticScore();
            $attendanceScore = $this->generateRealisticScore();
            $studentInteractionScore = $this->generateRealisticScore();
            $behaviorCooperationScore = $this->generateRealisticScore();
            $memorizationRecitationScore = $this->generateRealisticScore();
            $generalEvaluationScore = $this->generateRealisticScore();

            // حساب النتيجة الإجمالية
            $totalScore = $performanceScore + $attendanceScore + $studentInteractionScore + 
                         $behaviorCooperationScore + $memorizationRecitationScore + $generalEvaluationScore;

            // إنشاء التقييم
            TeacherEvaluation::create([
                'teacher_id' => $teacher->id,
                'performance_score' => $performanceScore,
                'attendance_score' => $attendanceScore,
                'student_interaction_score' => $studentInteractionScore,
                'behavior_cooperation_score' => $behaviorCooperationScore,
                'memorization_recitation_score' => $memorizationRecitationScore,
                'general_evaluation_score' => $generalEvaluationScore,
                'total_score' => $totalScore,
                'evaluation_date' => Carbon::now()->subDays(rand(1, 30)),
                'evaluation_period' => $evaluationPeriods[array_rand($evaluationPeriods)],
                'notes' => $this->generateEvaluationNotes($totalScore),
                'evaluator_id' => $evaluator->id,
                'evaluator_role' => $evaluatorRoles[array_rand($evaluatorRoles)],
                'status' => $statuses[array_rand($statuses)],
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // عرض إحصائيات التقييمات المنشأة
        $this->displayStatistics($count);

        $this->info('✅ تم إنشاء بيانات التقييمات التجريبية بنجاح!');
        $this->info('📊 يمكنك الآن الوصول إلى صفحة تقييمات المعلمين في لوحة التحكم.');
    }

    /**
     * توليد درجة واقعية للمعايير (بين 10-20)
     */
    private function generateRealisticScore(): float
    {
        // توزيع واقعي: معظم الدرجات تكون جيدة مع بعض التنوع
        $rand = rand(1, 100);
        
        if ($rand <= 10) {
            // 10% درجات ضعيفة (10-14)
            return round(rand(100, 140) / 10, 1);
        } elseif ($rand <= 30) {
            // 20% درجات متوسطة (14-16)
            return round(rand(140, 160) / 10, 1);
        } elseif ($rand <= 70) {
            // 40% درجات جيدة (16-18)
            return round(rand(160, 180) / 10, 1);
        } else {
            // 30% درجات ممتازة (18-20)
            return round(rand(180, 200) / 10, 1);
        }
    }

    /**
     * توليد ملاحظات تقييم حسب النتيجة
     */
    private function generateEvaluationNotes(float $totalScore): string
    {
        $notes = [
            'ممتاز' => [
                'أداء متميز في جميع المعايير',
                'معلم ملتزم ومتفاني في عمله',
                'تفاعل إيجابي مع الطلاب وحماسة في التدريس',
                'سلوك مثالي وتعاون ممتاز مع الزملاء',
                'إتقان عالي للتلاوة والحفظ',
            ],
            'جيد جداً' => [
                'أداء جيد جداً مع إمكانية للتحسين',
                'التزام بالحضور مع بعض الملاحظات البسيطة',
                'تفاعل جيد مع الطلاب',
                'سلوك طيب وتعاون مع الفريق',
                'مستوى جيد في الحفظ والتلاوة',
            ],
            'جيد' => [
                'أداء مقبول يحتاج إلى تطوير',
                'يحتاج إلى تحسين الالتزام بالحضور',
                'تفاعل متوسط مع الطلاب',
                'يحتاج إلى تطوير التعاون مع الزملاء',
                'مستوى متوسط في التلاوة',
            ],
            'مقبول' => [
                'أداء يحتاج إلى تحسين كبير',
                'مشاكل في الالتزام بالحضور',
                'ضعف في التفاعل مع الطلاب',
                'يحتاج إلى تدريب إضافي',
                'يحتاج إلى تحسين مستوى الحفظ',
            ],
            'ضعيف' => [
                'أداء ضعيف يحتاج إلى متابعة مكثفة',
                'مشاكل جدية في الالتزام',
                'صعوبة في التعامل مع الطلاب',
                'يحتاج إلى خطة تحسين عاجلة',
                'مستوى ضعيف يحتاج إلى تدريب مكثف',
            ]
        ];

        $grade = match (true) {
            $totalScore >= 90 => 'ممتاز',
            $totalScore >= 80 => 'جيد جداً',
            $totalScore >= 70 => 'جيد',
            $totalScore >= 60 => 'مقبول',
            default => 'ضعيف'
        };

        return $notes[$grade][array_rand($notes[$grade])];
    }

    /**
     * عرض إحصائيات التقييمات المنشأة
     */
    private function displayStatistics(int $count): void
    {
        $this->info('📊 إحصائيات التقييمات المنشأة:');
        
        $evaluations = TeacherEvaluation::latest()->take($count)->get();
        
        $excellent = $evaluations->where('total_score', '>=', 90)->count();
        $veryGood = $evaluations->whereBetween('total_score', [80, 89.9])->count();
        $good = $evaluations->whereBetween('total_score', [70, 79.9])->count();
        $acceptable = $evaluations->whereBetween('total_score', [60, 69.9])->count();
        $weak = $evaluations->where('total_score', '<', 60)->count();

        $this->table(
            ['التصنيف', 'العدد', 'النسبة'],
            [
                ['ممتاز (90-100%)', $excellent, round($excellent / $count * 100, 1) . '%'],
                ['جيد جداً (80-89%)', $veryGood, round($veryGood / $count * 100, 1) . '%'],
                ['جيد (70-79%)', $good, round($good / $count * 100, 1) . '%'],
                ['مقبول (60-69%)', $acceptable, round($acceptable / $count * 100, 1) . '%'],
                ['ضعيف (أقل من 60%)', $weak, round($weak / $count * 100, 1) . '%'],
            ]
        );

        $avgScore = round($evaluations->avg('total_score'), 2);
        $this->info("📈 متوسط النتائج: {$avgScore}%");
    }
}
