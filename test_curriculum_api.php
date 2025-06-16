<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Student;
use App\Models\StudentCurriculum;
use App\Models\Curriculum;

// بدء Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار نظام المناهج اليومية ===\n\n";

// البحث عن طلاب لديهم مناهج نشطة
echo "1. البحث عن طلاب لديهم مناهج نشطة...\n";

$students = Student::whereHas('curricula', function($q) {
    $q->where('status', 'قيد التنفيذ');
})->with(['curricula' => function($q) {
    $q->where('status', 'قيد التنفيذ')->with('curriculum');
}])->take(3)->get();

if ($students->count() > 0) {
    foreach ($students as $student) {
        echo "الطالب: {$student->name} (ID: {$student->id})\n";
        foreach ($student->curricula as $curriculum) {
            echo "  - المنهج: {$curriculum->curriculum->name} ({$curriculum->curriculum->type})\n";
        }
        echo "---\n";
    }
} else {
    echo "لم يتم العثور على طلاب لديهم مناهج نشطة.\n";
    
    // إنشاء بيانات تجريبية
    echo "\n2. إنشاء بيانات تجريبية...\n";
    
    $curriculum = Curriculum::firstOrCreate([
        'name' => 'منهج تجريبي للاختبار'
    ], [
        'type' => 'منهج تلقين',
        'is_active' => true,
        'description' => 'منهج تجريبي لاختبار النظام'
    ]);
    
    echo "تم إنشاء/العثور على المنهج: {$curriculum->name}\n";
}

// عرض المناهج المتاحة
echo "\n3. المناهج المتاحة في النظام:\n";
$curricula = Curriculum::where('is_active', true)->get();
foreach ($curricula as $curriculum) {
    echo "- {$curriculum->name} ({$curriculum->type})\n";
}

echo "\n=== انتهاء الاختبار ===\n";
