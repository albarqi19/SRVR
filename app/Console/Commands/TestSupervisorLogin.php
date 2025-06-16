<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class TestSupervisorLogin extends Command
{
    protected $signature = 'test:supervisor-login';
    protected $description = 'اختبار تسجيل دخول المشرفين عبر API';

    public function handle()
    {
        $this->info('=== اختبار تسجيل دخول المشرفين عبر API ===');
        $this->line('');

        // العثور على مشرف للاختبار
        $supervisor = User::role('supervisor')->where('is_active', true)->first();
        
        if (!$supervisor) {
            $this->error('لم يتم العثور على أي مشرف نشط في النظام');
            return;
        }

        $this->info("سيتم اختبار تسجيل الدخول للمشرف: {$supervisor->name}");
        $this->line("رقم الهوية: {$supervisor->identity_number}");
        $this->line('');

        // اختبار تسجيل الدخول بكلمات المرور المختلفة
        $passwords = [
            'supervisor123',
            '123456',
            $supervisor->identity_number, // رقم الهوية
            'password'
        ];

        foreach ($passwords as $password) {
            $this->info("اختبار كلمة المرور: {$password}");
            
            try {
                $response = Http::accept('application/json')
                    ->post('http://127.0.0.1:8000/api/auth/supervisor/login', [
                        'identity_number' => $supervisor->identity_number,
                        'password' => $password
                    ]);

                if ($response->successful()) {
                    $this->success('✅ تم تسجيل الدخول بنجاح!');
                    $data = $response->json();
                    $this->line("البيانات المُرجعة:");
                    $this->line(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    $this->line('');
                    
                    // اختبار مسارات API الأخرى
                    $this->testOtherApiRoutes();
                    return;
                } else {
                    $this->warn("❌ فشل تسجيل الدخول");
                    $this->line("الرد: " . $response->body());
                    $this->line('');
                }
                
            } catch (\Exception $e) {
                $this->error("خطأ في الاتصال: " . $e->getMessage());
            }
        }

        $this->error('فشل في تسجيل الدخول بجميع كلمات المرور المجربة');
        $this->line('');
        $this->info('يمكنك تجربة إعادة تعيين كلمة المرور باستخدام الأمر:');
        $this->line('php artisan supervisor:reset-password');
    }

    private function testOtherApiRoutes()
    {
        $this->info('اختبار مسارات API الأخرى:');
        $this->line('---------------------------');

        // اختبار قائمة المشرفين
        try {
            $response = Http::get('http://127.0.0.1:8000/api/supervisors');
            if ($response->successful()) {
                $this->info('✅ GET /api/supervisors - يعمل بنجاح');
                $data = $response->json();
                $this->line("عدد المشرفين: " . count($data['data'] ?? []));
            } else {
                $this->warn('❌ GET /api/supervisors - فشل');
            }
        } catch (\Exception $e) {
            $this->error("خطأ في GET /api/supervisors: " . $e->getMessage());
        }

        // اختبار لوحة تحكم المشرف
        try {
            $response = Http::get('http://127.0.0.1:8000/api/supervisor/dashboard');
            if ($response->successful()) {
                $this->info('✅ GET /api/supervisor/dashboard - يعمل بنجاح');
            } else {
                $this->warn('❌ GET /api/supervisor/dashboard - فشل');
            }
        } catch (\Exception $e) {
            $this->error("خطأ في GET /api/supervisor/dashboard: " . $e->getMessage());
        }

        $this->line('');
    }
}
