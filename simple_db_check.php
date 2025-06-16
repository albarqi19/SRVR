<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== بداية الاختبار ===\n";
    
    // اختبار الاتصال بقاعدة البيانات
    $pdo = DB::connection()->getPdo();
    echo "✅ الاتصال بقاعدة البيانات ناجح\n";
    
    // عد الجداول
    $mosques = DB::table('mosques')->count();
    $circles = DB::table('quran_circles')->count();
    $teachers = DB::table('teachers')->count();
    $students = DB::table('students')->count();
    
    echo "📊 الإحصائيات:\n";
    echo "   المساجد: $mosques\n";
    echo "   الحلقات: $circles\n";
    echo "   المعلمين: $teachers\n";
    echo "   الطلاب: $students\n";
    
    if ($teachers > 0) {
        // جلب أول معلم
        $teacher = DB::table('teachers')->first();
        echo "\n👨‍🏫 أول معلم:\n";
        echo "   ID: " . $teacher->id . "\n";
        echo "   User ID: " . $teacher->user_id . "\n";
        echo "   Mosque ID: " . $teacher->mosque_id . "\n";
        echo "   Circle ID: " . ($teacher->quran_circle_id ?? 'null') . "\n";
        
        // البحث عن الحلقات التابعة لهذا المعلم
        $teacherCircles = DB::table('quran_circles')->where('teacher_id', $teacher->id)->get();
        echo "\n🔗 الحلقات التابعة للمعلم:\n";
        foreach ($teacherCircles as $circle) {
            echo "   - الحلقة: " . $circle->name . " (ID: " . $circle->id . ")\n";
            
            // البحث عن الطلاب في هذه الحلقة
            $circleStudents = DB::table('students')->where('quran_circle_id', $circle->id)->get();
            echo "     الطلاب: " . count($circleStudents) . "\n";
            foreach ($circleStudents as $student) {
                echo "     - " . $student->name . "\n";
            }
        }
    }
    
    echo "\n✅ الاختبار مكتمل\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
