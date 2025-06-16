<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\TeacherController;
use Illuminate\Http\Request;
use App\Models\Teacher;

class TestTeacherAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-api {method=index} {id?} {--debug} {--json} {--performance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Teacher API endpoints directly with enhanced options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $method = $this->argument('method');
        $id = $this->argument('id');
        $startTime = microtime(true);

        $this->info("=== Testing Teacher API ===");
        $this->info("Method: {$method}");
        if ($id) $this->info("ID: {$id}");
        $this->info("Timestamp: " . now()->format('Y-m-d H:i:s'));
        $this->info("=============================");
        
        try {
            $controller = new TeacherController();
            $request = new Request();
            
            // إضافة معلومات قاعدة البيانات
            $this->checkDatabaseConnection();
            
            switch ($method) {
                case 'index':
                    $this->info("🔍 Testing index method...");
                    $response = $controller->index($request);
                    break;
                    
                case 'show':
                    if (!$id) {
                        $this->error("❌ ID is required for show method");
                        return 1;
                    }
                    $this->info("🔍 Testing show method for teacher ID: {$id}");
                    $response = $controller->show($id);
                    break;
                    
                case 'students':
                    if (!$id) {
                        $this->error("❌ ID is required for students method");
                        return 1;
                    }
                    $this->info("🔍 Testing getStudents method for teacher ID: {$id}");
                    $response = $controller->getStudents($id);
                    break;
                    
                case 'all':
                    return $this->testAllMethods();
                    
                default:
                    $this->error("❌ Unknown method: {$method}");
                    $this->info("Available methods: index, show, students, all");
                    return 1;
            }
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            $this->displayResults($response, $executionTime);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->displayError($e);
            return 1;
        }
    }
    
    private function checkDatabaseConnection()
    {
        try {
            $teacherCount = Teacher::count();
            $this->info("📊 Teachers in database: {$teacherCount}");
        } catch (\Exception $e) {
            $this->warn("⚠️  Database connection issue: " . $e->getMessage());
        }
    }
    
    private function displayResults($response, $executionTime)
    {
        $this->info("✅ API call successful!");
        $this->info("📊 Response status: " . $response->getStatusCode());
        
        if ($this->option('performance')) {
            $this->info("⏱️  Execution time: {$executionTime}ms");
        }
        
        $content = $response->getContent();
        $this->info("📏 Response content length: " . strlen($content) . " characters");
        
        if ($this->option('json')) {
            // عرض JSON مُنسق
            try {
                $jsonData = json_decode($content, true);
                if ($jsonData) {
                    $this->info("📄 JSON Response:");
                    $this->line(json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Could not parse JSON: " . $e->getMessage());
            }
        } else {
            // عرض معاينة
            $preview = substr($content, 0, 300);
            $this->info("👀 Response preview:");
            $this->line($preview);
            if (strlen($content) > 300) {
                $this->info("... (truncated, use --json for full output)");
            }
        }
    }
    
    private function displayError($exception)
    {
        $this->error("❌ Error occurred: " . $exception->getMessage());
        $this->error("📁 File: " . $exception->getFile() . ":" . $exception->getLine());
        
        // عرض تفاصيل إضافية للأخطاء الشائعة
        if (strpos($exception->getMessage(), 'SQLSTATE') !== false) {
            $this->warn("💡 This appears to be a database error. Check your database connection and table structure.");
        }
        
        if (strpos($exception->getMessage(), 'Class') !== false && strpos($exception->getMessage(), 'not found') !== false) {
            $this->warn("💡 This appears to be a missing class error. Check your imports and autoloading.");
        }
        
        // عرض stack trace للمطورين
        if ($this->option('debug')) {
            $this->error("🔍 Stack trace:");
            $this->error($exception->getTraceAsString());
        } else {
            $this->info("💡 Use --debug flag for detailed stack trace");
        }
    }
    
    private function testAllMethods()
    {
        $this->info("🚀 Testing all Teacher API methods...");
        $this->info("====================================");
        
        $results = [];
        
        // اختبار index method
        $this->info("\n1️⃣ Testing INDEX method:");
        try {
            $startTime = microtime(true);
            $controller = new TeacherController();
            $request = new Request();
            $response = $controller->index($request);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $results['index'] = [
                'status' => 'success',
                'code' => $response->getStatusCode(),
                'time' => $executionTime,
                'content_length' => strlen($response->getContent())
            ];
            
            $this->info("✅ SUCCESS - Status: {$response->getStatusCode()}, Time: {$executionTime}ms");
            
        } catch (\Exception $e) {
            $results['index'] = ['status' => 'failed', 'error' => $e->getMessage()];
            $this->error("❌ FAILED - " . $e->getMessage());
        }
        
        // البحث عن teacher للاختبار
        $teacherId = $this->findValidTeacherId();
        
        if ($teacherId) {
            // اختبار show method
            $this->info("\n2️⃣ Testing SHOW method (ID: {$teacherId}):");
            try {
                $startTime = microtime(true);
                $controller = new TeacherController();
                $response = $controller->show($teacherId);
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                
                $results['show'] = [
                    'status' => 'success',
                    'code' => $response->getStatusCode(),
                    'time' => $executionTime,
                    'content_length' => strlen($response->getContent())
                ];
                
                $this->info("✅ SUCCESS - Status: {$response->getStatusCode()}, Time: {$executionTime}ms");
                
            } catch (\Exception $e) {
                $results['show'] = ['status' => 'failed', 'error' => $e->getMessage()];
                $this->error("❌ FAILED - " . $e->getMessage());
            }
            
            // اختبار students method
            $this->info("\n3️⃣ Testing STUDENTS method (ID: {$teacherId}):");
            try {
                $startTime = microtime(true);
                $controller = new TeacherController();
                $response = $controller->getStudents($teacherId);
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                
                $results['students'] = [
                    'status' => 'success',
                    'code' => $response->getStatusCode(),
                    'time' => $executionTime,
                    'content_length' => strlen($response->getContent())
                ];
                
                $this->info("✅ SUCCESS - Status: {$response->getStatusCode()}, Time: {$executionTime}ms");
                
            } catch (\Exception $e) {
                $results['students'] = ['status' => 'failed', 'error' => $e->getMessage()];
                $this->error("❌ FAILED - " . $e->getMessage());
            }
        } else {
            $this->warn("⚠️  No valid teacher ID found for show/students tests");
            $results['show'] = ['status' => 'skipped', 'reason' => 'No teacher ID found'];
            $results['students'] = ['status' => 'skipped', 'reason' => 'No teacher ID found'];
        }
        
        // عرض ملخص النتائج
        $this->displaySummary($results);
        
        return array_sum(array_map(function($result) {
            return $result['status'] === 'failed' ? 1 : 0;
        }, $results));
    }
    
    private function findValidTeacherId()
    {
        try {
            $teacher = Teacher::first();
            if ($teacher) {
                $this->info("🎯 Found teacher ID: {$teacher->id} ({$teacher->name})");
                return $teacher->id;
            }
        } catch (\Exception $e) {
            $this->warn("⚠️  Could not find valid teacher: " . $e->getMessage());
        }
        return null;
    }
    
    private function displaySummary($results)
    {
        $this->info("\n📊 TEST SUMMARY");
        $this->info("================");
        
        $successful = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($results as $method => $result) {
            $icon = match($result['status']) {
                'success' => '✅',
                'failed' => '❌',
                'skipped' => '⏭️',
                default => '❓'
            };
            
            $this->info("{$icon} {$method}: {$result['status']}");
            
            if ($result['status'] === 'success') {
                $successful++;
                if (isset($result['time'])) {
                    $this->info("   └─ Response: {$result['code']}, Time: {$result['time']}ms");
                }
            } elseif ($result['status'] === 'failed') {
                $failed++;
                $this->error("   └─ Error: " . $result['error']);
            } else {
                $skipped++;
                if (isset($result['reason'])) {
                    $this->warn("   └─ Reason: " . $result['reason']);
                }
            }
        }
        
        $this->info("\nResults: {$successful} passed, {$failed} failed, {$skipped} skipped");
        
        if ($failed === 0) {
            $this->info("🎉 All tests passed successfully!");
        } else {
            $this->warn("⚠️  Some tests failed. Check the errors above.");
        }
    }
}
