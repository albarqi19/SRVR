<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// إنشاء تطبيق Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار نظام جدولة المعلمين في المساجد المتعددة ===\n\n";

try {
    // 1. اختبار النماذج
    echo "1. فحص النماذج:\n";
    
    // فحص وجود النماذج
    if (class_exists('App\Models\Teacher')) {
        echo "   ✓ نموذج Teacher موجود\n";
    } else {
        echo "   ✗ نموذج Teacher غير موجود\n";
    }
    
    if (class_exists('App\Models\Mosque')) {
        echo "   ✓ نموذج Mosque موجود\n";
    } else {
        echo "   ✗ نموذج Mosque غير موجود\n";
    }
    
    if (class_exists('App\Models\TeacherMosqueSchedule')) {
        echo "   ✓ نموذج TeacherMosqueSchedule موجود\n";
    } else {
        echo "   ✗ نموذج TeacherMosqueSchedule غير موجود\n";
    }
    
    echo "\n2. فحص الجداول:\n";
    
    // فحص وجود الجداول
    $tables = [
        'teachers' => 'جدول المعلمين',
        'mosques' => 'جدول المساجد', 
        'teacher_mosque_schedules' => 'جدول جداول المعلمين'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $count = DB::table($table)->count();
            echo "   ✓ $description موجود ($count سجل)\n";
        } catch (Exception $e) {
            echo "   ✗ $description غير موجود أو خطأ: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n3. فحص العلاقات:\n";
    
    // فحص العلاقات
    $teacher = new App\Models\Teacher();
    $mosque = new App\Models\Mosque();
    
    if (method_exists($teacher, 'mosqueSchedules')) {
        echo "   ✓ العلاقة mosqueSchedules موجودة في نموذج Teacher\n";
    } else {
        echo "   ✗ العلاقة mosqueSchedules غير موجودة في نموذج Teacher\n";
    }
    
    if (method_exists($mosque, 'teacherSchedules')) {
        echo "   ✓ العلاقة teacherSchedules موجودة في نموذج Mosque\n";
    } else {
        echo "   ✗ العلاقة teacherSchedules غير موجودة في نموذج Mosque\n";
    }
    
    echo "\n4. إنشاء بيانات تجريبية:\n";
    
    // إنشاء معلم تجريبي إذا لم يكن موجوداً
    $teacher = App\Models\Teacher::first();
    if (!$teacher) {
        echo "   - إنشاء معلم تجريبي...\n";
        $teacher = App\Models\Teacher::create([
            'name' => 'أحمد محمد',
            'phone' => '0501234567',
            'email' => 'ahmed@test.com',
            'hiring_date' => now(),
            'status' => 'نشط',
            'degree' => 'ماجستير'
        ]);
        echo "   ✓ تم إنشاء المعلم: {$teacher->name}\n";
    } else {
        echo "   ✓ المعلم موجود: {$teacher->name}\n";
    }
    
    // إنشاء مسجد تجريبي إذا لم يكن موجوداً
    $mosque = App\Models\Mosque::first();
    if (!$mosque) {
        echo "   - إنشاء مسجد تجريبي...\n";
        $mosque = App\Models\Mosque::create([
            'name' => 'مسجد النور',
            'neighborhood' => 'حي النور',
            'street' => 'شارع الملك فهد'
        ]);
        echo "   ✓ تم إنشاء المسجد: {$mosque->name}\n";
    } else {
        echo "   ✓ المسجد موجود: {$mosque->name}\n";
    }
    
    // إنشاء جدول زمني تجريبي
    $schedule = App\Models\TeacherMosqueSchedule::where('teacher_id', $teacher->id)
                                                ->where('mosque_id', $mosque->id)
                                                ->first();
    
    if (!$schedule) {
        echo "   - إنشاء جدول زمني تجريبي...\n";
        $schedule = App\Models\TeacherMosqueSchedule::create([
            'teacher_id' => $teacher->id,
            'mosque_id' => $mosque->id,
            'day_of_week' => 'السبت',
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'session_type' => 'العصر',
            'notes' => 'جلسة تجريبية',
            'is_active' => true
        ]);
        echo "   ✓ تم إنشاء الجدول الزمني\n";
    } else {
        echo "   ✓ الجدول الزمني موجود\n";
    }
    
    echo "\n5. اختبار العلاقات:\n";
    
    // اختبار العلاقات
    $teacherSchedules = $teacher->mosqueSchedules;
    echo "   ✓ عدد جداول المعلم: " . $teacherSchedules->count() . "\n";
    
    $mosqueSchedules = $mosque->teacherSchedules;
    echo "   ✓ عدد جداول المسجد: " . $mosqueSchedules->count() . "\n";
    
    if ($teacherSchedules->count() > 0) {
        $firstSchedule = $teacherSchedules->first();
        echo "   ✓ تفاصيل الجدول الأول:\n";
        echo "     - اليوم: {$firstSchedule->day_of_week}\n";
        echo "     - الوقت: {$firstSchedule->start_time} - {$firstSchedule->end_time}\n";
        echo "     - نوع الجلسة: {$firstSchedule->session_type}\n";
        echo "     - المسجد: {$firstSchedule->mosque->name}\n";
        echo "     - المعلم: {$firstSchedule->teacher->name}\n";
    }
    
    echo "\n=== تم الانتهاء من الاختبار بنجاح! ===\n";
    echo "النظام يعمل بشكل صحيح والمعلمون مرتبطون بالمساجد من خلال الجداول الزمنية.\n";
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}
