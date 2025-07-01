<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TestStudentTransferApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:student-transfer-api {--base-url=https://inviting-pleasantly-barnacle.ngrok-free.app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار API نقل الطلاب مع المصادقة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = $this->option('base-url');
        
        $this->info('🚀 بدء اختبار API نقل الطلاب...');
        $this->newLine();

        // 1. البحث عن مستخدم للاختبار
        $this->info('1️⃣ البحث عن مستخدم للاختبار...');
        $user = User::where('email', 'demo@test.com')->first();
        
        if (!$user) {
            $this->info('إنشاء مستخدم تجريبي...');
            $user = User::create([
                'name' => 'Demo User',
                'username' => 'demo_user',
                'email' => 'demo@test.com',
                'password' => bcrypt('123456'),
                'national_id' => '1234567890'
            ]);
        }
        
        $this->info("✅ المستخدم: {$user->name} ({$user->email})");
        $this->newLine();

        // 2. تسجيل الدخول والحصول على Token
        $this->info('2️⃣ تسجيل الدخول للحصول على Token...');
        
        $loginResponse = Http::withoutVerifying()->withHeaders([
            'ngrok-skip-browser-warning' => 'true',
            'Content-Type' => 'application/json'
        ])->post($baseUrl . '/api/auth/login', [
            'email' => $user->email,
            'password' => '123456'
        ]);

        if (!$loginResponse->successful()) {
            $this->error('❌ فشل في تسجيل الدخول');
            $this->info('الاستجابة: ' . $loginResponse->body());
            return;
        }

        $loginData = $loginResponse->json();
        
        if (!isset($loginData['token'])) {
            $this->error('❌ لم يتم الحصول على Token');
            $this->info('الاستجابة: ' . $loginResponse->body());
            return;
        }

        $token = $loginData['token'];
        $this->info('✅ تم الحصول على Token بنجاح');
        $this->newLine();

        // 3. التحقق من وجود بيانات اختبار
        $this->info('3️⃣ التحقق من بيانات الاختبار...');
        
        // التحقق من الطلاب
        $studentCount = DB::table('students')->count();
        $this->info("عدد الطلاب في قاعدة البيانات: {$studentCount}");
        
        // التحقق من الحلقات
        $circleCount = DB::table('quran_circles')->count();
        $this->info("عدد الحلقات في قاعدة البيانات: {$circleCount}");
        
        if ($studentCount == 0 || $circleCount == 0) {
            $this->warn('⚠️ لا توجد بيانات كافية للاختبار');
            return;
        }
        
        // الحصول على أول طالب وحلقة للاختبار
        $student = DB::table('students')->first();
        $circle = DB::table('quran_circles')->first();
        
        $this->info("سيتم اختبار نقل الطالب: {$student->name} (ID: {$student->id})");
        $this->info("إلى الحلقة: {$circle->name} (ID: {$circle->id})");
        $this->newLine();

        // 4. اختبار API نقل الطلاب
        $this->info('4️⃣ اختبار API نقل الطلاب...');
        
        $transferData = [
            'student_id' => $student->id,
            'transfer_reason' => 'اختبار النقل عبر API',
            'requested_circle_id' => $circle->id,
            'notes' => 'هذا اختبار تلقائي لنقل الطلاب'
        ];
        
        $transferResponse = Http::withoutVerifying()->withHeaders([
            'ngrok-skip-browser-warning' => 'true',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($baseUrl . '/api/supervisors/student-transfer', $transferData);

        $this->info('📡 إرسال طلب النقل...');
        $this->newLine();

        // 5. عرض النتائج
        $this->info('5️⃣ نتائج الاختبار:');
        $this->info('HTTP Status Code: ' . $transferResponse->status());
        
        if ($transferResponse->successful()) {
            $this->info('✅ نجح الطلب!');
            $responseData = $transferResponse->json();
            if (isset($responseData['message'])) {
                $this->info('الرسالة: ' . $responseData['message']);
            }
            if (isset($responseData['data']['id'])) {
                $this->info('معرف طلب النقل: ' . $responseData['data']['id']);
            }
        } else {
            $this->error('❌ فشل الطلب');
            $this->info('رمز الخطأ: ' . $transferResponse->status());
            $errorData = $transferResponse->json();
            if (isset($errorData['message'])) {
                $this->error('رسالة الخطأ: ' . $errorData['message']);
            }
        }
        
        $this->newLine();
        
        // 6. التحقق من قاعدة البيانات
        if ($transferResponse->successful()) {
            $this->info('6️⃣ التحقق من قاعدة البيانات...');
            $transferRequests = DB::table('student_transfer_requests')
                ->where('student_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($transferRequests) {
                $this->info('✅ تم إنشاء طلب النقل في قاعدة البيانات');
                $this->info("ID طلب النقل: {$transferRequests->id}");
                $this->info("الحالة: {$transferRequests->status}");
            } else {
                $this->warn('⚠️ لم يتم العثور على طلب النقل في قاعدة البيانات');
            }
        }
        
        $this->newLine();
        $this->info('🎉 انتهى الاختبار');
    }
}
