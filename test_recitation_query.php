<?php

require_once 'vendor/autoload.php';

// استخدام Laravel bootstrap
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧪 اختبار استعلام التسميع للمعلم ID=1 في 2025-06-30:\n";
echo "=" * 60 . "\n";

$teacherId = 1;
$date = '2025-06-30';

// الاستعلام الأصلي من الكود
$recitationRecorded = DB::table('recitation_sessions')
    ->whereDate('created_at', $date)
    ->where('teacher_id', $teacherId)
    ->exists();

echo "نتيجة exists(): " . ($recitationRecorded ? 'true' : 'false') . "\n";

// عدد الجلسات
$recitationCount = DB::table('recitation_sessions')
    ->whereDate('created_at', $date)
    ->where('teacher_id', $teacherId)
    ->count();

echo "عدد الجلسات: {$recitationCount}\n";

// عرض الجلسات
$sessions = DB::table('recitation_sessions')
    ->whereDate('created_at', $date)
    ->where('teacher_id', $teacherId)
    ->select('id', 'student_id', 'recitation_type', 'grade', 'created_at')
    ->get();

echo "\nالجلسات الموجودة:\n";
foreach ($sessions as $session) {
    echo "- جلسة {$session->id}: طالب {$session->student_id}, نوع: {$session->recitation_type}, تاريخ: {$session->created_at}\n";
}

// اختبار تاريخ مختلف لمقارنة
echo "\n" . "=" * 60 . "\n";
echo "🧪 اختبار للمعلم ID=34 في نفس التاريخ:\n";

$teacherId2 = 34;
$recitationRecorded2 = DB::table('recitation_sessions')
    ->whereDate('created_at', $date)
    ->where('teacher_id', $teacherId2)
    ->exists();

$recitationCount2 = DB::table('recitation_sessions')
    ->whereDate('created_at', $date)
    ->where('teacher_id', $teacherId2)
    ->count();

echo "المعلم 34 - exists(): " . ($recitationRecorded2 ? 'true' : 'false') . "\n";
echo "المعلم 34 - عدد الجلسات: {$recitationCount2}\n";

// اختبار استعلام SQL مباشر
echo "\n" . "=" * 60 . "\n";
echo "🧪 اختبار SQL مباشر:\n";

$rawQuery = "SELECT COUNT(*) as count FROM recitation_sessions WHERE DATE(created_at) = '2025-06-30' AND teacher_id = 1";
$rawResult = DB::select($rawQuery);

echo "SQL مباشر: {$rawResult[0]->count} جلسة\n";

// اختبار الاستعلام بدون whereDate
$withoutWhereDate = DB::table('recitation_sessions')
    ->where('teacher_id', $teacherId)
    ->where('created_at', '>=', $date . ' 00:00:00')
    ->where('created_at', '<=', $date . ' 23:59:59')
    ->count();

echo "بدون whereDate: {$withoutWhereDate} جلسة\n";

echo "\n🎯 خلاصة: إذا كانت النتائج صحيحة هنا ولكن API لا يعمل، فالمشكلة في مكان آخر!\n";
