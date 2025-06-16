<?php

chdir(__DIR__);
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "إنشاء بيانات تجريبية لحضور الطلاب...\n";

try {
    $student = \App\Models\Student::first();
    
    if (!$student) {
        echo "لم يتم العثور على طلاب في النظام\n";
        exit;
    }
    
    echo "الطالب الموجود: " . $student->name . "\n";
    
    // إنشاء سجلات حضور متنوعة
    $attendanceData = [
        [
            'student_id' => $student->id,
            'date' => today(),
            'status' => 'حاضر',
            'recorded_by' => 'Admin',
            'period' => 'الحصة الأولى'
        ],
        [
            'student_id' => $student->id,
            'date' => today()->subDay(),
            'status' => 'غائب',
            'recorded_by' => 'Admin',
            'period' => 'الحصة الأولى'
        ],
        [
            'student_id' => $student->id,
            'date' => today()->subDays(2),
            'status' => 'متأخر',
            'recorded_by' => 'Admin',
            'period' => 'الحصة الأولى'
        ],
        [
            'student_id' => $student->id,
            'date' => today()->subDays(3),
            'status' => 'مأذون',
            'excuse_reason' => 'عذر مرضي',
            'recorded_by' => 'Admin',
            'period' => 'الحصة الأولى'
        ]
    ];
    
    foreach ($attendanceData as $data) {
        $attendance = \App\Models\StudentAttendance::create($data);
        echo "تم إنشاء سجل حضور بحالة: " . $attendance->status . " في تاريخ: " . $attendance->date . "\n";
    }
    
    echo "\nتم إنشاء " . count($attendanceData) . " سجل حضور بنجاح!\n";
    
    // عرض جميع السجلات
    echo "\nسجلات الحضور الحالية:\n";
    $records = \App\Models\StudentAttendance::with('student')->get();
    foreach ($records as $record) {
        echo "- " . $record->student->name . " | " . $record->date . " | " . $record->status . "\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getTraceAsString() . "\n";
}
