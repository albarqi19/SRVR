<?php

/**
 * اختبار شامل لـ API مساجد المعلم
 * GET /api/teachers/{id}/mosques
 * 
 * هذا الملف يختبر جميع جوانب API عرض مساجد المعلم
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\QuranCircle;
use App\Models\Student;
use App\Models\TeacherMosqueSchedule;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class TeacherMosquesAPITest
{
    private $baseUrl;
    private $testResults = [];
    
    public function __construct()
    {
        $this->baseUrl = 'http://localhost/api'; // تعديل الرابط حسب إعدادك
        $this->testResults = [
            'passed' => 0,
            'failed' => 0,
            'total' => 0,
            'details' => []
        ];
    }
    
    /**
     * تشغيل جميع الاختبارات
     */
    public function runAllTests()
    {
        echo "🚀 بدء اختبار API مساجد المعلم\n";
        echo "========================================\n\n";
        
        // التحقق من قاعدة البيانات أولاً
        $this->checkDatabaseConnection();
        
        // إعداد بيانات الاختبار
        $teacherId = $this->setupTestData();
        
        if ($teacherId) {
            // اختبار الحالات المختلفة
            $this->testValidTeacherId($teacherId);
            $this->testInvalidTeacherId();
            $this->testNonExistentTeacherId();
            $this->testAPIResponse($teacherId);
            $this->testResponseStructure($teacherId);
            $this->testDataAccuracy($teacherId);
            
            // تنظيف البيانات
            $this->cleanupTestData($teacherId);
        }
        
        // عرض النتائج النهائية
        $this->displayResults();
    }
    
    /**
     * التحقق من الاتصال بقاعدة البيانات
     */
    private function checkDatabaseConnection()
    {
        $this->startTest("التحقق من الاتصال بقاعدة البيانات");
        
        try {
            DB::connection()->getPdo();
            $this->passTest("✅ تم الاتصال بقاعدة البيانات بنجاح");
        } catch (Exception $e) {
            $this->failTest("❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * إعداد بيانات الاختبار
     */
    private function setupTestData()
    {
        $this->startTest("إعداد بيانات الاختبار");
        
        try {
            // إنشاء مسجد للاختبار
            $mosque1 = Mosque::create([
                'name' => 'مسجد الاختبار الأول',
                'neighborhood' => 'حي الاختبار',
                'location_lat' => 24.7136,
                'location_long' => 46.6753,
                'contact_number' => '0112345678'
            ]);
            
            $mosque2 = Mosque::create([
                'name' => 'مسجد الاختبار الثاني',
                'neighborhood' => 'حي الاختبار المتقدم',
                'location_lat' => 24.8136,
                'location_long' => 46.7753,
                'contact_number' => '0112345679'
            ]);
            
            // إنشاء معلم للاختبار
            $teacher = Teacher::create([
                'identity_number' => '1234567890',
                'name' => 'معلم الاختبار الأول',
                'nationality' => 'سعودي',
                'mosque_id' => $mosque1->id,
                'phone' => '0551234567',
                'password' => bcrypt('password123'),
                'is_active_user' => true,
                'job_title' => 'معلم حفظ',
                'task_type' => 'معلم بمكافأة',
                'circle_type' => 'مدرسة قرآنية',
                'work_time' => 'عصر'
            ]);
            
            // إنشاء حلقة قرآنية
            $circle = QuranCircle::create([
                'name' => 'حلقة الاختبار الأولى',
                'mosque_id' => $mosque1->id,
                'grade_level' => 'المستوى الأول',
                'circle_type' => 'حلقة جماعية',
                'is_active' => true
            ]);
            
            // ربط المعلم بالحلقة
            $teacher->update(['quran_circle_id' => $circle->id]);
            
            // إنشاء طلاب للاختبار
            Student::create([
                'name' => 'طالب الاختبار الأول',
                'student_number' => 'ST001',
                'phone' => '0551234568',
                'quran_circle_id' => $circle->id,
                'mosque_id' => $mosque1->id,
                'is_active' => true
            ]);
            
            Student::create([
                'name' => 'طالب الاختبار الثاني',
                'student_number' => 'ST002',
                'phone' => '0551234569',
                'quran_circle_id' => $circle->id,
                'mosque_id' => $mosque1->id,
                'is_active' => true
            ]);
            
            // إنشاء جدول عمل إضافي في مسجد آخر
            TeacherMosqueSchedule::create([
                'teacher_id' => $teacher->id,
                'mosque_id' => $mosque2->id,
                'day_of_week' => 'السبت',
                'start_time' => '16:00',
                'end_time' => '18:00',
                'session_type' => 'تدريس',
                'is_active' => true,
                'notes' => 'جلسة مراجعة أسبوعية'
            ]);
            
            $this->passTest("✅ تم إعداد بيانات الاختبار بنجاح (معلم ID: {$teacher->id})");
            return $teacher->id;
            
        } catch (Exception $e) {
            $this->failTest("❌ فشل في إعداد بيانات الاختبار: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * اختبار معرف معلم صحيح
     */
    private function testValidTeacherId($teacherId)
    {
        $this->startTest("اختبار معرف معلم صحيح");
        
        try {
            $response = $this->makeAPIRequest("/teachers/{$teacherId}/mosques");
            
            if ($response && isset($response['نجح']) && $response['نجح'] === true) {
                $this->passTest("✅ API يستجيب بنجاح للمعرف الصحيح");
            } else {
                $this->failTest("❌ API لا يستجيب بنجاح للمعرف الصحيح");
            }
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في اختبار المعرف الصحيح: " . $e->getMessage());
        }
    }
    
    /**
     * اختبار معرف معلم غير صحيح
     */
    private function testInvalidTeacherId()
    {
        $this->startTest("اختبار معرف معلم غير صحيح");
        
        try {
            $response = $this->makeAPIRequest("/teachers/abc/mosques");
            
            // يجب أن يعيد خطأ 404 أو خطأ validation
            if (!$response || (isset($response['نجح']) && $response['نجح'] === false)) {
                $this->passTest("✅ API يتعامل مع المعرف غير الصحيح بشكل مناسب");
            } else {
                $this->failTest("❌ API لا يتعامل مع المعرف غير الصحيح بشكل مناسب");
            }
        } catch (Exception $e) {
            $this->passTest("✅ API يرفض المعرف غير الصحيح (متوقع)");
        }
    }
    
    /**
     * اختبار معرف معلم غير موجود
     */
    private function testNonExistentTeacherId()
    {
        $this->startTest("اختبار معرف معلم غير موجود");
        
        try {
            $response = $this->makeAPIRequest("/teachers/99999/mosques");
            
            if ($response && isset($response['نجح']) && $response['نجح'] === false) {
                if (isset($response['error']) && $response['error'] === 'teacher_not_found') {
                    $this->passTest("✅ API يعيد خطأ صحيح للمعلم غير الموجود");
                } else {
                    $this->passTest("✅ API يعيد استجابة خطأ مناسبة للمعلم غير الموجود");
                }
            } else {
                $this->failTest("❌ API لا يتعامل مع المعلم غير الموجود بشكل صحيح");
            }
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في اختبار المعلم غير الموجود: " . $e->getMessage());
        }
    }
    
    /**
     * اختبار استجابة API
     */
    private function testAPIResponse($teacherId)
    {
        $this->startTest("اختبار استجابة API");
        
        try {
            $response = $this->makeAPIRequest("/teachers/{$teacherId}/mosques");
            
            if ($response) {
                $this->passTest("✅ API يعيد استجابة صحيحة");
                
                // التحقق من وجود المفاتيح الأساسية
                $requiredKeys = ['نجح', 'رسالة', 'البيانات'];
                $missingKeys = [];
                
                foreach ($requiredKeys as $key) {
                    if (!isset($response[$key])) {
                        $missingKeys[] = $key;
                    }
                }
                
                if (empty($missingKeys)) {
                    $this->passTest("✅ الاستجابة تحتوي على جميع المفاتيح المطلوبة");
                } else {
                    $this->failTest("❌ الاستجابة تفتقر للمفاتيح: " . implode(', ', $missingKeys));
                }
            } else {
                $this->failTest("❌ API لا يعيد استجابة");
            }
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في اختبار استجابة API: " . $e->getMessage());
        }
    }
    
    /**
     * اختبار هيكل الاستجابة
     */
    private function testResponseStructure($teacherId)
    {
        $this->startTest("اختبار هيكل الاستجابة");
        
        try {
            $response = $this->makeAPIRequest("/teachers/{$teacherId}/mosques");
            
            if ($response && isset($response['البيانات'])) {
                $data = $response['البيانات'];
                
                // التحقق من هيكل البيانات
                $requiredDataKeys = ['معلومات_المعلم', 'الإحصائيات', 'المساجد'];
                $missingDataKeys = [];
                
                foreach ($requiredDataKeys as $key) {
                    if (!isset($data[$key])) {
                        $missingDataKeys[] = $key;
                    }
                }
                
                if (empty($missingDataKeys)) {
                    $this->passTest("✅ هيكل البيانات صحيح");
                    
                    // التحقق من تفاصيل معلومات المعلم
                    if (isset($data['معلومات_المعلم']['id']) && isset($data['معلومات_المعلم']['الاسم'])) {
                        $this->passTest("✅ معلومات المعلم مكتملة");
                    } else {
                        $this->failTest("❌ معلومات المعلم ناقصة");
                    }
                    
                    // التحقق من الإحصائيات
                    if (isset($data['الإحصائيات']['عدد_المساجد']) && 
                        isset($data['الإحصائيات']['عدد_الحلقات']) && 
                        isset($data['الإحصائيات']['إجمالي_الطلاب'])) {
                        $this->passTest("✅ الإحصائيات مكتملة");
                    } else {
                        $this->failTest("❌ الإحصائيات ناقصة");
                    }
                    
                    // التحقق من هيكل المساجد
                    if (is_array($data['المساجد']) && !empty($data['المساجد'])) {
                        $mosque = $data['المساجد'][0];
                        $requiredMosqueKeys = ['id', 'اسم_المسجد', 'العنوان', 'النوع', 'الحلقات', 'الجداول'];
                        $missingMosqueKeys = [];
                        
                        foreach ($requiredMosqueKeys as $key) {
                            if (!isset($mosque[$key])) {
                                $missingMosqueKeys[] = $key;
                            }
                        }
                        
                        if (empty($missingMosqueKeys)) {
                            $this->passTest("✅ هيكل المساجد صحيح");
                        } else {
                            $this->failTest("❌ هيكل المساجد يفتقر للمفاتيح: " . implode(', ', $missingMosqueKeys));
                        }
                    } else {
                        $this->failTest("❌ قائمة المساجد فارغة أو غير صحيحة");
                    }
                } else {
                    $this->failTest("❌ هيكل البيانات يفتقر للمفاتيح: " . implode(', ', $missingDataKeys));
                }
            } else {
                $this->failTest("❌ البيانات غير موجودة في الاستجابة");
            }
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في اختبار هيكل الاستجابة: " . $e->getMessage());
        }
    }
    
    /**
     * اختبار دقة البيانات
     */
    private function testDataAccuracy($teacherId)
    {
        $this->startTest("اختبار دقة البيانات");
        
        try {
            $response = $this->makeAPIRequest("/teachers/{$teacherId}/mosques");
            
            if ($response && isset($response['البيانات'])) {
                $data = $response['البيانات'];
                
                // الحصول على البيانات من قاعدة البيانات للمقارنة
                $teacher = Teacher::with([
                    'mosque:id,name,neighborhood',
                    'quranCircle:id,name,grade_level,mosque_id',
                    'quranCircle.students:id,name,student_number,phone,quran_circle_id,is_active',
                    'activeMosqueSchedules.mosque:id,name,neighborhood'
                ])->find($teacherId);
                
                if ($teacher) {
                    // التحقق من معلومات المعلم
                    if ($data['معلومات_المعلم']['id'] == $teacher->id &&
                        $data['معلومات_المعلم']['الاسم'] == $teacher->name) {
                        $this->passTest("✅ معلومات المعلم صحيحة");
                    } else {
                        $this->failTest("❌ معلومات المعلم غير صحيحة");
                    }
                    
                    // التحقق من عدد المساجد
                    $expectedMosquesCount = 1; // المسجد الأساسي
                    if ($teacher->activeMosqueSchedules->isNotEmpty()) {
                        $uniqueMosques = $teacher->activeMosqueSchedules->pluck('mosque_id')->unique();
                        $expectedMosquesCount += $uniqueMosques->filter(function($mosqueId) use ($teacher) {
                            return $mosqueId != $teacher->mosque_id;
                        })->count();
                    }
                    
                    if ($data['الإحصائيات']['عدد_المساجد'] == $expectedMosquesCount) {
                        $this->passTest("✅ عدد المساجد صحيح ({$expectedMosquesCount})");
                    } else {
                        $this->failTest("❌ عدد المساجد غير صحيح. متوقع: {$expectedMosquesCount}, فعلي: {$data['الإحصائيات']['عدد_المساجد']}");
                    }
                    
                    // التحقق من عدد الطلاب
                    $expectedStudentsCount = 0;
                    if ($teacher->quranCircle && $teacher->quranCircle->students) {
                        $expectedStudentsCount = $teacher->quranCircle->students->count();
                    }
                    
                    if ($data['الإحصائيات']['إجمالي_الطلاب'] == $expectedStudentsCount) {
                        $this->passTest("✅ عدد الطلاب صحيح ({$expectedStudentsCount})");
                    } else {
                        $this->failTest("❌ عدد الطلاب غير صحيح. متوقع: {$expectedStudentsCount}, فعلي: {$data['الإحصائيات']['إجمالي_الطلاب']}");
                    }
                    
                    // التحقق من بيانات المساجد
                    if (!empty($data['المساجد'])) {
                        $primaryMosque = null;
                        foreach ($data['المساجد'] as $mosque) {
                            if ($mosque['النوع'] === 'مسجد أساسي') {
                                $primaryMosque = $mosque;
                                break;
                            }
                        }
                        
                        if ($primaryMosque && $teacher->mosque) {
                            if ($primaryMosque['id'] == $teacher->mosque->id &&
                                $primaryMosque['اسم_المسجد'] == $teacher->mosque->name) {
                                $this->passTest("✅ بيانات المسجد الأساسي صحيحة");
                            } else {
                                $this->failTest("❌ بيانات المسجد الأساسي غير صحيحة");
                            }
                        }
                    }
                } else {
                    $this->failTest("❌ لا يمكن العثور على المعلم في قاعدة البيانات");
                }
            } else {
                $this->failTest("❌ لا توجد بيانات في الاستجابة للمقارنة");
            }
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في اختبار دقة البيانات: " . $e->getMessage());
        }
    }
    
    /**
     * تنظيف بيانات الاختبار
     */
    private function cleanupTestData($teacherId)
    {
        $this->startTest("تنظيف بيانات الاختبار");
        
        try {
            // حذف البيانات المرتبطة
            $teacher = Teacher::find($teacherId);
            if ($teacher) {
                // حذف الطلاب
                if ($teacher->quranCircle) {
                    Student::where('quran_circle_id', $teacher->quranCircle->id)->delete();
                    $teacher->quranCircle->delete();
                }
                
                // حذف جداول العمل
                TeacherMosqueSchedule::where('teacher_id', $teacher->id)->delete();
                
                // حذف المعلم
                $teacher->delete();
            }
            
            // حذف المساجد
            Mosque::where('name', 'LIKE', 'مسجد الاختبار%')->delete();
            
            $this->passTest("✅ تم تنظيف بيانات الاختبار بنجاح");
        } catch (Exception $e) {
            $this->failTest("❌ خطأ في تنظيف بيانات الاختبار: " . $e->getMessage());
        }
    }
    
    /**
     * إرسال طلب API
     */
    private function makeAPIRequest($endpoint)
    {
        $url = $this->baseUrl . $endpoint;
        
        // استخدام cURL لإرسال الطلب
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception("فشل في إرسال الطلب إلى: $url");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * بدء اختبار جديد
     */
    private function startTest($testName)
    {
        echo "🧪 $testName... ";
        $this->testResults['total']++;
    }
    
    /**
     * نجح الاختبار
     */
    private function passTest($message)
    {
        echo "$message\n";
        $this->testResults['passed']++;
        $this->testResults['details'][] = ['status' => 'pass', 'message' => $message];
    }
    
    /**
     * فشل الاختبار
     */
    private function failTest($message)
    {
        echo "$message\n";
        $this->testResults['failed']++;
        $this->testResults['details'][] = ['status' => 'fail', 'message' => $message];
    }
    
    /**
     * عرض النتائج النهائية
     */
    private function displayResults()
    {
        echo "\n========================================\n";
        echo "📊 نتائج الاختبار النهائية\n";
        echo "========================================\n";
        echo "إجمالي الاختبارات: {$this->testResults['total']}\n";
        echo "الاختبارات الناجحة: {$this->testResults['passed']}\n";
        echo "الاختبارات الفاشلة: {$this->testResults['failed']}\n";
        
        $successRate = $this->testResults['total'] > 0 ? 
            round(($this->testResults['passed'] / $this->testResults['total']) * 100, 2) : 0;
        echo "معدل النجاح: {$successRate}%\n";
        
        if ($this->testResults['failed'] > 0) {
            echo "\n❌ الاختبارات الفاشلة:\n";
            foreach ($this->testResults['details'] as $detail) {
                if ($detail['status'] === 'fail') {
                    echo "   • {$detail['message']}\n";
                }
            }
        }
        
        echo "\n🎯 التوصيات:\n";
        if ($successRate >= 90) {
            echo "   • ✅ API يعمل بشكل ممتاز!\n";
        } elseif ($successRate >= 70) {
            echo "   • ⚠️ API يعمل بشكل جيد مع بعض المشاكل البسيطة\n";
        } else {
            echo "   • ❌ API يحتاج إلى مراجعة وإصلاح\n";
        }
        
        echo "\n📋 معلومات إضافية:\n";
        echo "   • رابط API: GET /api/teachers/{id}/mosques\n";
        echo "   • طريقة الاستخدام: curl -X GET 'http://localhost/api/teachers/1/mosques'\n";
        echo "   • معلومات المطور: نظام إدارة مركز تحفيظ القرآن الكريم\n";
        echo "   • تاريخ الاختبار: " . date('Y-m-d H:i:s') . "\n";
    }
}

// تشغيل الاختبار
try {
    $test = new TeacherMosquesAPITest();
    $test->runAllTests();
} catch (Exception $e) {
    echo "❌ خطأ عام في تشغيل الاختبار: " . $e->getMessage() . "\n";
    echo "تأكد من:\n";
    echo "   • تشغيل خادم Laravel (php artisan serve)\n";
    echo "   • صحة إعدادات قاعدة البيانات\n";
    echo "   • وجود جداول المعلمين والمساجد والحلقات\n";
}
