<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\TeacherMosqueSchedule;

echo "إنشاء بيانات تجريبية لجداول المعلمين في المساجد" . PHP_EOL;

try {
    // الحصول على أول معلم ومسجد من قاعدة البيانات
    $teacher = Teacher::first();
    $mosque = Mosque::first();
    
    if (!$teacher) {
        echo "لا يوجد معلمين في قاعدة البيانات" . PHP_EOL;
        exit;
    }
    
    if (!$mosque) {
        echo "لا يوجد مساجد في قاعدة البيانات" . PHP_EOL;
        exit;
    }
    
    echo "المعلم: " . $teacher->first_name . " " . $teacher->last_name . PHP_EOL;
    echo "المسجد: " . $mosque->name . PHP_EOL;
    
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
    
    echo "تم إنشاء جدول بنجاح - ID: " . $schedule->id . PHP_EOL;
    
    // اختبار العلاقات
    echo "اختبار العلاقات:" . PHP_EOL;
    
    // جداول المعلم
    $teacherSchedules = $teacher->mosqueSchedules;
    echo "عدد جداول المعلم: " . $teacherSchedules->count() . PHP_EOL;
    
    // جداول المسجد
    $mosqueSchedules = $mosque->teacherSchedules;
    echo "عدد جداول المسجد: " . $mosqueSchedules->count() . PHP_EOL;
    
    // المساجد التي يعمل بها المعلم
    $mosquesWorkedIn = $teacher->getMosquesWorkedIn();
    echo "المساجد التي يعمل بها المعلم: " . $mosquesWorkedIn->count() . PHP_EOL;
    
    echo "تم إنشاء البيانات التجريبية بنجاح!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . PHP_EOL;
}
