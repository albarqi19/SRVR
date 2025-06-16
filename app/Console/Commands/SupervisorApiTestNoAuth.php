<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SupervisorApiTestNoAuth extends Command
{
    protected $signature = 'test:supervisor-apis-no-auth';
    protected $description = 'اختبار APIs المشرف بدون مصادقة للتشخيص السريع';

    private $baseUrl = 'http://127.0.0.1:8000/api';
    private $teacherId;
    private $circleId;
    private $studentId;
    private $evaluationId;
    private $transferRequestId;
    
    // متغيرات لتتبع نتائج الاختبارات
    private $testResults = [
        'total_tests' => 0,
        'passed_tests' => 0,
        'failed_tests' => 0,
        'warnings' => 0,
        'test_details' => []
    ];

    public function handle()
    {
        $this->info('🚀 بدء اختبار APIs المشرف بدون مصادقة');
        $this->info('=====================================');
        
        try {
            $this->testCircleApis();
            $this->testTeacherApis();
            $this->testStudentApis();
            $this->testStatisticsApis();
            
            $this->displayFinalReport();
            
        } catch (\Exception $e) {
            $this->error('❌ فشل في الاختبار: ' . $e->getMessage());
            $this->displayFinalReport();
        }
    }

    /**
     * اختبار واجهات الحلقات
     */
    private function testCircleApis()
    {
        $this->info('🔵 اختبار Circle APIs...');
        
        $response = Http::get($this->baseUrl . '/supervisors/circles');
        
        $this->info("📡 Status Code: " . $response->status());
        $this->info("📄 Response: " . substr($response->body(), 0, 200) . "...");
        
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['success']) && $data['success'] === true) {
                if (!empty($data['data'])) {
                    $this->circleId = $data['data'][0]['id'];
                    $this->info("✅ تم الحصول على " . count($data['data']) . " حلقة بنجاح");
                    $this->info("   📝 معرف الحلقة الأولى: {$this->circleId}");
                    $this->recordTestResult('الحصول على الحلقات', 'passed', 'تم العثور على ' . count($data['data']) . ' حلقة');
                } else {
                    $this->warn('⚠️ لا توجد حلقات مسندة للمشرف');
                    $this->recordTestResult('الحصول على الحلقات', 'warning', 'لا توجد حلقات مسندة');
                }
            } else {
                $this->warn('⚠️ استجابة API غير متوقعة: ' . json_encode($data));
                $this->recordTestResult('الحصول على الحلقات', 'warning', 'استجابة غير متوقعة');
            }
        } else {
            $this->error('❌ فشل في الحصول على الحلقات: ' . $response->body());
            $this->recordTestResult('الحصول على الحلقات', 'failed', 'HTTP ' . $response->status());
        }
    }

    /**
     * اختبار واجهات المعلمين
     */
    private function testTeacherApis()
    {
        $this->info('👨‍🏫 اختبار Teacher APIs...');
        
        if (empty($this->circleId)) {
            $this->warn('⚠️ لا يوجد معرف حلقة لاختبار واجهات المعلمين');
            $this->circleId = 1; // استخدام معرف افتراضي للاختبار
        }
        
        // الحصول على معلمي حلقة محددة
        $response = Http::get($this->baseUrl . '/supervisors/circles/' . $this->circleId . '/teachers');
        
        $this->info("📡 Status Code: " . $response->status());
        $this->info("📄 Response: " . substr($response->body(), 0, 200) . "...");
        
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['success']) && $data['success'] === true) {
                if (!empty($data['data'])) {
                    $this->teacherId = $data['data'][0]['id'];
                    $this->info("✅ تم الحصول على " . count($data['data']) . " معلم بنجاح");
                    $this->info("   📝 معرف المعلم الأول: {$this->teacherId}");
                    $this->recordTestResult('الحصول على المعلمين', 'passed', 'تم العثور على ' . count($data['data']) . ' معلم');
                } else {
                    $this->warn('⚠️ لا يوجد معلمون في الحلقة');
                    $this->recordTestResult('الحصول على المعلمين', 'warning', 'لا يوجد معلمون');
                }
            } else {
                $this->warn('⚠️ استجابة API غير متوقعة: ' . json_encode($data));
                $this->recordTestResult('الحصول على المعلمين', 'warning', 'استجابة غير متوقعة');
            }
        } else {
            $this->error('❌ فشل في الحصول على المعلمين: ' . $response->body());
            $this->recordTestResult('الحصول على المعلمين', 'failed', 'HTTP ' . $response->status());
        }

        // اختبار إنشاء تقرير للمعلم
        if (!empty($this->teacherId)) {
            $this->info('📋 اختبار إنشاء تقرير للمعلم...');
            
            $response = Http::post($this->baseUrl . '/supervisors/teacher-report', [
                'teacher_id' => $this->teacherId,
                'evaluation_score' => 8,
                'performance_notes' => 'أداء ممتاز في التدريس من اختبار API',
                'attendance_notes' => 'منتظم في الحضور',
                'recommendations' => 'يُنصح بإعطائه مزيد من الحلقات'
            ]);
            
            $this->info("📡 Status Code: " . $response->status());
            $this->info("📄 Response: " . substr($response->body(), 0, 200) . "...");
            
            if ($response->successful()) {
                $this->info("✅ تم إنشاء تقرير للمعلم بنجاح");
                $this->recordTestResult('إنشاء تقرير المعلم', 'passed', 'تم إنشاء التقرير بنجاح');
            } else {
                $this->warn('⚠️ فشل في إنشاء تقرير للمعلم: ' . $response->body());
                $this->recordTestResult('إنشاء تقرير المعلم', 'failed', 'HTTP ' . $response->status());
            }
        }
    }

    /**
     * اختبار واجهات الطلاب
     */
    private function testStudentApis()
    {
        $this->info('👥 اختبار Student APIs...');
        
        if (empty($this->circleId)) {
            $this->warn('⚠️ لا يوجد معرف حلقة لاختبار واجهات الطلاب');
            $this->circleId = 1; // استخدام معرف افتراضي للاختبار
        }
        
        // الحصول على طلاب حلقة محددة
        $response = Http::get($this->baseUrl . '/supervisors/circles/' . $this->circleId . '/students');
        
        $this->info("📡 Status Code: " . $response->status());
        $this->info("📄 Response: " . substr($response->body(), 0, 200) . "...");
        
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['success']) && $data['success'] === true) {
                if (!empty($data['data'])) {
                    $this->studentId = $data['data'][0]['id'];
                    $this->info("✅ تم الحصول على " . count($data['data']) . " طالب بنجاح");
                    $this->info("   📝 معرف الطالب الأول: {$this->studentId}");
                    $this->recordTestResult('الحصول على الطلاب', 'passed', 'تم العثور على ' . count($data['data']) . ' طالب');
                } else {
                    $this->warn('⚠️ لا يوجد طلاب في الحلقة');
                    $this->recordTestResult('الحصول على الطلاب', 'warning', 'لا يوجد طلاب');
                }
            } else {
                $this->warn('⚠️ استجابة API غير متوقعة: ' . json_encode($data));
                $this->recordTestResult('الحصول على الطلاب', 'warning', 'استجابة غير متوقعة');
            }
        } else {
            $this->error('❌ فشل في الحصول على الطلاب: ' . $response->body());
            $this->recordTestResult('الحصول على الطلاب', 'failed', 'HTTP ' . $response->status());
        }
    }

    /**
     * اختبار واجهات الإحصائيات
     */
    private function testStatisticsApis()
    {
        $this->info('📊 اختبار Statistics APIs...');
        
        $response = Http::get($this->baseUrl . '/supervisors/dashboard-stats');
        
        $this->info("📡 Status Code: " . $response->status());
        $this->info("📄 Response: " . substr($response->body(), 0, 200) . "...");
        
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['success']) && $data['success'] === true) {
                $this->info("✅ تم الحصول على إحصائيات لوحة المعلومات بنجاح");
                
                if (isset($data['data']['circles_count'])) {
                    $this->info("   🔵 عدد الحلقات: " . $data['data']['circles_count']);
                }
                
                if (isset($data['data']['students_count'])) {
                    $this->info("   👥 عدد الطلاب: " . $data['data']['students_count']);
                }
                
                $this->recordTestResult('إحصائيات المشرف', 'passed', 'تم الحصول على الإحصائيات بنجاح');
            } else {
                $this->warn('⚠️ استجابة الإحصائيات غير متوقعة: ' . json_encode($data));
                $this->recordTestResult('إحصائيات المشرف', 'warning', 'استجابة غير متوقعة');
            }
        } else {
            $this->error('❌ فشل في الحصول على إحصائيات لوحة المعلومات: ' . $response->body());
            $this->recordTestResult('إحصائيات المشرف', 'failed', 'HTTP ' . $response->status());
        }
    }
    
    /**
     * تسجيل نتيجة اختبار
     */
    private function recordTestResult($testName, $status, $message = '')
    {
        $this->testResults['total_tests']++;
        
        switch ($status) {
            case 'passed':
                $this->testResults['passed_tests']++;
                break;
            case 'failed':
                $this->testResults['failed_tests']++;
                break;
            case 'warning':
                $this->testResults['warnings']++;
                break;
        }
        
        $this->testResults['test_details'][] = [
            'name' => $testName,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->format('H:i:s')
        ];
    }
    
    /**
     * عرض التقرير النهائي للاختبارات
     */
    private function displayFinalReport()
    {
        $this->info('');
        $this->info('📊 =============== التقرير النهائي ===============');
        $this->info('');
        
        // الإحصائيات العامة
        $this->info("📈 إجمالي الاختبارات: {$this->testResults['total_tests']}");
        $this->info("✅ الاختبارات الناجحة: {$this->testResults['passed_tests']}");
        $this->info("❌ الاختبارات الفاشلة: {$this->testResults['failed_tests']}");
        $this->info("⚠️ التحذيرات: {$this->testResults['warnings']}");
        
        // حساب نسبة النجاح
        $successRate = $this->testResults['total_tests'] > 0 
            ? round(($this->testResults['passed_tests'] / $this->testResults['total_tests']) * 100, 2)
            : 0;
            
        $this->info("📊 نسبة النجاح: {$successRate}%");
        
        // عرض تفاصيل الاختبارات
        if (!empty($this->testResults['test_details'])) {
            $this->info('');
            $this->info('📋 تفاصيل الاختبارات:');
            $this->info('----------------------------------------');
            
            foreach ($this->testResults['test_details'] as $test) {
                $icon = match($test['status']) {
                    'passed' => '✅',
                    'failed' => '❌',
                    'warning' => '⚠️',
                    default => '🔹'
                };
                
                $this->line("{$icon} [{$test['timestamp']}] {$test['name']} - {$test['message']}");
            }
        }
        
        $this->info('');
        $this->info('🏁 انتهى التقرير');
        $this->info('===============================================');
        
        // تحديد لون التقرير النهائي حسب النتائج
        if ($this->testResults['failed_tests'] > 0) {
            $this->error('⚠️ تحتاج بعض APIs إلى مراجعة');
        } elseif ($this->testResults['warnings'] > 0) {
            $this->warn('⚠️ تم اكتشاف بعض التحذيرات');
        } else {
            $this->info('🎉 جميع APIs تعمل بشكل مثالي!');
        }
    }
}
