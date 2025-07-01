<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TestAllTeachersMapping extends Command
{
    protected $signature = 'test:all-teachers-mapping {--base-url=https://inviting-pleasantly-barnacle.ngrok-free.app} {--limit=5 : عدد المعلمين للاختبار}';
    protected $description = 'اختبار mapping لجميع المعلمين';

    public function handle()
    {
        $baseUrl = $this->option('base-url');
        $limit = (int) $this->option('limit');
        
        $this->info('🧪 اختبار teacher_id mapping لجميع المعلمين...');
        $this->newLine();

        // 1. جلب المعلمين مع user_ids من API
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'ngrok-skip-browser-warning' => 'true'
            ])->withoutVerifying()->get("{$baseUrl}/api/teachers/with-user-ids");

            if (!$response->successful()) {
                $this->error('❌ فشل في جلب قائمة المعلمين من API');
                return;
            }

            $data = $response->json();
            if (!$data['success']) {
                $this->error('❌ API error: ' . $data['message']);
                return;
            }

            $teachers = collect($data['data']);
            $this->info("📊 تم العثور على {$teachers->count()} معلم");

        } catch (\Exception $e) {
            $this->error('❌ خطأ في الاتصال: ' . $e->getMessage());
            return;
        }

        $this->newLine();
        
        // 2. عرض حالة جميع المعلمين
        $this->info('📋 حالة المعلمين:');
        $withUsers = $teachers->where('user_id', '!=', null);
        $withoutUsers = $teachers->where('user_id', null);
        
        $this->info("   ✅ معلمين لديهم user_id: {$withUsers->count()}");
        $this->info("   ❌ معلمين بدون user_id: {$withoutUsers->count()}");

        if ($withoutUsers->count() > 0) {
            $this->newLine();
            $this->warn('⚠️ المعلمون بدون user_id:');
            foreach ($withoutUsers->take(10) as $teacher) {
                $this->warn("   - {$teacher['teacher_name']} (teacher_id: {$teacher['teacher_id']})");
            }
            
            if ($withoutUsers->count() > 10) {
                $remaining = $withoutUsers->count() - 10;
                $this->warn("   ... و {$remaining} معلم آخر");
            }
        }

        $this->newLine();
        
        // 3. اختبار عينة من المعلمين
        $teachersToTest = $withUsers->take($limit);
        $this->info("🧪 اختبار {$teachersToTest->count()} معلم:");
        
        $successfulTests = 0;
        
        foreach ($teachersToTest as $teacher) {
            $teacherId = $teacher['teacher_id'];
            $userId = $teacher['user_id'];
            $name = $teacher['teacher_name'];
            
            $this->line("🔄 اختبار {$name} (teacher_id: {$teacherId} → user_id: {$userId})");
            
            // اختبار الحصول على user_id
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'ngrok-skip-browser-warning' => 'true'
                ])->withoutVerifying()->get("{$baseUrl}/api/teachers/get-user-id/{$teacherId}");

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success'] && $data['data']['teacher_id_for_api'] == $userId) {
                        $this->line("   ✅ API mapping يعمل بشكل صحيح");
                        $successfulTests++;
                    } else {
                        $this->line("   ❌ API mapping غير صحيح");
                    }
                } else {
                    $this->line("   ❌ فشل API call");
                }
            } catch (\Exception $e) {
                $this->line("   ❌ خطأ: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('📊 نتائج الاختبار:');
        $this->info("   ✅ اختبارات ناجحة: {$successfulTests}/{$teachersToTest->count()}");
        
        if ($successfulTests == $teachersToTest->count()) {
            $this->info('🎉 جميع الاختبارات نجحت! النظام يعمل بشكل مثالي!');
        } else {
            $this->warn('⚠️ بعض الاختبارات فشلت - تحقق من البيانات');
        }

        $this->newLine();
        $this->info('💡 التوصيات:');
        $this->info('1. في Frontend: يمكن استخدام أي teacher_id (التحويل تلقائي)');
        $this->info('2. للتأكد: استخدم /api/teachers/get-user-id/{teacherId}');
        $this->info('3. لقائمة كاملة: استخدم /api/teachers/with-user-ids');
    }
}
