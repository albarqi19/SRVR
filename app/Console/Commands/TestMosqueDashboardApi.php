<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\MosqueDashboardController;
use Illuminate\Http\Request;

class TestMosqueDashboardApi extends Command
{
    protected $signature = 'test:mosque-dashboard';
    protected $description = 'اختبار API لوحة معلومات المسجد المحسن';

    public function handle()
    {
        $this->info('🚀 اختبار API لوحة معلومات المسجد المحسن');
        $this->newLine();

        try {
            // 1. اختبار بدون معلم محدد
            $this->info('1️⃣ اختبار بدون معلم محدد:');
            $controller = app(MosqueDashboardController::class);
            $request = new Request();
            
            $response = $controller->dashboard(1, $request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $this->info('✅ API يعمل بنجاح');
                $this->line('📊 إحصائيات:');
                $stats = $data['data']['attendance_stats'];
                $this->line("   - إجمالي الطلاب: {$stats['total_students']}");
                $this->line("   - معدل الحضور: {$stats['attendance_rate']}%");
                
                $this->line('👥 أسماء الطلاب:');
                foreach ($data['data']['students'] as $student) {
                    $this->line("   - {$student['name']} (حلقة: {$student['circle_id']})");
                }
            } else {
                $this->error('❌ فشل: ' . $data['message']);
            }
            
            $this->newLine();
            
            // 2. اختبار مع معلم محدد
            $this->info('2️⃣ اختبار مع معلم محدد (ID: 70):');
            $request = new Request(['teacher_id' => 70]);
            
            $response = $controller->dashboard(1, $request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $this->info('✅ API يعمل مع المعلم المحدد');
                $stats = $data['data']['attendance_stats'];
                $this->line("   - طلاب المعلم: {$stats['total_students']}");
                $this->line("   - معدل الحضور: {$stats['attendance_rate']}%");
                
                if (!empty($data['data']['attendance_today'])) {
                    $this->line('👥 حضور الطلاب اليوم:');
                    foreach ($data['data']['attendance_today'] as $student => $status) {
                        $this->line("   - {$student}: {$status}");
                    }
                } else {
                    $this->warn('⚠️ لا توجد بيانات حضور اليوم');
                }
            } else {
                $this->error('❌ فشل مع المعلم: ' . $data['message']);
            }
            
            $this->newLine();
            
            // 3. اختبار API البسيط
            $this->info('3️⃣ اختبار API حضور اليوم البسيط:');
            $request = new Request();
            
            $response = $controller->attendanceToday(1, $request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $this->info('✅ API البسيط يعمل');
                $this->line("📅 تاريخ: {$data['data']['date']}");
                $this->line("👥 عدد الطلاب: " . count($data['data']['attendance']));
                
                if (!empty($data['data']['attendance'])) {
                    $this->line('📋 عينة من البيانات:');
                    $count = 0;
                    foreach ($data['data']['attendance'] as $student => $status) {
                        if ($count < 3) {
                            $this->line("   - {$student}: {$status}");
                            $count++;
                        } else {
                            break;
                        }
                    }
                    if (count($data['data']['attendance']) > 3) {
                        $this->line("   ... و " . (count($data['data']['attendance']) - 3) . " طلاب آخرين");
                    }
                }
            } else {
                $this->error('❌ فشل API البسيط: ' . $data['message']);
            }
            
            $this->newLine();
            
            // 4. اختبار مع معلم له طلاب
            $this->info('4️⃣ البحث عن معلم له طلاب فعلياً:');
            
            // البحث عن معلم نشط مع طلاب
            $teachers = \App\Models\Teacher::whereHas('activeCircles.students')->take(3)->get();
            
            if ($teachers->count() > 0) {
                foreach ($teachers as $teacher) {
                    $this->line("📝 اختبار مع المعلم: {$teacher->name} (ID: {$teacher->id})");
                    
                    $request = new Request(['teacher_id' => $teacher->id]);
                    $response = $controller->dashboard(1, $request);
                    $data = json_decode($response->getContent(), true);
                    
                    if ($data['success']) {
                        $stats = $data['data']['attendance_stats'];
                        $this->info("   ✅ المعلم له {$stats['total_students']} طلاب");
                        if ($stats['total_students'] > 0) {
                            break; // وجدنا معلم له طلاب
                        }
                    } else {
                        $this->line("   ❌ فشل مع المعلم: " . $data['message']);
                    }
                }
            } else {
                $this->warn('⚠️ لم يتم العثور على معلمين لديهم طلاب');
            }
            
            $this->newLine();
            $this->info('🎉 انتهاء الاختبار!');
            
        } catch (\Exception $e) {
            $this->error('💥 خطأ غير متوقع: ' . $e->getMessage());
            $this->line('📄 Stack trace: ' . $e->getTraceAsString());
        }
    }
}
