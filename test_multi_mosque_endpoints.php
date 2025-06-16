<?php
/**
 * اختبار شامل لنقاط النهاية الجديدة لنظام المعلمين متعددي المساجد
 * Test comprehensive for new multi-mosque teacher system endpoints
 */

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MultiMosqueApiTester 
{
    private $client;
    private $baseUrl;
    private $results = [];
    
    public function __construct()
    {
        $this->baseUrl = 'http://127.0.0.1:8000/api/teachers';
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }
    
    /**
     * اختبار قائمة المعلمين
     */
    public function testTeachersList()
    {
        echo "🔍 اختبار قائمة المعلمين...\n";
        
        try {
            $response = $this->client->get($this->baseUrl);
            $data = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 200 && $data['نجح']) {
                $teachersCount = count($data['البيانات']['المعلمون']);
                echo "✅ نجح: تم العثور على {$teachersCount} معلم\n";
                
                // حفظ أول معلم للاختبارات اللاحقة
                if ($teachersCount > 0) {
                    $this->results['first_teacher_id'] = $data['البيانات']['المعلمون'][0]['id'];
                    echo "📋 سيتم اختبار المعلم ID: {$this->results['first_teacher_id']}\n";
                }
                
                return true;
            }
        } catch (RequestException $e) {
            echo "❌ فشل: " . $e->getMessage() . "\n";
        }
        
        return false;
    }
    
    /**
     * اختبار نقطة نهاية مساجد المعلم الجديدة
     */
    public function testTeacherMosques($teacherId)
    {
        echo "\n🏛️ اختبار مساجد المعلم (ID: {$teacherId})...\n";
        
        try {
            $response = $this->client->get($this->baseUrl . "/{$teacherId}/mosques");
            $data = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 200 && $data['نجح']) {
                echo "✅ نجح: تم جلب مساجد المعلم\n";
                
                $mosques = $data['البيانات']['المساجد'] ?? [];
                $stats = $data['البيانات']['الإحصائيات'] ?? [];
                
                echo "📊 الإحصائيات:\n";
                echo "   - عدد المساجد: " . ($stats['عدد_المساجد'] ?? 0) . "\n";
                echo "   - عدد الحلقات: " . ($stats['عدد_الحلقات'] ?? 0) . "\n";
                echo "   - إجمالي الطلاب: " . ($stats['إجمالي_الطلاب'] ?? 0) . "\n";
                
                foreach ($mosques as $index => $mosque) {
                    echo "🏛️ المسجد " . ($index + 1) . ":\n";
                    echo "   - الاسم: " . ($mosque['اسم_المسجد'] ?? 'غير محدد') . "\n";
                    echo "   - النوع: " . ($mosque['النوع'] ?? 'غير محدد') . "\n";
                    echo "   - عدد الحلقات: " . count($mosque['الحلقات'] ?? []) . "\n";
                    echo "   - عدد الجداول: " . count($mosque['الجداول'] ?? []) . "\n";
                }
                
                $this->results['mosques_test'] = $data;
                return true;
            }
        } catch (RequestException $e) {
            echo "❌ فشل: " . $e->getMessage() . "\n";
        }
        
        return false;
    }
    
    /**
     * اختبار نقطة نهاية الحلقات المفصلة الجديدة
     */
    public function testTeacherCirclesDetailed($teacherId)
    {
        echo "\n📚 اختبار الحلقات المفصلة للمعلم (ID: {$teacherId})...\n";
        
        try {
            $response = $this->client->get($this->baseUrl . "/{$teacherId}/circles-detailed");
            $data = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 200 && $data['نجح']) {
                echo "✅ نجح: تم جلب الحلقات المفصلة\n";
                
                $circles = $data['البيانات']['الحلقات'] ?? [];
                $generalStats = $data['البيانات']['الإحصائيات_العامة'] ?? [];
                
                echo "📊 الإحصائيات العامة:\n";
                echo "   - عدد الحلقات: " . ($generalStats['عدد_الحلقات'] ?? 0) . "\n";
                echo "   - إجمالي الطلاب: " . ($generalStats['إجمالي_الطلاب'] ?? 0) . "\n";
                
                foreach ($circles as $index => $circle) {
                    echo "📚 الحلقة " . ($index + 1) . ":\n";
                    echo "   - الاسم: " . ($circle['اسم_الحلقة'] ?? 'غير محدد') . "\n";
                    echo "   - المستوى: " . ($circle['المستوى'] ?? 'غير محدد') . "\n";
                    echo "   - عدد الطلاب: " . ($circle['إحصائيات']['عدد_الطلاب'] ?? 0) . "\n";
                    echo "   - الطلاب النشطون: " . ($circle['إحصائيات']['الطلاب_النشطون'] ?? 0) . "\n";
                    
                    // عرض بعض الطلاب كأمثلة
                    $students = $circle['الطلاب'] ?? [];
                    if (count($students) > 0) {
                        echo "   - أمثلة على الطلاب:\n";
                        foreach (array_slice($students, 0, 3) as $student) {
                            echo "     • " . ($student['الاسم'] ?? 'غير محدد') . 
                                 " (نسبة الحضور: " . ($student['الحضور_الشهري']['نسبة_الحضور'] ?? '0%') . ")\n";
                        }
                    }
                }
                
                $this->results['circles_detailed_test'] = $data;
                return true;
            }
        } catch (RequestException $e) {
            echo "❌ فشل: " . $e->getMessage() . "\n";
        }
        
        return false;
    }
    
    /**
     * اختبار جميع نقاط النهاية الأخرى للتأكد من عدم وجود تعارضات
     */
    public function testOtherEndpoints($teacherId)
    {
        echo "\n🔧 اختبار نقاط النهاية الأخرى...\n";
        
        $endpoints = [
            '' => 'تفاصيل المعلم',
            '/circles' => 'حلقات المعلم',
            '/students' => 'طلاب المعلم',
            '/stats' => 'إحصائيات المعلم',
            '/attendance' => 'حضور المعلم',
            '/financials' => 'الماليات'
        ];
        
        $successCount = 0;
        
        foreach ($endpoints as $endpoint => $description) {
            try {
                $response = $this->client->get($this->baseUrl . "/{$teacherId}" . $endpoint);
                $data = json_decode($response->getBody(), true);
                
                if ($response->getStatusCode() === 200 && isset($data['نجح']) && $data['نجح']) {
                    echo "✅ {$description}: نجح\n";
                    $successCount++;
                } else {
                    echo "⚠️ {$description}: استجابة غير متوقعة\n";
                }
            } catch (RequestException $e) {
                echo "❌ {$description}: فشل - " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n📊 نتيجة الاختبار: {$successCount}/" . count($endpoints) . " نقاط نهاية تعمل بنجاح\n";
        
        return $successCount === count($endpoints);
    }
    
    /**
     * اختبار حالات الخطأ
     */
    public function testErrorCases()
    {
        echo "\n🚨 اختبار حالات الخطأ...\n";
        
        // اختبار معلم غير موجود
        try {
            $response = $this->client->get($this->baseUrl . "/99999/mosques");
            $data = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 404 && !$data['نجح']) {
                echo "✅ اختبار معلم غير موجود (مساجد): نجح - تم إرجاع 404\n";
            } else {
                echo "⚠️ اختبار معلم غير موجود (مساجد): لم يتم إرجاع الخطأ المتوقع\n";
            }
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                echo "✅ اختبار معلم غير موجود (مساجد): نجح - تم إرجاع 404\n";
            } else {
                echo "❌ اختبار معلم غير موجود (مساجد): فشل - " . $e->getMessage() . "\n";
            }
        }
        
        // اختبار معلم غير موجود للحلقات المفصلة
        try {
            $response = $this->client->get($this->baseUrl . "/99999/circles-detailed");
            $data = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 404 && !$data['نجح']) {
                echo "✅ اختبار معلم غير موجود (حلقات مفصلة): نجح - تم إرجاع 404\n";
            } else {
                echo "⚠️ اختبار معلم غير موجود (حلقات مفصلة): لم يتم إرجاع الخطأ المتوقع\n";
            }
        } catch (RequestException $e) {
            if ($e->getCode() === 404) {
                echo "✅ اختبار معلم غير موجود (حلقات مفصلة): نجح - تم إرجاع 404\n";
            } else {
                echo "❌ اختبار معلم غير موجود (حلقات مفصلة): فشل - " . $e->getMessage() . "\n";
            }
        }
    }
    
    /**
     * تشغيل جميع الاختبارات
     */
    public function runAllTests()
    {
        echo "🚀 بدء اختبار نظام المعلمين متعددي المساجد\n";
        echo "=".str_repeat("=", 50)."\n\n";
        
        // اختبار الاتصال بالخادم أولاً
        echo "🌐 اختبار الاتصال بالخادم...\n";
        try {
            $response = $this->client->get('http://127.0.0.1:8000');
            echo "✅ الخادم يعمل بنجاح\n\n";
        } catch (Exception $e) {
            echo "❌ فشل في الاتصال بالخادم. تأكد من تشغيل: php artisan serve\n";
            return;
        }
        
        // تشغيل الاختبارات
        $testResults = [];
        
        // 1. اختبار قائمة المعلمين
        $testResults['teachers_list'] = $this->testTeachersList();
        
        if (!isset($this->results['first_teacher_id'])) {
            echo "\n❌ لا يمكن المتابعة - لا توجد معلمين في النظام\n";
            return;
        }
        
        $teacherId = $this->results['first_teacher_id'];
        
        // 2. اختبار نقاط النهاية الجديدة
        $testResults['teacher_mosques'] = $this->testTeacherMosques($teacherId);
        $testResults['teacher_circles_detailed'] = $this->testTeacherCirclesDetailed($teacherId);
        
        // 3. اختبار نقاط النهاية الأخرى
        $testResults['other_endpoints'] = $this->testOtherEndpoints($teacherId);
        
        // 4. اختبار حالات الخطأ
        $this->testErrorCases();
        
        // تقرير النهائي
        echo "\n" . "=".str_repeat("=", 50) . "\n";
        echo "📋 تقرير الاختبار النهائي:\n";
        echo "=".str_repeat("=", 50)."\n";
        
        $successCount = 0;
        $totalTests = count($testResults);
        
        foreach ($testResults as $test => $result) {
            $status = $result ? "✅ نجح" : "❌ فشل";
            echo "{$status} - {$test}\n";
            if ($result) $successCount++;
        }
        
        echo "\n📊 النتيجة الإجمالية: {$successCount}/{$totalTests} اختبارات نجحت\n";
        
        if ($successCount === $totalTests) {
            echo "🎉 تهانينا! جميع اختبارات نظام المعلمين متعددي المساجد نجحت!\n";
        } else {
            echo "⚠️ بعض الاختبارات فشلت. راجع الرسائل أعلاه للحصول على التفاصيل.\n";
        }
        
        echo "\n📁 تم حفظ نتائج الاختبار في الخاصية results\n";
    }
}

// تشغيل الاختبارات
$tester = new MultiMosqueApiTester();
$tester->runAllTests();

echo "\n✨ انتهى الاختبار!\n";
