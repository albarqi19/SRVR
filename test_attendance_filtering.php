<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\StudentAttendance;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار فلترة حضور الطلاب بالمعلم والمسجد ===\n\n";

function testAPI($url, $description) {
    echo "🔍 $description\n";
    echo "📤 الطلب: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    echo "📥 الرد (كود $httpCode):\n";
    if ($data && isset($data['success']) && $data['success']) {
        echo "  ✓ النجاح: نعم\n";
        if (isset($data['data']['data'])) {
            $records = $data['data']['data'];
            echo "  📊 عدد السجلات: " . count($records) . "\n";
            
            // Show student names found
            $studentNames = array_unique(array_map(function($record) {
                return $record['student']['name'] ?? 'غير معروف';
            }, $records));
            
            echo "  👥 الطلاب الموجودون: " . implode(', ', $studentNames) . "\n";
        }
    } else {
        echo "  ❌ النجاح: لا\n";
        if (isset($data['message'])) {
            echo "  📝 الرسالة: " . $data['message'] . "\n";
        }
        if (isset($data['error'])) {
            echo "  ⚠️ الخطأ: " . $data['error'] . "\n";
        }
    }
    echo "\n";
    
    return $data;
}

try {
    // 1. Test: Get all attendance records (no filters)
    echo "1. اختبار: جلب جميع السجلات (بدون فلاتر)\n";
    $allRecords = testAPI(
        'http://localhost/garb-project/public/api/attendance/records',
        'جلب جميع سجلات الحضور'
    );
    
    // 2. Test: Filter by teacher_id = 1
    echo "2. اختبار: فلترة بالمعلم ID = 1\n";
    $teacherRecords = testAPI(
        'http://localhost/garb-project/public/api/attendance/records?teacher_id=1',
        'فلترة سجلات الحضور بالمعلم رقم 1'
    );
    
    // 3. Test: Filter by mosque_id = 2
    echo "3. اختبار: فلترة بالمسجد ID = 2\n";
    $mosqueRecords = testAPI(
        'http://localhost/garb-project/public/api/attendance/records?mosque_id=2',
        'فلترة سجلات الحضور بالمسجد رقم 2'
    );
    
    // 4. Test: Filter by both teacher_id = 1 and mosque_id = 2
    echo "4. اختبار: فلترة بالمعلم والمسجد معاً\n";
    $combinedRecords = testAPI(
        'http://localhost/garb-project/public/api/attendance/records?teacher_id=1&mosque_id=2',
        'فلترة سجلات الحضور بالمعلم رقم 1 والمسجد رقم 2'
    );
    
    // 5. Compare with specific teacher-mosque-students API
    echo "5. مقارنة مع API الطلاب المخصص\n";
    $specificStudents = testAPI(
        'http://localhost/garb-project/public/api/teachers/1/mosques/2/students',
        'جلب طلاب المعلم رقم 1 في المسجد رقم 2'
    );
    
    // Analysis
    echo "=== تحليل النتائج ===\n";
    
    $allCount = isset($allRecords['data']['data']) ? count($allRecords['data']['data']) : 0;
    $teacherCount = isset($teacherRecords['data']['data']) ? count($teacherRecords['data']['data']) : 0;
    $mosqueCount = isset($mosqueRecords['data']['data']) ? count($mosqueRecords['data']['data']) : 0;
    $combinedCount = isset($combinedRecords['data']['data']) ? count($combinedRecords['data']['data']) : 0;
    $specificCount = isset($specificStudents['data']) ? count($specificStudents['data']) : 0;
    
    echo "📊 عدد السجلات:\n";
    echo "  - جميع السجلات: $allCount\n";
    echo "  - فلترة بالمعلم: $teacherCount\n";
    echo "  - فلترة بالمسجد: $mosqueCount\n";
    echo "  - فلترة مشتركة: $combinedCount\n";
    echo "  - API المخصص: $specificCount طالب\n\n";
    
    // Check if filtering is working
    echo "🔍 تحليل الفلترة:\n";
    
    if ($teacherCount < $allCount) {
        echo "  ✓ فلترة المعلم تعمل (عدد أقل من الكل)\n";
    } else {
        echo "  ❌ فلترة المعلم قد لا تعمل\n";
    }
    
    if ($mosqueCount < $allCount) {
        echo "  ✓ فلترة المسجد تعمل (عدد أقل من الكل)\n";
    } else {
        echo "  ❌ فلترة المسجد قد لا تعمل\n";
    }
    
    if ($combinedCount <= $teacherCount && $combinedCount <= $mosqueCount) {
        echo "  ✓ الفلترة المشتركة تعمل بشكل صحيح\n";
    } else {
        echo "  ❌ الفلترة المشتركة قد لا تعمل بشكل صحيح\n";
    }
    
    echo "\n";
    
    // Additional database checks
    echo "=== فحص قاعدة البيانات ===\n";
    
    // Check student-teacher-mosque relationships
    $studentsWithRelations = Student::with(['mosque', 'quranCircle.activeTeachers'])
        ->get()
        ->map(function($student) {
            $teachers = $student->quranCircle ? 
                $student->quranCircle->activeTeachers->pluck('name')->toArray() : [];
            return [
                'student' => $student->name,
                'mosque' => $student->mosque ? $student->mosque->name : 'غير محدد',
                'teachers' => $teachers
            ];
        });
    
    echo "👥 علاقات الطلاب:\n";
    foreach ($studentsWithRelations as $relation) {
        echo "  • {$relation['student']} - {$relation['mosque']} - المعلمون: " . 
             (empty($relation['teachers']) ? 'لا يوجد' : implode(', ', $relation['teachers'])) . "\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "📍 في الملف: " . $e->getFile() . " السطر: " . $e->getLine() . "\n";
}

echo "\n🎯 انتهى الاختبار!\n";
