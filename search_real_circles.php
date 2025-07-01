<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 البحث عن الحلقات الحقيقية من الواجهة:\n";
echo str_repeat("=", 60) . "\n\n";

$realCircleNames = [
    'تجربة' => 0,
    '477' => 0,
    'تجربة معلم مكلف' => 0,
    'خالد العنزي' => 0,
    'الشنقيطي' => 7,
    'الشهاب' => 6,
    'السبيعي' => 2,
    'محمدين' => 4,
    'ايمن' => 1,
    'بليدي' => 14
];

echo "📋 البحث في قاعدة البيانات:\n";
foreach ($realCircleNames as $name => $expectedCount) {
    echo "\n🔍 البحث عن '$name' (متوقع: $expectedCount طلاب):\n";
    
    // البحث المباشر
    $directMatch = DB::table('quran_circles')
        ->where('name', $name)
        ->first();
    
    if ($directMatch) {
        $studentCount = DB::table('students')
            ->where('quran_circle_id', $directMatch->id)
            ->where('is_active', true)
            ->count();
        echo "   ✅ مطابقة مباشرة: ID=$directMatch->id, الطلاب=$studentCount\n";
    }
    
    // البحث بـ LIKE
    $likeMatches = DB::table('quran_circles')
        ->where('name', 'LIKE', "%$name%")
        ->get();
    
    if ($likeMatches->count() > 0) {
        echo "   🔎 مطابقات جزئية:\n";
        foreach ($likeMatches as $match) {
            $studentCount = DB::table('students')
                ->where('quran_circle_id', $match->id)
                ->where('is_active', true)
                ->count();
            echo "      - ID=$match->id, الاسم='$match->name', الطلاب=$studentCount\n";
        }
    }
    
    if (!$directMatch && $likeMatches->count() == 0) {
        echo "   ❌ غير موجود في قاعدة البيانات\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 البحث عن المعلمين بالأسماء الحقيقية:\n";

$realTeacherNames = [
    'أحمد10',
    'فهددددد', 
    'خالد العنزي',
    'عبدالله الشنقيطي',
    'محمد الشهاب',
    'فهم السبيعي',
    'أحمد محمدين',
    'ايمن عبدالحميد',
    'أحمد بليدي'
];

foreach ($realTeacherNames as $teacherName) {
    echo "\n👤 البحث عن المعلم '$teacherName':\n";
    
    $teacher = DB::table('teachers')
        ->where('name', 'LIKE', "%$teacherName%")
        ->first();
    
    if ($teacher) {
        echo "   ✅ موجود: ID=$teacher->id, الحلقة=$teacher->quran_circle_id, نشط=" . ($teacher->is_active_user ? 'نعم' : 'لا') . "\n";
        
        // البحث عن اسم الحلقة
        $circle = DB::table('quran_circles')->where('id', $teacher->quran_circle_id)->first();
        if ($circle) {
            echo "   📍 اسم الحلقة: '$circle->name'\n";
            
            $studentCount = DB::table('students')
                ->where('quran_circle_id', $teacher->quran_circle_id)
                ->where('is_active', true)
                ->count();
            echo "   👥 عدد الطلاب الفعلي: $studentCount\n";
        }
    } else {
        echo "   ❌ غير موجود\n";
    }
}

echo "\n✅ انتهى البحث!\n";
?>
