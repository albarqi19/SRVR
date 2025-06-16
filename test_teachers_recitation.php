<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Teacher;
use App\Models\RecitationSession;

echo "فحص المعلمين المتاحين:\n";
echo "========================\n";

$teachers = Teacher::select('id', 'name', 'mosque_id', 'quran_circle_id')->get();

if ($teachers->count() > 0) {
    foreach ($teachers as $teacher) {
        echo "المعلم ID: {$teacher->id} - الاسم: {$teacher->name} - المسجد: {$teacher->mosque_id} - الحلقة: {$teacher->quran_circle_id}\n";
    }
} else {
    echo "لا يوجد معلمين في النظام\n";
}

echo "\n";
echo "فحص جلسات التسميع:\n";
echo "==================\n";

$sessions = RecitationSession::with('teacher')->take(5)->get();

if ($sessions->count() > 0) {
    foreach ($sessions as $session) {
        $teacherName = $session->teacher ? $session->teacher->name : 'غير محدد';
        echo "جلسة ID: {$session->id} - المعلم: {$teacherName} - معرف المعلم: {$session->teacher_id}\n";
    }
} else {
    echo "لا توجد جلسات تسميع في النظام\n";
}

echo "\n";
echo "اختبار العلاقة:\n";
echo "===============\n";

// اختبار العلاقة الجديدة
$firstSession = RecitationSession::first();
if ($firstSession) {
    echo "معرف الجلسة: {$firstSession->id}\n";
    echo "معرف المعلم: {$firstSession->teacher_id}\n";
    
    try {
        $teacher = $firstSession->teacher;
        if ($teacher) {
            echo "اسم المعلم (من العلاقة): {$teacher->name}\n";
            echo "العلاقة تعمل بنجاح! ✓\n";
        } else {
            echo "لا يوجد معلم مرتبط بهذه الجلسة\n";
        }
    } catch (Exception $e) {
        echo "خطأ في العلاقة: " . $e->getMessage() . "\n";
    }
} else {
    echo "لا توجد جلسات في النظام\n";
}
