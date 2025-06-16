<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\TeacherMosqueSchedule;

// تحديد مسار Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "إنشاء بيانات تجريبية لجداول المعلمين في المساجد...\n";

try {
    // الحصول على المعلمين والمساجد الموجودة
    $teachers = Teacher::take(3)->get();
    $mosques = Mosque::take(3)->get();

    if ($teachers->count() == 0) {
        echo "لا توجد معلمين في قاعدة البيانات\n";
        exit;
    }

    if ($mosques->count() == 0) {
        echo "لا توجد مساجد في قاعدة البيانات\n";
        exit;
    }

    // إنشاء جداول تجريبية
    $schedules = [
        [
            'teacher_id' => $teachers[0]->id,
            'mosque_id' => $mosques[0]->id,
            'day_of_week' => 'الأحد',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'session_type' => 'تحفيظ القرآن',
            'notes' => 'حلقة للمبتدئين',
            'is_active' => true,
        ],
        [
            'teacher_id' => $teachers[0]->id,
            'mosque_id' => $mosques[1]->id,
            'day_of_week' => 'الثلاثاء',
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'session_type' => 'تحفيظ القرآن',
            'notes' => 'حلقة للمتقدمين',
            'is_active' => true,
        ],
        [
            'teacher_id' => $teachers[1]->id,
            'mosque_id' => $mosques[0]->id,
            'day_of_week' => 'الأربعاء',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'session_type' => 'تجويد',
            'notes' => 'دروس التجويد',
            'is_active' => true,
        ],
        [
            'teacher_id' => $teachers[1]->id,
            'mosque_id' => $mosques[2]->id,
            'day_of_week' => 'الخميس',
            'start_time' => '15:30:00',
            'end_time' => '17:30:00',
            'session_type' => 'تحفيظ القرآن',
            'notes' => 'حلقة مسائية',
            'is_active' => true,
        ],
        [
            'teacher_id' => $teachers[2]->id,
            'mosque_id' => $mosques[1]->id,
            'day_of_week' => 'الجمعة',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'session_type' => 'تفسير',
            'notes' => 'درس تفسير القرآن',
            'is_active' => true,
        ],
    ];

    foreach ($schedules as $schedule) {
        $existingSchedule = TeacherMosqueSchedule::where('teacher_id', $schedule['teacher_id'])
            ->where('mosque_id', $schedule['mosque_id'])
            ->where('day_of_week', $schedule['day_of_week'])
            ->first();

        if (!$existingSchedule) {
            TeacherMosqueSchedule::create($schedule);
            echo "تم إنشاء جدول للمعلم {$teachers->find($schedule['teacher_id'])->user->name} في مسجد {$mosques->find($schedule['mosque_id'])->name} يوم {$schedule['day_of_week']}\n";
        } else {
            echo "الجدول موجود مسبقاً للمعلم {$teachers->find($schedule['teacher_id'])->user->name} في مسجد {$mosques->find($schedule['mosque_id'])->name} يوم {$schedule['day_of_week']}\n";
        }
    }

    echo "\n=== إحصائيات النظام ===\n";
    echo "إجمالي الجداول: " . TeacherMosqueSchedule::count() . "\n";
    echo "الجداول النشطة: " . TeacherMosqueSchedule::where('is_active', true)->count() . "\n";
    
    $teachersWithMultipleMosques = Teacher::whereHas('mosqueSchedules', function ($query) {
        $query->where('is_active', true);
    })->withCount(['mosqueSchedules' => function ($query) {
        $query->where('is_active', true)->distinct('mosque_id');
    }])->having('mosque_schedules_count', '>', 1)->count();
    
    echo "المعلمون في مساجد متعددة: " . $teachersWithMultipleMosques . "\n";

    echo "\n=== جداول المعلمين ===\n";
    foreach ($teachers as $teacher) {
        $teacherSchedules = $teacher->mosqueSchedules()->with('mosque')->get();
        if ($teacherSchedules->count() > 0) {
            echo "المعلم: {$teacher->user->name}\n";
            foreach ($teacherSchedules as $schedule) {
                echo "  - {$schedule->mosque->name} - {$schedule->day_of_week} ({$schedule->getFormattedTimeRange()})\n";
            }
            echo "\n";
        }
    }

    echo "تم إنشاء البيانات التجريبية بنجاح!\n";

} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}
