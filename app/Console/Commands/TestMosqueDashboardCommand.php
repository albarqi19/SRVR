<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\MosqueDashboardController;
use App\Models\Mosque;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TestMosqueDashboardCommand extends Command
{
    protected $signature = 'test:mosque-dashboard {mosque_id?} {teacher_id?}';
    protected $description = 'اختبار API لوحة معلومات المسجد';

    public function handle()
    {
        $mosque_id = $this->argument('mosque_id') ?? 1;
        $teacher_id = $this->argument('teacher_id');

        $this->info("🔍 اختبار لوحة معلومات المسجد");
        $this->info("المسجد ID: {$mosque_id}");
        if ($teacher_id) {
            $this->info("المعلم ID: {$teacher_id}");
        }
        $this->newLine();

        try {
            // اختبار البيانات الأساسية
            $this->testBasicData($mosque_id, $teacher_id);
            
            // اختبار Controller
            $this->testController($mosque_id, $teacher_id);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ: " . $e->getMessage());
            $this->error("في الملف: " . $e->getFile() . " السطر: " . $e->getLine());
        }
    }

    private function testBasicData($mosque_id, $teacher_id)
    {
        $this->info("📊 اختبار البيانات الأساسية:");
        
        // فحص المسجد
        $mosque = Mosque::find($mosque_id);
        if ($mosque) {
            $this->info("✅ المسجد موجود: {$mosque->name}");
        } else {
            $this->error("❌ المسجد غير موجود");
            return;
        }

        // فحص المعلم
        if ($teacher_id) {
            $teacher = Teacher::find($teacher_id);
            if ($teacher) {
                $this->info("✅ المعلم موجود: {$teacher->name}");
            } else {
                $this->error("❌ المعلم غير موجود");
                return;
            }
        }

        // فحص الطلاب
        $studentsQuery = Student::where('mosque_id', $mosque_id);
        $totalStudents = $studentsQuery->count();
        $this->info("📚 إجمالي الطلاب في المسجد: {$totalStudents}");

        if ($teacher_id && $totalStudents > 0) {
            $teacherStudents = Student::where('mosque_id', $mosque_id)
                ->whereHas('quranCircle', function($q) use ($teacher_id) {
                    $q->where('teacher_id', $teacher_id);
                })
                ->count();
            $this->info("👨‍🏫 طلاب المعلم: {$teacherStudents}");
        }

        // فحص الحضور
        $today = Carbon::today()->toDateString();
        $attendanceToday = Attendance::where('attendable_type', 'App\\Models\\Student')
            ->where('date', $today)
            ->count();
        $this->info("📅 سجلات حضور اليوم ({$today}): {$attendanceToday}");

        $this->newLine();
    }

    private function testController($mosque_id, $teacher_id)
    {
        $this->info("🎮 اختبار Controller:");
        
        try {
            $controller = new MosqueDashboardController();
            
            // إنشاء request
            $request = new Request();
            if ($teacher_id) {
                $request->merge(['teacher_id' => $teacher_id]);
            }

            // اختبار dashboard
            $this->info("⚡ اختبار dashboard...");
            $response = $controller->dashboard($mosque_id, $request);
            
            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getContent(), true);
            
            $this->info("📊 حالة الاستجابة: {$statusCode}");
            
            if ($statusCode === 200 && $content['success']) {
                $this->info("✅ نجح API Dashboard!");
                $data = $content['data'];
                
                $this->info("🏛️ المسجد: " . $data['mosque']['name']);
                $this->info("📅 التاريخ: " . $data['date']);
                $this->info("👥 عدد الطلاب: " . count($data['students']));
                $this->info("📊 إحصائيات الحضور:");
                
                $stats = $data['attendance_stats'];
                $this->info("  - إجمالي الطلاب: " . $stats['total_students']);
                $this->info("  - حاضر: " . $stats['present']);
                $this->info("  - غائب: " . $stats['absent']);
                $this->info("  - متأخر: " . $stats['late']);
                $this->info("  - مأذون: " . $stats['excused']);
                $this->info("  - غير مسجل: " . $stats['not_recorded']);
                $this->info("  - معدل الحضور: " . $stats['attendance_rate'] . "%");
                
                if (!empty($data['attendance_today'])) {
                    $this->info("🎯 حضور اليوم للطلاب:");
                    foreach ($data['attendance_today'] as $student => $status) {
                        $this->info("  - {$student}: {$status}");
                    }
                }
                
            } else {
                $this->error("❌ فشل API Dashboard");
                if (isset($content['message'])) {
                    $this->error("الرسالة: " . $content['message']);
                }
                if (isset($content['error'])) {
                    $this->error("الخطأ: " . $content['error']);
                }
            }

            $this->newLine();
            
            // اختبار attendanceToday
            $this->info("⚡ اختبار attendanceToday...");
            $response2 = $controller->attendanceToday($mosque_id, $request);
            
            $statusCode2 = $response2->getStatusCode();
            $content2 = json_decode($response2->getContent(), true);
            
            $this->info("📊 حالة الاستجابة: {$statusCode2}");
            
            if ($statusCode2 === 200 && $content2['success']) {
                $this->info("✅ نجح API AttendanceToday!");
                $attendance = $content2['data']['attendance'];
                $this->info("📅 التاريخ: " . $content2['data']['date']);
                $this->info("👥 حضور الطلاب (" . count($attendance) . "):");
                foreach ($attendance as $student => $status) {
                    $this->info("  - {$student}: {$status}");
                }
            } else {
                $this->error("❌ فشل API AttendanceToday");
                if (isset($content2['message'])) {
                    $this->error("الرسالة: " . $content2['message']);
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Controller: " . $e->getMessage());
            $this->error("الملف: " . $e->getFile() . " السطر: " . $e->getLine());
        }
    }
}
