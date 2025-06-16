<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\RecitationSessionController;
use App\Models\Student;
use App\Models\User;
use App\Models\QuranCircle;
use App\Models\Curriculum;
use App\Models\RecitationSession;

class TestApiIssue extends Command
{
    protected $signature = 'test:api-issue';
    protected $description = 'Test API issue with validation';

    public function handle()
    {
        $this->info('=== Testing API vs Direct Creation ===');

        // اختبار 1: إنشاء مباشر (يعمل)
        $this->testDirectCreation();
        
        // اختبار 2: اختبار validation rules
        $this->testValidationRules();
        
        // اختبار 3: محاكاة API request
        $this->testApiController();
        
        $this->info('=== Test Completed ===');
    }

    private function testDirectCreation()
    {
        $this->info('--- Test 1: Direct Model Creation ---');
        
        try {
            $session = RecitationSession::create([
                'student_id' => 1,
                'teacher_id' => 1,
                'quran_circle_id' => 1,
                'session_id' => 'direct_' . time(),
                'start_surah_number' => 1,
                'start_verse' => 1,
                'end_surah_number' => 1,
                'end_verse' => 5,
                'recitation_type' => 'حفظ',
                'duration_minutes' => 30,
                'grade' => 8.5,
                'evaluation' => 'جيد جداً',
                'teacher_notes' => 'Direct creation test',
                'status' => 'مكتملة'
            ]);
            
            $this->info("✅ Direct creation SUCCESS - Session ID: {$session->id}");
            
        } catch (\Exception $e) {
            $this->error("❌ Direct creation FAILED: {$e->getMessage()}");
        }
    }

    private function testValidationRules()
    {
        $this->info('--- Test 2: Validation Rules ---');
        
        $testData = [
            'student_id' => 1,
            'teacher_id' => 1,
            'quran_circle_id' => 1,
            'curriculum_id' => 1,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 7,
            'recitation_type' => 'حفظ',
            'duration_minutes' => 15,
            'grade' => 8.5,
            'evaluation' => 'جيد جداً',
            'teacher_notes' => 'Test validation',
            'status' => 'مكتملة'
        ];

        // Same rules as Controller
        $rules = [
            'student_id' => 'required|exists:students,id',
            'teacher_id' => 'required|exists:users,id',
            'quran_circle_id' => 'required|exists:quran_circles,id',
            'start_surah_number' => 'required|integer|min:1|max:114',
            'start_verse' => 'required|integer|min:1',
            'end_surah_number' => 'required|integer|min:1|max:114',
            'end_verse' => 'required|integer|min:1',
            'recitation_type' => 'required|in:حفظ,مراجعة صغرى,مراجعة كبرى,تثبيت',
            'duration_minutes' => 'nullable|integer|min:1',
            'grade' => 'required|numeric|min:0|max:10',
            'evaluation' => 'required|in:ممتاز,جيد جداً,جيد جدا,جيد,مقبول,ضعيف',
            'teacher_notes' => 'nullable|string|max:1000',
            'curriculum_id' => 'nullable|exists:curriculums,id',
            'status' => 'nullable|in:جارية,غير مكتملة,مكتملة'
        ];

        $validator = Validator::make($testData, $rules);

        if ($validator->fails()) {
            $this->error('❌ Validation FAILED:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }
        } else {
            $this->info('✅ Validation PASSED');
        }
    }

    private function testApiController()
    {
        $this->info('--- Test 3: API Controller ---');
        
        try {
            // Create proper Request with data
            $data = [
                'student_id' => 1,
                'teacher_id' => 1,
                'quran_circle_id' => 1,
                'curriculum_id' => 1,
                'start_surah_number' => 1,
                'start_verse' => 1,
                'end_surah_number' => 1,
                'end_verse' => 7,
                'recitation_type' => 'حفظ',
                'duration_minutes' => 15,
                'grade' => 8.5,
                'evaluation' => 'جيد جداً',
                'teacher_notes' => 'API Controller test',
                'status' => 'مكتملة'
            ];

            // Create Request object
            $request = Request::create('/api/recitation/sessions', 'POST', $data);
            $request->headers->set('Content-Type', 'application/json');
            $request->headers->set('Accept', 'application/json');

            // Create Controller with proper dependencies
            $controller = app(RecitationSessionController::class);

            $response = $controller->store($request);
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);

            if ($statusCode === 201) {
                $this->info('✅ API Controller SUCCESS');
                $this->info("Session ID: {$content['data']['id']}");
            } else {
                $this->error("❌ API Controller FAILED - Status: {$statusCode}");
                $this->error("Response: " . json_encode($content, JSON_UNESCAPED_UNICODE));
            }

        } catch (\Exception $e) {
            $this->error("❌ API Controller EXCEPTION: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}:{$e->getLine()}");
        }
    }
}
