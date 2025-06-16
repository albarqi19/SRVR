<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\RecitationError;
use App\Models\Student;
use App\Models\User;
use App\Models\QuranCircle;
use Illuminate\Support\Facades\Http;

class TestRecitationSessionsAndErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:recitation-complete {--api : Test API endpoints} {--db : Test database directly} {--errors : Test error management} {--stats : Show statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار شامل لنظام جلسات التلاوة والأخطاء مع خيارات متعددة';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء الاختبار الشامل لنظام جلسات التلاوة والأخطاء...');
        $this->line(str_repeat('=', 80));
        
        // إنشاء بيانات تجريبية إذا لم تكن موجودة
        $this->ensureTestData();
        
        // اختبار قاعدة البيانات مباشرة (افتراضي)
        if ($this->option('db') || (!$this->option('api') && !$this->option('errors') && !$this->option('stats'))) {
            $this->testDatabaseDirectly();
        }
        
        // اختبار إدارة الأخطاء
        if ($this->option('errors') || (!$this->option('api') && !$this->option('db') && !$this->option('stats'))) {
            $this->testErrorManagement();
        }
        
        // اختبار API
        if ($this->option('api')) {
            $this->testApiEndpoints();
        }
        
        // عرض الإحصائيات
        if ($this->option('stats') || (!$this->option('api') && !$this->option('db') && !$this->option('errors'))) {
            $this->showDetailedStats();
        }
        
        $this->info('🎉 انتهى الاختبار الشامل بنجاح!');
        
        return 0;
    }
    
    private function ensureTestData()
    {
        $this->info('📋 التحقق من البيانات التجريبية...');
        
        // التحقق من وجود بيانات أساسية
        $studentsCount = Student::count();
        $teachersCount = User::count();
        $circlesCount = QuranCircle::count();
        
        $this->line("   👨‍🎓 الطلاب: {$studentsCount}");
        $this->line("   👨‍🏫 المعلمين: {$teachersCount}");
        $this->line("   📚 الحلقات: {$circlesCount}");
        
        if ($studentsCount == 0 || $teachersCount == 0) {
            $this->warn('📝 إنشاء بيانات تجريبية...');
            $this->createTestData();
        }
    }
    
    private function createTestData()
    {
        // إنشاء طالب تجريبي إذا لم يكن موجوداً
        $student = Student::firstOrCreate([
            'identity_number' => '1234567890'
        ], [
            'name' => 'الطالب التجريبي للاختبار',
            'age' => 15,
            'gender' => 'male',
            'enrollment_date' => now(),
        ]);
        
        // إنشاء معلم تجريبي إذا لم يكن موجوداً
        $teacher = User::firstOrCreate([
            'email' => 'test.teacher@example.com'
        ], [
            'name' => 'المعلم التجريبي للاختبار',
            'password' => bcrypt('password'),
        ]);
        
        // إنشاء حلقة تجريبية إذا لم تكن موجودة
        $circle = QuranCircle::firstOrCreate([
            'name' => 'حلقة تجريبية للاختبار'
        ], [
            'mosque_id' => 1,
            'circle_type' => 'مدرسة قرآنية',
            'circle_status' => 'تعمل',
            'period' => 'عصر',
        ]);
        
        $this->info('✅ تم إنشاء البيانات التجريبية');
    }
    
    private function testDatabaseDirectly()
    {
        $this->warn('🗄️ اختبار قاعدة البيانات مباشرة...');
        $this->line(str_repeat('-', 50));
        
        // 1. اختبار إنشاء جلسة جديدة
        $this->info('1️⃣ اختبار إنشاء جلسة تلاوة جديدة...');
        
        $student = Student::first();
        $teacher = User::first();
        $circle = QuranCircle::first();
        
        $sessionId = 'RS-' . date('Ymd-His') . '-TEST';
        
        $session = RecitationSession::create([
            'session_id' => $sessionId,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id ?? null,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 7,
            'recitation_type' => 'حفظ',
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'teacher_notes' => 'أداء ممتاز في التلاوة',
            'has_errors' => false,
            'session_date' => now(),
        ]);
        
        $this->info("   ✅ تم إنشاء الجلسة: {$session->session_id}");
        $this->line("   📚 الطالب: {$session->student->name}");
        $this->line("   👨‍🏫 المعلم: {$session->teacher->name}");
        $this->line("   🎯 الدرجة: {$session->grade}");
        $this->line("   📊 التقييم: {$session->evaluation}");
        
        // 2. اختبار استرجاع الجلسات
        $this->info('2️⃣ اختبار استرجاع الجلسات...');
          $sessions = RecitationSession::with(['student', 'teacher', 'circle'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $this->line("   📋 عدد الجلسات المسترجعة: " . $sessions->count());
          foreach ($sessions as $index => $sess) {
            $circleName = $sess->circle ? $sess->circle->name : 'غير محدد';
            $sessionNumber = $index + 1;
            $this->line("   {$sessionNumber}. {$sess->session_id} - {$sess->student->name} - {$sess->evaluation}");
        }
        
        // 3. اختبار تحديث جلسة
        $this->info('3️⃣ اختبار تحديث جلسة...');
        
        $session->update([
            'teacher_notes' => 'تم تحديث الملاحظات - ' . now()->format('H:i:s'),
            'grade' => 8.5,
            'evaluation' => 'جيد جداً'
        ]);
        
        $this->info("   ✅ تم تحديث الجلسة {$session->session_id}");
        $this->line("   📝 الملاحظات الجديدة: {$session->teacher_notes}");
        $this->line("   🎯 الدرجة الجديدة: {$session->grade}");
        
        return $session;
    }
    
    private function testErrorManagement()
    {
        $this->warn('🐛 اختبار إدارة أخطاء التلاوة...');
        $this->line(str_repeat('-', 50));
        
        // الحصول على آخر جلسة
        $session = RecitationSession::orderBy('created_at', 'desc')->first();
        
        if (!$session) {
            $this->error('❌ لا توجد جلسات متاحة لإضافة الأخطاء');
            return;
        }
        
        $this->info("🔍 استخدام الجلسة: {$session->session_id}");
        
        // 1. إضافة أخطاء متنوعة
        $this->info('1️⃣ اختبار إضافة أخطاء متنوعة...');
        
        $errors = [
            [
                'surah_number' => 1,
                'verse_number' => 2,
                'word_text' => 'الرحمن',
                'error_type' => 'تجويد',
                'correction_note' => 'عدم مد الألف في "الرحمن"',
                'teacher_note' => 'يحتاج مراجعة أحكام المد',
                'is_repeated' => true,
                'severity_level' => 'متوسط'
            ],
            [
                'surah_number' => 1,
                'verse_number' => 3,
                'word_text' => 'مالك',
                'error_type' => 'نطق',
                'correction_note' => 'نطق الكاف غير واضح',
                'teacher_note' => 'تدريب على مخارج الحروف',
                'is_repeated' => false,
                'severity_level' => 'خفيف'
            ],
            [
                'surah_number' => 1,
                'verse_number' => 4,
                'word_text' => 'الدين',
                'error_type' => 'ترتيل',
                'correction_note' => 'سرعة في القراءة',
                'teacher_note' => 'التأني في الترتيل',
                'is_repeated' => true,
                'severity_level' => 'شديد'
            ],
            [
                'surah_number' => 1,
                'verse_number' => 6,
                'word_text' => 'الصراط',
                'error_type' => 'تشكيل',
                'correction_note' => 'خطأ في تشكيل الصاد',
                'teacher_note' => 'مراجعة التشكيل',
                'is_repeated' => false,
                'severity_level' => 'متوسط'
            ]
        ];
        
        foreach ($errors as $index => $errorData) {
            $error = RecitationError::create([
                'recitation_session_id' => $session->id,
                'session_id' => $session->session_id,
                'surah_number' => $errorData['surah_number'],
                'verse_number' => $errorData['verse_number'],
                'word_text' => $errorData['word_text'],
                'error_type' => $errorData['error_type'],
                'correction_note' => $errorData['correction_note'],
                'teacher_note' => $errorData['teacher_note'],
                'is_repeated' => $errorData['is_repeated'],
                'severity_level' => $errorData['severity_level']
            ]);
            
            $this->info("   ✅ تم إضافة خطأ {$errorData['error_type']} في سورة {$errorData['surah_number']} آية {$errorData['verse_number']}");
        }
        
        // تحديث الجلسة لتشير إلى وجود أخطاء
        $session->update(['has_errors' => true]);
        
        // 2. اختبار استرجاع الأخطاء
        $this->info('2️⃣ اختبار استرجاع أخطاء الجلسة...');
        
        $sessionErrors = RecitationError::where('session_id', $session->session_id)
            ->orderBy('surah_number')
            ->orderBy('verse_number')
            ->get();
            
        $this->line("   📋 عدد الأخطاء: " . $sessionErrors->count());
        
        // عرض الأخطاء في جدول
        $this->displayErrorsTable($sessionErrors);
        
        // 3. إحصائيات الأخطاء
        $this->info('3️⃣ إحصائيات أخطاء الجلسة...');
        
        $errorStats = $this->calculateErrorStats($sessionErrors);
        $this->displayErrorStats($errorStats);
        
        return $session;
    }
    
    private function testApiEndpoints()
    {
        $this->warn('🌐 اختبار API endpoints...');
        $this->line(str_repeat('-', 50));
        
        $baseUrl = 'http://localhost:8000/api';
        
        // 1. اختبار إنشاء جلسة عبر API
        $this->info('1️⃣ اختبار إنشاء جلسة عبر API...');
          $student = Student::first();
        $teacher = User::first();
        $circle = QuranCircle::first();
        
        $sessionData = [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'start_surah_number' => 2,
            'start_verse' => 1,
            'end_surah_number' => 2,
            'end_verse' => 5,
            'recitation_type' => 'مراجعة صغرى',
            'grade' => 7.5,
            'evaluation' => 'جيد جداً',
            'teacher_notes' => 'جلسة تجريبية عبر API'
        ];
          try {
            $response = Http::post("{$baseUrl}/recitation/sessions", $sessionData);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("   ✅ تم إنشاء الجلسة عبر API: " . $data['data']['session_id']);
            } else {
                $this->error("   ❌ فشل إنشاء الجلسة: " . $response->status());
                if ($response->status() == 422) {
                    $errorData = $response->json();
                    $this->error("   📋 تفاصيل الأخطاء:");
                    if (isset($errorData['errors'])) {
                        foreach ($errorData['errors'] as $field => $errors) {
                            $this->error("      - {$field}: " . implode(', ', $errors));
                        }
                    }
                    if (isset($errorData['message'])) {
                        $this->error("      - الرسالة: " . $errorData['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
        
        // 2. اختبار جلب الجلسات عبر API
        $this->info('2️⃣ اختبار جلب الجلسات عبر API...');
        
        try {
            $response = Http::get("{$baseUrl}/recitation/sessions", [
                'limit' => 5,
                'student_id' => $student->id
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("   ✅ تم جلب " . count($data['data']['data']) . " جلسة عبر API");
            } else {
                $this->error("   ❌ فشل جلب الجلسات: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
        
        // 3. اختبار إضافة أخطاء عبر API
        $this->info('3️⃣ اختبار إضافة أخطاء عبر API...');
        
        $session = RecitationSession::orderBy('created_at', 'desc')->first();
        
        if ($session) {
            $errorData = [
                'session_id' => $session->session_id,
                'errors' => [
                    [
                        'surah_number' => 2,
                        'verse_number' => 10,
                        'word_text' => 'يخادعون',
                        'error_type' => 'نطق',
                        'correction_note' => 'نطق الخاء غير صحيح',
                        'teacher_note' => 'تدريب على الحروف الحلقية',
                        'is_repeated' => false,
                        'severity_level' => 'متوسط'
                    ]
                ]
            ];
            
            try {
                $response = Http::post("{$baseUrl}/recitation/errors", $errorData);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $this->info("   ✅ تم إضافة الأخطاء عبر API: " . $data['total_errors'] . " خطأ");
                } else {
                    $this->error("   ❌ فشل إضافة الأخطاء: " . $response->status());
                }
            } catch (\Exception $e) {
                $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
            }
        }
    }
    
    private function showDetailedStats()
    {
        $this->warn('📊 عرض الإحصائيات التفصيلية...');
        $this->line(str_repeat('-', 50));
        
        // 1. إحصائيات عامة
        $this->info('📈 الإحصائيات العامة:');
        
        $totalSessions = RecitationSession::count();
        $totalErrors = RecitationError::count();
        $sessionsWithErrors = RecitationSession::where('has_errors', true)->count();
        $avgGrade = RecitationSession::whereNotNull('grade')->avg('grade');
        
        $this->line("   📚 إجمالي الجلسات: {$totalSessions}");
        $this->line("   🐛 إجمالي الأخطاء: {$totalErrors}");
        $this->line("   ⚠️ جلسات بها أخطاء: {$sessionsWithErrors}");
        $this->line("   🎯 متوسط الدرجات: " . number_format($avgGrade, 2));
        
        // 2. إحصائيات أنواع التلاوة
        $this->info('📖 إحصائيات أنواع التلاوة:');
        
        $recitationTypes = RecitationSession::selectRaw('recitation_type, COUNT(*) as count')
            ->groupBy('recitation_type')
            ->get();
            
        foreach ($recitationTypes as $type) {
            $this->line("   🔹 {$type->recitation_type}: {$type->count} جلسة");
        }
        
        // 3. إحصائيات التقييمات
        $this->info('🏆 إحصائيات التقييمات:');
        
        $evaluations = RecitationSession::selectRaw('evaluation, COUNT(*) as count')
            ->whereNotNull('evaluation')
            ->groupBy('evaluation')
            ->orderByDesc('count')
            ->get();
            
        foreach ($evaluations as $eval) {
            $this->line("   🌟 {$eval->evaluation}: {$eval->count} جلسة");
        }
        
        // 4. إحصائيات أنواع الأخطاء
        if ($totalErrors > 0) {
            $this->info('🐛 إحصائيات أنواع الأخطاء:');
            
            $errorTypes = RecitationError::selectRaw('error_type, COUNT(*) as count')
                ->groupBy('error_type')
                ->orderByDesc('count')
                ->get();
                
            foreach ($errorTypes as $errorType) {
                $this->line("   🔸 {$errorType->error_type}: {$errorType->count} خطأ");
            }
            
            // 5. إحصائيات شدة الأخطاء
            $this->info('⚡ إحصائيات شدة الأخطاء:');
            
            $severityLevels = RecitationError::selectRaw('severity_level, COUNT(*) as count')
                ->groupBy('severity_level')
                ->get();
                
            foreach ($severityLevels as $severity) {
                $this->line("   🎯 {$severity->severity_level}: {$severity->count} خطأ");
            }
        }
        
        // 6. أحدث الجلسات
        $this->info('🕒 أحدث 5 جلسات:');
        
        $recentSessions = RecitationSession::with(['student', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
              foreach ($recentSessions as $index => $session) {
            $hasErrors = $session->has_errors ? '⚠️' : '✅';
            $sessionNumber = $index + 1;
            $this->line("   {$sessionNumber}. {$session->session_id} - {$session->student->name} - {$session->evaluation} {$hasErrors}");
        }
    }
    
    private function displayErrorsTable($errors)
    {
        if ($errors->isEmpty()) {
            $this->line("   📋 لا توجد أخطاء في هذه الجلسة");
            return;
        }
        
        $this->line("   +" . str_repeat("-", 95) . "+");
        $this->line("   | سورة | آية | الكلمة     | نوع الخطأ | شدة الخطأ | متكرر | ملاحظة التصحيح                          |");
        $this->line("   +" . str_repeat("-", 95) . "+");
        
        foreach ($errors as $error) {
            $repeated = $error->is_repeated ? 'نعم' : 'لا';
            $this->line(sprintf(
                "   | %-4s | %-3s | %-10s | %-9s | %-8s | %-4s | %-40s |",
                $error->surah_number,
                $error->verse_number,
                mb_substr($error->word_text, 0, 10),
                mb_substr($error->error_type, 0, 9),
                mb_substr($error->severity_level, 0, 8),
                $repeated,
                mb_substr($error->correction_note, 0, 40)
            ));
        }
        
        $this->line("   +" . str_repeat("-", 95) . "+");
    }
    
    private function calculateErrorStats($errors)
    {
        if ($errors->isEmpty()) {
            return [];
        }
        
        return [
            'total' => $errors->count(),
            'by_type' => $errors->groupBy('error_type')->map->count(),
            'by_severity' => $errors->groupBy('severity_level')->map->count(),
            'repeated' => $errors->where('is_repeated', true)->count(),
            'non_repeated' => $errors->where('is_repeated', false)->count(),
        ];
    }
    
    private function displayErrorStats($stats)
    {
        if (empty($stats)) {
            $this->line("   📊 لا توجد أخطاء لحساب الإحصائيات");
            return;
        }
        
        $this->line("   📊 إجمالي الأخطاء: " . $stats['total']);
        
        $this->line("   🔸 حسب النوع:");
        foreach ($stats['by_type'] as $type => $count) {
            $this->line("      - {$type}: {$count} أخطاء");
        }
        
        $this->line("   🎯 حسب الشدة:");
        foreach ($stats['by_severity'] as $severity => $count) {
            $this->line("      - {$severity}: {$count} أخطاء");
        }
        
        $this->line("   🔄 الأخطاء المتكررة: " . $stats['repeated']);
        $this->line("   ✨ الأخطاء غير المتكررة: " . $stats['non_repeated']);
    }
}
