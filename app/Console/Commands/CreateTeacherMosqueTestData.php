<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\TeacherMosqueSchedule;

class CreateTeacherMosqueTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teacher-mosque:create-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء بيانات تجريبية لجداول المعلمين في المساجد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('إنشاء بيانات تجريبية لجداول المعلمين في المساجد');

        try {
            // الحصول على أول معلم ومسجد من قاعدة البيانات
            $teacher = Teacher::first();
            $mosque = Mosque::first();
            
            if (!$teacher) {
                $this->error('لا يوجد معلمين في قاعدة البيانات');
                return;
            }
            
            if (!$mosque) {
                $this->error('لا يوجد مساجد في قاعدة البيانات');
                return;
            }
            
            $this->info("المعلم: {$teacher->first_name} {$teacher->last_name}");
            $this->info("المسجد: {$mosque->name}");
            
            // إنشاء جدول للمعلم في المسجد
            $schedule = TeacherMosqueSchedule::create([
                'teacher_id' => $teacher->id,
                'mosque_id' => $mosque->id,
                'day_of_week' => 'الأحد',
                'start_time' => '16:00:00', // بعد العصر
                'end_time' => '18:00:00',   // قبل المغرب
                'session_type' => 'حلقة قرآن',
                'notes' => 'حلقة تحفيظ القرآن الكريم للأطفال',
                'is_active' => true
            ]);
            
            $this->info("تم إنشاء جدول بنجاح - ID: {$schedule->id}");
            
            // اختبار العلاقات
            $this->info('اختبار العلاقات:');
            
            // جداول المعلم
            $teacherSchedules = $teacher->mosqueSchedules;
            $this->info("عدد جداول المعلم: {$teacherSchedules->count()}");
            
            // جداول المسجد
            $mosqueSchedules = $mosque->teacherSchedules;
            $this->info("عدد جداول المسجد: {$mosqueSchedules->count()}");
            
            // المساجد التي يعمل بها المعلم
            $mosquesWorkedIn = $teacher->getMosquesWorkedIn();
            $this->info("المساجد التي يعمل بها المعلم: {$mosquesWorkedIn->count()}");
            
            $this->info('تم إنشاء البيانات التجريبية بنجاح!');
            
            // إضافة جدول آخر في يوم مختلف
            if (Mosque::count() > 1) {
                $secondMosque = Mosque::where('id', '!=', $mosque->id)->first();
                if ($secondMosque) {
                    $schedule2 = TeacherMosqueSchedule::create([
                        'teacher_id' => $teacher->id,
                        'mosque_id' => $secondMosque->id,
                        'day_of_week' => 'الثلاثاء',
                        'start_time' => '17:30:00', // بعد العصر
                        'end_time' => '19:00:00',   // بعد المغرب
                        'session_type' => 'دروس تفسير',
                        'notes' => 'دروس تفسير القرآن الكريم للكبار',
                        'is_active' => true
                    ]);
                    
                    $this->info("تم إنشاء جدول ثاني في مسجد آخر - ID: {$schedule2->id}");
                    $this->info("المسجد الثاني: {$secondMosque->name}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("خطأ: {$e->getMessage()}");
        }
    }
}
