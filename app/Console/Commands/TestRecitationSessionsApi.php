<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\QuranCircle;
use Illuminate\Support\Facades\Http;

class TestRecitationSessionsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */    protected $signature = 'test:recitation-sessions {--create-data} {--test-api} {--test-errors} {--show-stats} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار API جلسات التسميع مع إنشاء بيانات تجريبية وإدارة الأخطاء';

    private $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء اختبار نظام جلسات التسميع...');
        $this->newLine();

        if ($this->option('all') || $this->option('create-data')) {
            $this->createTestData();
        }        if ($this->option('all') || $this->option('test-api')) {
            $this->testApi();
        }

        if ($this->option('all') || $this->option('test-errors')) {
            $this->testErrorsManagement();
        }

        if ($this->option('all') || $this->option('show-stats')) {
            $this->showStats();
        }

        $this->newLine();
        $this->info('🎉 انتهى اختبار النظام بنجاح!');
    }

    /**
     * إنشاء بيانات تجريبية
     */
    private function createTestData()
    {
        $this->warn('📝 إنشاء بيانات تجريبية...');

        // التأكد من وجود بيانات أساسية
        $student = Student::first();
        $teacher = User::first();
        $circle = QuranCircle::first();

        if (!$student || !$teacher || !$circle) {
            $this->error('❌ لا توجد بيانات أساسية (طلاب، معلمين، حلقات) في قاعدة البيانات');
            return;
        }

        // إنشاء عدة جلسات تجريبية
        $testSessions = [
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'quran_circle_id' => $circle->id,
                'start_surah_number' => 1,
                'start_verse' => 1,
                'end_surah_number' => 1,
                'end_verse' => 7,
                'recitation_type' => 'حفظ',
                'duration_minutes' => 15,
                'grade' => 9.5,
                'evaluation' => 'ممتاز',
                'teacher_notes' => 'حفظ ممتاز لسورة الفاتحة'
            ],
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'quran_circle_id' => $circle->id,
                'start_surah_number' => 2,
                'start_verse' => 255,
                'end_surah_number' => 2,
                'end_verse' => 255,
                'recitation_type' => 'مراجعة صغرى',
                'duration_minutes' => 10,
                'grade' => 8.0,
                'evaluation' => 'جيد جداً',
                'teacher_notes' => 'مراجعة آية الكرسي'
            ],
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'quran_circle_id' => $circle->id,
                'start_surah_number' => 3,
                'start_verse' => 1,
                'end_surah_number' => 3,
                'end_verse' => 20,
                'recitation_type' => 'مراجعة كبرى',
                'duration_minutes' => 25,
                'grade' => 7.5,
                'evaluation' => 'جيد',
                'teacher_notes' => 'مراجعة بداية سورة آل عمران'
            ]
        ];

        foreach ($testSessions as $index => $sessionData) {
            try {
                $session = RecitationSession::create($sessionData);
                $this->line("✅ تم إنشاء الجلسة " . ($index + 1) . ": {$session->session_id}");
            } catch (\Exception $e) {
                $this->error("❌ فشل إنشاء الجلسة " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        $this->newLine();
    }

    /**
     * اختبار API
     */
    private function testApi()
    {
        $this->warn('🧪 اختبار API...');

        // 1. اختبار إنشاء جلسة جديدة
        $this->testCreateSession();

        // 2. اختبار جلب جميع الجلسات
        $this->testGetSessions();

        // 3. اختبار جلب جلسة محددة
        $this->testGetSpecificSession();

        // 4. اختبار تحديث جلسة
        $this->testUpdateSession();

        $this->newLine();
    }

    /**
     * اختبار إنشاء جلسة جديدة
     */
    private function testCreateSession()
    {
        $this->line('1️⃣ اختبار إنشاء جلسة جديدة...');

        $student = Student::first();
        $teacher = User::first();
        $circle = QuranCircle::first();

        if (!$student || !$teacher || !$circle) {
            $this->error('   ❌ لا توجد بيانات أساسية للاختبار');
            return;
        }

        $sessionData = [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'start_surah_number' => 112,
            'start_verse' => 1,
            'end_surah_number' => 114,
            'end_verse' => 6,
            'recitation_type' => 'حفظ',
            'duration_minutes' => 20,
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'teacher_notes' => 'حفظ المعوذات - جلسة اختبار API'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/recitation/sessions', $sessionData);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->line("   ✅ نجح إنشاء الجلسة: {$data['session_id']}");
                    $this->line("   📊 معرف الجلسة: {$data['data']['session_id']}");
                    $this->line("   📊 الطالب: {$data['data']['student']['name']}");
                    $this->line("   📊 الدرجة: {$data['data']['grade']}/10");
                    $this->line("   📊 التقدير: {$data['data']['evaluation']}");
                    
                    // حفظ معرف الجلسة للاختبارات التالية
                    $this->testSessionId = $data['session_id'];
                } else {
                    $this->error("   ❌ فشل الإنشاء: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار جلب جميع الجلسات
     */
    private function testGetSessions()
    {
        $this->line('2️⃣ اختبار جلب جميع الجلسات...');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/recitation/sessions');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $total = $data['data']['total'];
                    $current = count($data['data']['data']);
                    $this->line("   ✅ نجح جلب الجلسات");
                    $this->line("   📊 إجمالي الجلسات: {$total}");
                    $this->line("   📊 الجلسات في هذه الصفحة: {$current}");

                    if ($current > 0) {
                        $this->line("   📋 آخر الجلسات:");
                        foreach (array_slice($data['data']['data'], 0, 3) as $session) {
                            $this->line("      - {$session['session_id']}: {$session['student']['name']} ({$session['grade']}/10)");
                        }
                    }
                } else {
                    $this->error("   ❌ فشل الجلب: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار جلب جلسة محددة
     */
    private function testGetSpecificSession()
    {
        $this->line('3️⃣ اختبار جلب جلسة محددة...');

        // استخدام آخر جلسة تم إنشاؤها أو جلسة موجودة
        $sessionId = $this->testSessionId ?? RecitationSession::latest()->first()?->session_id;

        if (!$sessionId) {
            $this->error('   ❌ لا توجد جلسة للاختبار');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . "/recitation/sessions/{$sessionId}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $session = $data['data'];
                    $this->line("   ✅ نجح جلب الجلسة: {$sessionId}");
                    $this->line("   📊 الطالب: {$session['student']['name']}");
                    $this->line("   📊 المعلم: {$session['teacher']['name']}");
                    $this->line("   📊 النطاق: سورة {$session['start_surah_number']} آية {$session['start_verse']} - سورة {$session['end_surah_number']} آية {$session['end_verse']}");
                    $this->line("   📊 النوع: {$session['recitation_type']}");
                    $this->line("   📊 الدرجة: {$session['grade']}/10");
                    $this->line("   📊 التقدير: {$session['evaluation']}");
                    $this->line("   📊 أخطاء: " . ($session['has_errors'] ? 'نعم' : 'لا'));

                    if (!empty($session['teacher_notes'])) {
                        $this->line("   📝 ملاحظات: {$session['teacher_notes']}");
                    }
                } else {
                    $this->error("   ❌ فشل الجلب: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار تحديث جلسة
     */
    private function testUpdateSession()
    {
        $this->line('4️⃣ اختبار تحديث جلسة...');

        $sessionId = $this->testSessionId ?? RecitationSession::latest()->first()?->session_id;

        if (!$sessionId) {
            $this->error('   ❌ لا توجد جلسة للاختبار');
            return;
        }

        $updateData = [
            'grade' => 10.0,
            'teacher_notes' => 'تم تحديث الدرجة إلى العلامة الكاملة - اختبار API'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->put($this->baseUrl . "/recitation/sessions/{$sessionId}", $updateData);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->line("   ✅ نجح تحديث الجلسة: {$sessionId}");
                    $this->line("   📊 الدرجة الجديدة: {$data['data']['grade']}/10");
                    $this->line("   📊 التقدير الجديد: {$data['data']['evaluation']}");
                } else {
                    $this->error("   ❌ فشل التحديث: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }    }

    /**
     * اختبار إدارة الأخطاء
     */
    private function testErrorsManagement()
    {
        $this->warn('🐛 اختبار إدارة الأخطاء...');

        // 1. البحث عن جلسة موجودة أو إنشاء جلسة جديدة
        $sessionId = $this->getOrCreateTestSession();
        
        if (!$sessionId) {
            $this->error('❌ لا يمكن الحصول على جلسة للاختبار');
            return;
        }

        // 2. إضافة أخطاء متنوعة
        $this->testAddErrors($sessionId);

        // 3. جلب الأخطاء
        $this->testGetSessionErrors($sessionId);

        // 4. إحصائيات الأخطاء
        $this->testErrorsStats($sessionId);

        $this->newLine();
    }

    /**
     * الحصول على جلسة موجودة أو إنشاء جديدة
     */
    private function getOrCreateTestSession()
    {
        // محاولة استخدام جلسة موجودة
        $existingSession = RecitationSession::latest()->first();
        
        if ($existingSession) {
            $this->line("🔍 استخدام جلسة موجودة: {$existingSession->session_id}");
            return $existingSession->session_id;
        }

        // إنشاء جلسة جديدة إذا لم توجد
        $this->line("🆕 إنشاء جلسة جديدة للاختبار...");
        
        $student = Student::first();
        $teacher = User::first();
        $circle = QuranCircle::first();

        if (!$student || !$teacher || !$circle) {
            return null;
        }

        $sessionData = [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 7,
            'recitation_type' => 'حفظ',
            'duration_minutes' => 15,
            'grade' => 8.0,
            'evaluation' => 'جيد جداً',
            'teacher_notes' => 'جلسة اختبار الأخطاء - سورة الفاتحة'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/recitation/sessions', $sessionData);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->line("✅ تم إنشاء جلسة جديدة: {$data['session_id']}");
                    return $data['session_id'];
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ فشل إنشاء الجلسة: " . $e->getMessage());
        }

        return null;
    }

    /**
     * اختبار إضافة أخطاء متنوعة
     */
    private function testAddErrors($sessionId)
    {
        $this->line('1️⃣ اختبار إضافة أخطاء متنوعة...');

        // أخطاء مختلفة للاختبار
        $errors = [
            [
                'session_id' => $sessionId,
                'error_type' => 'نطق',
                'error_description' => 'خطأ في نطق كلمة "الرحمن"',
                'surah_number' => 1,
                'verse_number' => 3,
                'word_position' => 2,
                'severity' => 'متوسط',
                'correction_provided' => true,
                'notes' => 'تم تصحيح النطق مباشرة'
            ],
            [
                'session_id' => $sessionId,
                'error_type' => 'تجويد',
                'error_description' => 'عدم إظهار الغنة في "مَن"',
                'surah_number' => 1,
                'verse_number' => 4,
                'word_position' => 1,
                'severity' => 'خفيف',
                'correction_provided' => true,
                'notes' => 'شرح قاعدة الغنة'
            ],
            [
                'session_id' => $sessionId,
                'error_type' => 'ترتيل',
                'error_description' => 'سرعة في القراءة',
                'surah_number' => 1,
                'verse_number' => 6,
                'word_position' => null,
                'severity' => 'خفيف',
                'correction_provided' => true,
                'notes' => 'التذكير بأهمية التمهل'
            ]
        ];

        foreach ($errors as $index => $errorData) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])->post($this->baseUrl . '/recitation/errors', $errorData);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['success']) {
                        $this->line("   ✅ تم إضافة خطأ " . ($index + 1) . ": {$errorData['error_type']} - {$errorData['error_description']}");
                    } else {
                        $this->error("   ❌ فشل إضافة خطأ " . ($index + 1) . ": {$data['message']}");
                    }
                } else {
                    $this->error("   ❌ فشل الطلب لخطأ " . ($index + 1) . ": " . $response->status());
                }
            } catch (\Exception $e) {
                $this->error("   ❌ خطأ في الاتصال لخطأ " . ($index + 1) . ": " . $e->getMessage());
            }

            // توقف قصير بين الطلبات
            usleep(200000); // 0.2 ثانية
        }
    }

    /**
     * اختبار جلب أخطاء الجلسة
     */
    private function testGetSessionErrors($sessionId)
    {
        $this->line('2️⃣ اختبار جلب أخطاء الجلسة...');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . "/recitation/sessions/{$sessionId}/errors");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $errors = $data['data'];
                    $this->line("   ✅ تم جلب أخطاء الجلسة: {$sessionId}");
                    $this->line("   📊 عدد الأخطاء: " . count($errors));
                    
                    if (count($errors) > 0) {
                        $this->line("   📋 تفاصيل الأخطاء:");
                        
                        $headers = ['النوع', 'الوصف', 'السورة:الآية', 'الشدة', 'التصحيح'];
                        $rows = [];
                        
                        foreach ($errors as $error) {
                            $rows[] = [
                                $error['error_type'],
                                mb_substr($error['error_description'], 0, 30) . (mb_strlen($error['error_description']) > 30 ? '...' : ''),
                                $error['surah_number'] . ':' . $error['verse_number'],
                                $error['severity'],
                                $error['correction_provided'] ? 'نعم' : 'لا'
                            ];
                        }
                        
                        $this->table($headers, $rows);
                    }
                } else {
                    $this->error("   ❌ فشل جلب الأخطاء: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار إحصائيات أخطاء الجلسة
     */
    private function testErrorsStats($sessionId)
    {
        $this->line('3️⃣ اختبار إحصائيات أخطاء الجلسة...');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . "/recitation/sessions/{$sessionId}/errors/stats");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $stats = $data['data'];
                    $this->line("   ✅ تم جلب إحصائيات الأخطاء للجلسة: {$sessionId}");
                    $this->line("   📊 إجمالي الأخطاء: {$stats['total_errors']}");
                    
                    if (!empty($stats['errors_by_type'])) {
                        $this->line("   📊 الأخطاء حسب النوع:");
                        foreach ($stats['errors_by_type'] as $type => $count) {
                            $this->line("      - {$type}: {$count}");
                        }
                    }
                    
                    if (!empty($stats['errors_by_severity'])) {
                        $this->line("   📊 الأخطاء حسب الشدة:");
                        foreach ($stats['errors_by_severity'] as $severity => $count) {
                            $this->line("      - {$severity}: {$count}");
                        }
                    }
                      $this->line("   📊 نسبة التصحيح: {$stats['correction_rate']}%");
                } else {
                    $this->error("   ❌ فشل جلب الإحصائيات: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * عرض الإحصائيات
     */
    private function showStats()
    {
        $this->warn('📊 عرض الإحصائيات...');

        // 1. الإحصائيات العامة
        $this->testGeneralStats();

        // 2. إحصائيات طالب
        $this->testStudentStats();

        // 3. إحصائيات معلم
        $this->testTeacherStats();

        $this->newLine();
    }

    /**
     * اختبار الإحصائيات العامة
     */
    private function testGeneralStats()
    {
        $this->line('📈 الإحصائيات العامة:');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/recitation/sessions/stats/summary');

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $stats = $data['data'];
                    $this->line("   📊 إجمالي الجلسات: {$stats['total_sessions']}");
                    $this->line("   📊 جلسات بها أخطاء: {$stats['sessions_with_errors']}");
                    $this->line("   📊 جلسات بدون أخطاء: {$stats['sessions_without_errors']}");
                    $this->line("   📊 معدل الأخطاء: {$stats['error_rate_percentage']}%");
                    $this->line("   📊 متوسط الدرجات: {$stats['average_grade']}/10");
                    $this->line("   📊 جلسات اليوم: {$stats['today_sessions']}");
                } else {
                    $this->error("   ❌ فشل جلب الإحصائيات: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار إحصائيات طالب
     */
    private function testStudentStats()
    {
        $this->line('👨‍🎓 إحصائيات طالب:');

        $student = Student::first();
        if (!$student) {
            $this->error('   ❌ لا يوجد طلاب للاختبار');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . "/recitation/sessions/stats/student/{$student->id}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $stats = $data['data'];
                    $this->line("   👤 الطالب: {$stats['student']['name']}");
                    $this->line("   📊 إجمالي الجلسات: {$stats['total_sessions']}");
                    $this->line("   📊 جلسات بها أخطاء: {$stats['sessions_with_errors']}");
                    $this->line("   📊 معدل الأخطاء: {$stats['error_rate_percentage']}%");
                    $this->line("   📊 متوسط الدرجات: {$stats['average_grade']}/10");
                    if ($stats['last_session_date']) {
                        $this->line("   📊 آخر جلسة: {$stats['last_session_date']}");
                    }
                } else {
                    $this->error("   ❌ فشل جلب الإحصائيات: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    /**
     * اختبار إحصائيات معلم
     */
    private function testTeacherStats()
    {
        $this->line('👨‍🏫 إحصائيات معلم:');

        $teacher = User::first();
        if (!$teacher) {
            $this->error('   ❌ لا يوجد معلمين للاختبار');
            return;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->get($this->baseUrl . "/recitation/sessions/stats/teacher/{$teacher->id}");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $stats = $data['data'];
                    $this->line("   👤 المعلم: {$stats['teacher']['name']}");
                    $this->line("   📊 إجمالي الجلسات: {$stats['total_sessions']}");
                    $this->line("   📊 جلسات بها أخطاء: {$stats['sessions_with_errors']}");
                    $this->line("   📊 معدل الأخطاء: {$stats['error_rate_percentage']}%");
                    $this->line("   📊 متوسط الدرجات: {$stats['average_grade']}/10");
                    $this->line("   📊 عدد الطلاب المُدرسين: {$stats['students_taught']}");
                } else {
                    $this->error("   ❌ فشل جلب الإحصائيات: {$data['message']}");
                }
            } else {
                $this->error("   ❌ فشل الطلب: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في الاتصال: " . $e->getMessage());
        }
    }

    private $testSessionId;
}
