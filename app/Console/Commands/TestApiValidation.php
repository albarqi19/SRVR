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

class TestApiValidation extends Command
{
    protected $signature = 'test:api-validation';
    protected $description = 'Test API validation directly through Laravel';

    public function handle()
    {
        $this->info('=== Testing API Validation ===');

        // فحص البيانات المطلوبة
        $this->checkRequiredData();
        
        // اختبار validation rules
        $this->testValidationRules();
        
        // اختبار Controller مباشرة
        $this->testControllerDirectly();
        
        $this->info('=== Test Completed ===');
    }

    private function checkRequiredData()
    {
        $this->info('--- Checking Required Data ---');
        
        // فحص الطالب
        $student = Student::find(1);
        $this->info($student ? "✅ Student ID 1: {$student->name}" : "❌ Student ID 1 not found");
        
        // فحص المعلم
        $teacher = User::find(1);
        $this->info($teacher ? "✅ Teacher ID 1: {$teacher->name}" : "❌ Teacher ID 1 not found");
        
        // فحص الحلقة
        $circle = QuranCircle::find(1);
        $this->info($circle ? "✅ Circle ID 1: {$circle->name}" : "❌ Circle ID 1 not found");
        
        // فحص المنهج
        $curriculum = Curriculum::find(1);
        $this->info($curriculum ? "✅ Curriculum ID 1: {$curriculum->name}" : "❌ Curriculum ID 1 not found");
    }

    private function testValidationRules()
    {
        $this->info('--- Testing Validation Rules ---');
        
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
            'teacher_notes' => 'Good performance',
            'status' => 'مكتملة'
        ];

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
            $this->error('❌ Validation Failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }
        } else {
            $this->info('✅ Validation Passed!');
        }
    }

    private function testControllerDirectly()
    {
        $this->info('--- Testing Controller Directly ---');
        
        try {
            // إنشاء request مع البيانات
            $request = new Request([
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
                'teacher_notes' => 'Test from Laravel command',
                'status' => 'مكتملة'
            ]);

            // إنشاء Controller وتشغيل store method
            $controller = new RecitationSessionController(
                app(\App\Services\DailyCurriculumTrackingService::class),
                app(\App\Services\FlexibleProgressionService::class),
                app(\App\Services\FlexibleCurriculumService::class)
            );

            $response = $controller->store($request);
            $responseData = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 201) {
                $this->info('✅ Controller Success!');
                $this->info("Session ID: {$responseData['data']['id']}");
                $this->info("Session UUID: {$responseData['data']['session_id']}");
            } else {
                $this->error('❌ Controller Failed!');
                $this->error("Status: {$response->getStatusCode()}");
                $this->error("Response: " . json_encode($responseData, JSON_UNESCAPED_UNICODE));
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception in Controller:');
            $this->error("Error: {$e->getMessage()}");
            $this->error("File: {$e->getFile()}:{$e->getLine()}");
        }
    }
}
