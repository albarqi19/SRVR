<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 فحص أسماء الحلقات الدقيقة:\n";
echo str_repeat("=", 50) . "\n";

$circles = DB::table('quran_circles')
    ->select('id', 'name')
    ->orderBy('id')
    ->get();

foreach ($circles as $circle) {
    echo "ID: $circle->id - الاسم: '" . trim($circle->name) . "'\n";
}

echo "\n📊 مقارنة مع الصورة:\n";
echo "من الصورة أرى:\n";
echo "- تجربة: 0 طلاب\n";
echo "- النهجان: 6 طلاب\n";
echo "- السيسبان: 2 طلاب\n";
echo "- محمدين: 4 طلاب\n";
echo "- ليمن: 1 طالب\n";
echo "- بليدي: 14 طالب\n";

echo "\n🔍 البحث عن هذه الأسماء:\n";
$searchNames = ['تجربة', 'النهجان', 'السيسبان', 'محمدين', 'ليمن', 'بليدي'];

foreach ($searchNames as $name) {
    $found = DB::table('quran_circles')
        ->where('name', 'LIKE', "%$name%")
        ->first();
    
    if ($found) {
        $studentCount = DB::table('students')
            ->where('quran_circle_id', $found->id)
            ->where('is_active', true)
            ->count();
        
        echo "✅ '$name' موجود: ID=$found->id, الاسم الكامل='$found->name', الطلاب=$studentCount\n";
    } else {
        echo "❌ '$name' غير موجود\n";
    }
}

?>
