<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "فحص القيم المسموحة للحقول:\n";
echo str_repeat("=", 40) . "\n\n";

// فحص عمود period
$result = DB::select('SHOW COLUMNS FROM student_attendances LIKE "period"');
echo "عمود period:\n";
print_r($result);

echo "\n";

// فحص عمود status
$result = DB::select('SHOW COLUMNS FROM student_attendances LIKE "status"');
echo "عمود status:\n";
print_r($result);

?>
