<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\RecitationError;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TestRecitationErrorsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:recitation-errors-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار API أخطاء التسميع مع إنشاء البيانات التجريبية';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء اختبار API أخطاء التسميع...');
        
        // 1. إنشاء أو جلب بيانات تجريبية
        $session = $this->createTestSession();
        
        if (!$session) {
            $this->error('❌ فشل في إنشاء جلسة التسميع التجريبية');
            return 1;
        }
        
        $this->info("✅ تم إنشاء جلسة التسميع: {$session->session_id}");
        
        // 2. اختبار إضافة خطأ واحد
        $this->testSingleError($session);
        
        // 3. اختبار إضافة عدة أخطاء
        $this->testMultipleErrors($session);
        
        // 4. اختبار جلب الأخطاء
        $this->testGetErrors($session);
        
        $this->info('🎉 انتهى اختبار API بنجاح!');
        
        return 0;
    }
    
    private function createTestSession()
    {
        // جلب أول طالب متاح أو إنشاؤه
        $student = Student::first() ?? Student::create([
            'name' => 'طالب تجريبي للاختبار',
            'identity_number' => '1234567890',
            'age' => 15,
            'gender' => 'male',
        ]);
        
        // جلب أول معلم متاح أو إنشاؤه
        $teacher = User::first() ?? User::create([
            'name' => 'معلم تجريبي للاختبار',
            'email' => 'test.teacher@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // إنشاء جلسة تسميع جديدة
        $sessionId = 'RS-' . date('Ymd-His') . '-TEST';
        
        return RecitationSession::create([
            'session_id' => $sessionId,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 7,
            'recitation_type' => 'حفظ',
            'grade' => 85.5,
            'notes' => 'جلسة تجريبية لاختبار API',
            'has_errors' => false,
            'session_date' => now(),
        ]);
    }
    
    private function testSingleError($session)
    {
        $this->info('🧪 اختبار إضافة خطأ واحد...');
        
        $data = [
            'session_id' => $session->session_id,
            'errors' => [
                [
                    'surah_number' => 1,
                    'verse_number' => 2,
                    'word_text' => 'الرحمن',
                    'error_type' => 'تجويد',
                    'correction_note' => 'يجب مد الرحمن',
                    'teacher_note' => 'خطأ في التجويد',
                    'is_repeated' => false,
                    'severity_level' => 'متوسط'
                ]
            ]
        ];
        
        $response = Http::post('http://localhost:8000/api/recitation/errors', $data);
        
        if ($response->successful()) {
            $this->info('✅ تم إضافة الخطأ بنجاح');
            $this->line('📊 الاستجابة: ' . $response->body());
        } else {
            $this->error('❌ فشل في إضافة الخطأ');
            $this->line('📋 رمز الخطأ: ' . $response->status());
            $this->line('📋 تفاصيل الخطأ: ' . $response->body());
        }
    }
    
    private function testMultipleErrors($session)
    {
        $this->info('🧪 اختبار إضافة عدة أخطاء...');
        
        $data = [
            'session_id' => $session->session_id,
            'errors' => [
                [
                    'surah_number' => 1,
                    'verse_number' => 3,
                    'word_text' => 'مالك',
                    'error_type' => 'نطق',
                    'correction_note' => 'نطق الكاف خاطئ',
                    'teacher_note' => 'تحتاج تدريب على النطق',
                    'is_repeated' => true,
                    'severity_level' => 'شديد'
                ],
                [
                    'surah_number' => 1,
                    'verse_number' => 4,
                    'word_text' => 'الدين',
                    'error_type' => 'ترتيل',
                    'correction_note' => 'سرعة في القراءة',
                    'teacher_note' => 'يجب التأني في القراءة',
                    'is_repeated' => false,
                    'severity_level' => 'خفيف'
                ]
            ]
        ];
        
        $response = Http::post('http://localhost:8000/api/recitation/errors', $data);
        
        if ($response->successful()) {
            $this->info('✅ تم إضافة الأخطاء المتعددة بنجاح');
            $responseData = $response->json();
            $this->line("📊 عدد الأخطاء المضافة: {$responseData['total_errors']}");
        } else {
            $this->error('❌ فشل في إضافة الأخطاء المتعددة');
            $this->line('📋 رمز الخطأ: ' . $response->status());
            $this->line('📋 تفاصيل الخطأ: ' . $response->body());
        }
    }
    
    private function testGetErrors($session)
    {
        $this->info('🧪 اختبار جلب الأخطاء...');
        
        $response = Http::get('http://localhost:8000/api/recitation/errors', [
            'session_id' => $session->session_id
        ]);
        
        if ($response->successful()) {
            $this->info('✅ تم جلب الأخطاء بنجاح');
            $responseData = $response->json();
            $this->line("📊 عدد الأخطاء الموجودة: " . count($responseData['data']['data']));
            
            // عرض تفاصيل الأخطاء
            foreach ($responseData['data']['data'] as $error) {
                $this->line("🔸 خطأ {$error['error_type']} في سورة {$error['surah_number']} آية {$error['verse_number']} - شدة: {$error['severity_level']}");
            }
        } else {
            $this->error('❌ فشل في جلب الأخطاء');
            $this->line('📋 رمز الخطأ: ' . $response->status());
            $this->line('📋 تفاصيل الخطأ: ' . $response->body());
        }
    }
}
