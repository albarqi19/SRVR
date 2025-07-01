<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GenerateTeacherMappingReport extends Command
{
    protected $signature = 'report:teacher-mapping {--export : تصدير التقرير إلى ملف}';
    protected $description = 'إنشاء تقرير شامل عن teacher_id mapping لجميع المعلمين';

    public function handle()
    {
        $this->info('📊 إنشاء تقرير شامل عن teacher_id mapping...');
        $this->newLine();

        // جلب جميع المعلمين مع user_ids المرتبطة
        $mappings = DB::table('teachers')
            ->leftJoin('users', function($join) {
                $join->on('users.name', '=', 'teachers.name')
                     ->orOn('users.email', '=', DB::raw("CONCAT('teacher_', teachers.id, '@garb.com')"));
            })
            ->select(
                'teachers.id as teacher_id',
                'teachers.name as teacher_name',
                'teachers.phone as teacher_phone',
                'users.id as user_id',
                'users.email as user_email',
                'users.username as username'
            )
            ->orderBy('teachers.id')
            ->get();

        $withMapping = $mappings->where('user_id', '!=', null);
        $withoutMapping = $mappings->where('user_id', null);

        $this->info('📈 إحصائيات عامة:');
        $this->info("   📊 إجمالي المعلمين: {$mappings->count()}");
        $this->info("   ✅ لديهم user_id: {$withMapping->count()}");
        $this->info("   ❌ بدون user_id: {$withoutMapping->count()}");
        
        $this->newLine();
        
        // عرض المعلمين الذين لديهم mapping
        if ($withMapping->count() > 0) {
            $this->info('✅ المعلمون الذين لديهم user_id:');
            $this->table(
                ['Teacher ID', 'اسم المعلم', 'User ID', 'البريد الإلكتروني', 'للاستخدام في API'],
                $withMapping->map(function($mapping) {
                    return [
                        $mapping->teacher_id,
                        $mapping->teacher_name,
                        $mapping->user_id,
                        $mapping->user_email,
                        "teacher_id: {$mapping->user_id}"
                    ];
                })->toArray()
            );
        }

        // عرض المعلمين الذين بدون mapping
        if ($withoutMapping->count() > 0) {
            $this->newLine();
            $this->warn('❌ المعلمون بدون user_id:');
            $this->table(
                ['Teacher ID', 'اسم المعلم', 'الهاتف', 'الحالة'],
                $withoutMapping->map(function($mapping) {
                    return [
                        $mapping->teacher_id,
                        $mapping->teacher_name,
                        $mapping->teacher_phone,
                        'يحتاج إنشاء user'
                    ];
                })->toArray()
            );
            
            $this->newLine();
            $this->warn('💡 لإنشاء users للمعلمين المفقودين:');
            $this->warn('php artisan create:users-for-all-teachers --force');
        }

        // تصدير إلى ملف
        if ($this->option('export')) {
            $this->exportToFile($mappings);
        }

        $this->newLine();
        $this->info('🎯 طرق الاستخدام في Frontend:');
        $this->info('1. استخدام teacher_id الأصلي (يتم التحويل تلقائياً)');
        $this->info('2. استخدام user_id مباشرة من الجدول أعلاه');
        $this->info('3. استخدام API: GET /api/teachers/get-user-id/{teacher_id}');
        
        $this->newLine();
        $this->info('📋 أمثلة للاستخدام:');
        
        // عرض أمثلة للمعلمين الأوائل
        $examples = $withMapping->take(3);
        foreach ($examples as $example) {
            $this->info("   المعلم: {$example->teacher_name}");
            $this->info("     - الطريقة القديمة: teacher_id: {$example->teacher_id}");
            $this->info("     - الطريقة الجديدة: teacher_id: {$example->user_id}");
            $this->info("     - كلاهما يعمل الآن! ✅");
        }
    }

    private function exportToFile($mappings)
    {
        $filename = 'teacher_mapping_report_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = storage_path('logs/' . $filename);
        
        $exportData = [
            'generated_at' => now()->toDateTimeString(),
            'total_teachers' => $mappings->count(),
            'with_user_id' => $mappings->where('user_id', '!=', null)->count(),
            'without_user_id' => $mappings->where('user_id', null)->count(),
            'mappings' => $mappings->map(function($mapping) {
                return [
                    'teacher_id' => $mapping->teacher_id,
                    'teacher_name' => $mapping->teacher_name,
                    'user_id' => $mapping->user_id,
                    'user_email' => $mapping->user_email,
                    'api_teacher_id' => $mapping->user_id ?? null,
                    'has_mapping' => $mapping->user_id !== null
                ];
            })->toArray()
        ];
        
        file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("📄 تم تصدير التقرير إلى: {$filepath}");
    }
}
