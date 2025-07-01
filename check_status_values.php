<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 فحص قيم الحالة:\n";

// فحص قيم status في circle_groups
$statuses = DB::table('circle_groups')->distinct()->pluck('status');
echo "قيم الحالة في circle_groups: ";
foreach($statuses as $s) {
    echo "'$s', ";
}
echo "\n\n";

// اختبار الاستعلام مع القيم المختلفة
echo "اختبار الاستعلامات:\n";
echo "1. البحث بـ 'active': " . DB::table('circle_groups')->where('status', 'active')->count() . "\n";
echo "2. البحث بـ 'نشطة': " . DB::table('circle_groups')->where('status', 'نشطة')->count() . "\n";
echo "3. البحث بـ 'غير نشطة': " . DB::table('circle_groups')->where('status', 'غير نشطة')->count() . "\n";

?>
