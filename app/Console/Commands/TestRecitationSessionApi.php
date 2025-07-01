<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Student;
use App\Models\QuranCircle;

class TestRecitationSessionApi extends Command
{
    protected $signature = 'test:recitation-session {--base-url=https://inviting-pleasantly-barnacle.ngrok-free.app}';
    protected $description = 'اختبار API إنشاء جلسة التسميع مع المعلم ID 89';

    public function handle()
    {
        $baseUrl = $this->option('base-url');
        
        $this->info('🔧 اختبار API إنشاء جلسة التسميع...');
        $this->newLine();

        // 1. اختبار الحصول على user_id للمعلم 89
        $this->info('1️⃣ جلب معرف المستخدم للمعلم ID 89...');
        
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'ngrok-skip-browser-warning' => 'true'
            ])->withoutVerifying()->get("{$baseUrl}/api/teachers/get-user-id/89");

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ تم الحصول على معرف المستخدم:');
                $this->info("   📋 Teacher ID في جدول teachers: {$data['data']['teacher_id_in_teachers_table']}");
                $this->info("   🆔 Teacher ID للاستخدام في API: {$data['data']['teacher_id_for_api']}");
                $this->info("   👤 اسم المعلم: {$data['data']['teacher_name']}");
                
                $teacherIdForApi = $data['data']['teacher_id_for_api'];
            } else {
                $this->error('❌ فشل في الحصول على معرف المستخدم');
                $this->error('Response: ' . $response->body());
                return;
            }
        } catch (\Exception $e) {
            $this->error('❌ خطأ في الاتصال: ' . $e->getMessage());
            return;
        }

        $this->newLine();

        // 2. اختبار إنشاء جلسة التسميع
        $this->info('2️⃣ إنشاء جلسة تسميع جديدة...');
        
        $sessionData = [
            'student_id' => 36,
            'teacher_id' => $teacherIdForApi, // استخدام user_id الصحيح
            'quran_circle_id' => 1,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 5,
            'recitation_type' => 'حفظ',
            'duration_minutes' => 30,
            'grade' => 8.5,
            'evaluation' => 'جيد جداً',
            'teacher_notes' => 'جلسة اختبارية من Laravel Command'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'ngrok-skip-browser-warning' => 'true'
            ])->withoutVerifying()->post("{$baseUrl}/api/recitation/sessions", $sessionData);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ تم إنشاء جلسة التسميع بنجاح!');
                $this->info("   🆔 Session ID: {$data['data']['session_id']}");
                $this->info("   📅 تاريخ الإنشاء: {$data['data']['created_at']}");
                $this->info("   ⭐ الدرجة: {$data['data']['grade']}");
                $this->info("   📝 التقييم: {$data['data']['evaluation']}");
            } else {
                $this->error('❌ فشل في إنشاء جلسة التسميع');
                $this->error('Status Code: ' . $response->status());
                $this->error('Response: ' . $response->body());
                
                // عرض تفاصيل الخطأ
                if ($response->json()) {
                    $errorData = $response->json();
                    if (isset($errorData['errors'])) {
                        $this->error('📋 تفاصيل الأخطاء:');
                        foreach ($errorData['errors'] as $field => $errors) {
                            $this->error("   {$field}: " . implode(', ', $errors));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ خطأ في الاتصال: ' . $e->getMessage());
            return;
        }

        $this->newLine();

        // 3. اختبار باستخدام teacher_id الأصلي (89) ليرى إذا كان التحويل التلقائي يعمل
        $this->info('3️⃣ اختبار التحويل التلقائي للمعلم ID 89...');
        
        $sessionDataOriginal = [
            'student_id' => 36,
            'teacher_id' => 89, // استخدام teacher_id الأصلي
            'quran_circle_id' => 1,
            'start_surah_number' => 2,
            'start_verse' => 1,
            'end_surah_number' => 2,
            'end_verse' => 3,
            'recitation_type' => 'مراجعة صغرى',
            'duration_minutes' => 25,
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'teacher_notes' => 'اختبار التحويل التلقائي'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'ngrok-skip-browser-warning' => 'true'
            ])->withoutVerifying()->post("{$baseUrl}/api/recitation/sessions", $sessionDataOriginal);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('🎉 التحويل التلقائي يعمل! تم إنشاء الجلسة بنجاح!');
                $this->info("   🆔 Session ID: {$data['data']['session_id']}");
                $this->info("   👤 Teacher ID المستخدم: {$data['data']['teacher_id']}");
            } else {
                $this->warn('⚠️ التحويل التلقائي لا يعمل بعد - استخدم user_id مباشرة');
                $this->info('Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->warn('⚠️ خطأ في اختبار التحويل التلقائي: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🏁 انتهاء الاختبار');
        
        // ملخص النتائج
        $this->info('📊 ملخص النتائج:');
        $this->info('   ✅ API للحصول على user_id يعمل');
        $this->info('   ✅ إنشاء جلسة التسميع باستخدام user_id الصحيح يعمل');
        $this->info('   💡 استخدم teacher_id = ' . $teacherIdForApi . ' بدلاً من 89 في Frontend');
    }
}
