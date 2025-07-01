<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Hash;

// دالة مساعدة للوقت الحالي
function now() {
    return date('Y-m-d H:i:s');
}

// إعداد قاعدة البيانات
$capsule = new DB;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'garb_project',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// مسار ملف CSV
$csvFile = 'التسجيل في حلقات جامع هيلة الحربي الصيفية 1447هـ (الردود) - الورقة1.csv';

// متغيرات العدّ
$successCount = 0;
$duplicateCount = 0;
$errorCount = 0;
$processedStudents = [];

echo "بدء عملية إضافة الطلاب...\n";

try {
    // التحقق من وجود الملف
    if (!file_exists($csvFile)) {
        throw new Exception("ملف CSV غير موجود: $csvFile");
    }

    // قراءة ملف CSV
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        throw new Exception("لا يمكن فتح ملف CSV");
    }

    // تخطي السطر الأول (عناوين الأعمدة)
    $headers = fgetcsv($handle);
    echo "عناوين الأعمدة: " . implode(' | ', $headers) . "\n\n";

    $addedCount = 0;
    $skippedCount = 0;
    $duplicateCount = 0;
    $processedStudents = [];

    // معالجة كل سطر
    while (($data = fgetcsv($handle)) !== false) {
        // التأكد من وجود البيانات الأساسية
        if (count($data) < 3 || empty(trim($data[1]))) {
            $skippedCount++;
            continue;
        }

        // استخراج البيانات (بناءً على ترتيب الأعمدة الصحيح)
        $timestamp = $data[0] ?? '';
        $fullName = trim($data[1] ?? '');  // الاسم الرباعي
        $nationalId = trim($data[2] ?? ''); // رقم الهوية
        $nationality = trim($data[3] ?? ''); // الجنسية
        $birthDate = trim($data[4] ?? ''); // تاريخ الميلاد
        $studentPhone = trim($data[5] ?? ''); // رقم هاتف الطالب
        $parentPhone = trim($data[6] ?? ''); // رقم هاتف ولي الأمر
        $period = trim($data[7] ?? ''); // الفترة
        $grade = trim($data[8] ?? ''); // الصف الدراسي
        $neighborhood = trim($data[9] ?? ''); // الحي

        // تنظيف البيانات
        $email = strtolower(str_replace(' ', '_', $fullName)) . '@temp.com'; // إنشاء إيميل مؤقت
        $studentPhone = preg_replace('/[^0-9+]/', '', $studentPhone);
        $parentPhone = preg_replace('/[^0-9+]/', '', $parentPhone);

        // تجنب المكررات بناءً على الاسم والإيميل
        $studentKey = strtolower($fullName) . '|' . strtolower($email);
        if (in_array($studentKey, $processedStudents)) {
            $duplicateCount++;
            echo "طالب مكرر تم تخطيه: $fullName\n";
            continue;
        }
        $processedStudents[] = $studentKey;

        // التحقق من وجود الطالب في قاعدة البيانات
        $existingStudent = DB::table('students')
            ->where('identity_number', $nationalId)
            ->first();

        if ($existingStudent) {
            $duplicateCount++;
            echo "طالب موجود مسبقاً: $fullName (رقم الهوية: $nationalId)\n";
            continue;
        }

        // إعداد بيانات الطالب - فقط الحقول الموجودة في قاعدة البيانات
        $studentData = [
            'identity_number' => $nationalId,
            'name' => $fullName,
            'nationality' => $nationality,
            'birth_date' => $birthDate,
            'phone' => $studentPhone,
            'guardian_phone' => $parentPhone,
            'neighborhood' => $neighborhood,
            'mosque_id' => 1, // جامع هيلة الحربي
            'enrollment_date' => now(),
            'is_active' => true,
            'education_level' => $grade,
            'created_at' => now(),
            'updated_at' => now()
        ];

        try {
            // البحث عن حلقة تجارب
            $testCircle = DB::table('quran_circles')
                ->where('name', 'LIKE', '%تجارب%')
                ->orWhere('name', 'LIKE', '%test%')
                ->first();

            if ($testCircle) {
                $studentData['quran_circle_id'] = $testCircle->id;
            } else {
                // إنشاء حلقة تجارب إذا لم تكن موجودة
                $circleId = DB::table('quran_circles')->insertGetId([
                    'name' => 'حلقة تجارب',
                    'description' => 'حلقة مؤقتة للطلاب الجدد',
                    'mosque_id' => 1,
                    'capacity' => 1000,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $studentData['quran_circle_id'] = $circleId;
                echo "تم إنشاء حلقة تجارب جديدة برقم: $circleId\n";
            }

            // إضافة الطالب
            // إدراج الطالب في قاعدة البيانات
            $studentId = DB::table('students')->insertGetId($studentData);
            
            $successCount++;
            echo "تم إضافة الطالب: $fullName (المعرف: $studentId)\n";

        } catch (Exception $e) {
            echo "خطأ في إضافة الطالب $fullName: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    fclose($handle);

    // إحصائيات نهائية
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "تمت عملية الإضافة بنجاح!\n";
    echo "عدد الطلاب المضافين: $successCount\n";
    echo "عدد الطلاب المكررين: $duplicateCount\n";
    echo "عدد الأخطاء: $errorCount\n";
    echo "إجمالي الطلاب المعالجين: " . ($successCount + $duplicateCount + $errorCount) . "\n";
    echo str_repeat("=", 50) . "\n";

} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}

?>
