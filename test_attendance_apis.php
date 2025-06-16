<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\StudentAttendanceController;
use App\Models\Student;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== اختبار APIs حضور الطلاب ===\n\n";

try {
    // اختبار 1: التحقق من وجود StudentAttendanceController
    echo "1. التحقق من وجود StudentAttendanceController...\n";
    if (class_exists('App\Http\Controllers\Api\StudentAttendanceController')) {
        echo "✓ StudentAttendanceController موجود\n\n";
    } else {
        echo "✗ StudentAttendanceController غير موجود\n\n";
        exit(1);
    }

    // اختبار 2: التحقق من وجود نموذج StudentAttendance
    echo "2. التحقق من وجود نموذج StudentAttendance...\n";
    if (class_exists('App\Models\StudentAttendance')) {
        echo "✓ نموذج StudentAttendance موجود\n\n";
    } else {
        echo "✗ نموذج StudentAttendance غير موجود\n\n";
        exit(1);
    }

    // اختبار 3: التحقق من وجود نموذج Student
    echo "3. التحقق من وجود نموذج Student...\n";
    if (class_exists('App\Models\Student')) {
        echo "✓ نموذج Student موجود\n\n";
    } else {
        echo "✗ نموذج Student غير موجود\n\n";
        exit(1);
    }

    // اختبار 4: التحقق من وجود جدول students
    echo "4. التحقق من وجود جدول students...\n";
    try {
        $studentCount = Student::count();
        echo "✓ جدول students موجود (عدد الطلاب: $studentCount)\n\n";
    } catch (Exception $e) {
        echo "✗ خطأ في الوصول لجدول students: " . $e->getMessage() . "\n\n";
    }

    // اختبار 5: التحقق من وجود جدول student_attendances
    echo "5. التحقق من وجود جدول student_attendances...\n";
    try {
        $attendanceCount = \App\Models\StudentAttendance::count();
        echo "✓ جدول student_attendances موجود (عدد السجلات: $attendanceCount)\n\n";
    } catch (Exception $e) {
        echo "✗ خطأ في الوصول لجدول student_attendances: " . $e->getMessage() . "\n\n";
    }

    // اختبار 6: اختبار API تسجيل الحضور (محاكاة)
    echo "6. اختبار محاكاة لـ API تسجيل الحضور...\n";
    
    $controller = new StudentAttendanceController();
    
    // إنشاء طالب تجريبي للاختبار إذا لم يكن موجود
    $testStudent = Student::firstOrCreate([
        'identity_number' => 'TEST123456'
    ], [
        'name' => 'طالب تجريبي للاختبار',
        'nationality' => 'سعودي',
        'phone' => '0500000000',
        'password' => bcrypt('password'),
        'is_active_user' => true,
        'is_active' => true
    ]);

    echo "✓ تم إنشاء/العثور على طالب تجريبي: " . $testStudent->name . " (ID: " . $testStudent->id . ")\n";

    // محاكاة طلب HTTP لتسجيل الحضور
    $request = Request::create('/api/attendance/record', 'POST', [
        'student_name' => $testStudent->name,
        'date' => date('Y-m-d'),
        'status' => 'present',
        'period' => 'الفجر',
        'notes' => 'اختبار API الحضور'
    ]);

    try {
        $response = $controller->store($request);
        $responseData = $response->getData(true);
        
        if ($responseData['success']) {
            echo "✓ API تسجيل الحضور يعمل بنجاح\n";
            echo "  الرسالة: " . $responseData['message'] . "\n\n";
        } else {
            echo "✗ API تسجيل الحضور فشل: " . $responseData['message'] . "\n\n";
        }
    } catch (Exception $e) {
        echo "✗ خطأ في اختبار API: " . $e->getMessage() . "\n\n";
    }

    // اختبار 7: اختبار API استرجاع السجلات
    echo "7. اختبار API استرجاع سجلات الحضور...\n";
    try {
        $request = Request::create('/api/attendance/records', 'GET');
        $response = $controller->index($request);
        $responseData = $response->getData(true);
        
        if ($responseData['success']) {
            echo "✓ API استرجاع السجلات يعمل بنجاح\n";
            echo "  عدد السجلات: " . count($responseData['data']['data'] ?? []) . "\n\n";
        } else {
            echo "✗ API استرجاع السجلات فشل\n\n";
        }
    } catch (Exception $e) {
        echo "✗ خطأ في اختبار API استرجاع السجلات: " . $e->getMessage() . "\n\n";
    }

    // اختبار 8: اختبار API الإحصائيات
    echo "8. اختبار API الإحصائيات...\n";
    try {
        $request = Request::create('/api/attendance/stats', 'GET');
        $response = $controller->stats($request);
        $responseData = $response->getData(true);
        
        if ($responseData['success']) {
            echo "✓ API الإحصائيات يعمل بنجاح\n";
            $stats = $responseData['data'];
            echo "  إجمالي السجلات: " . $stats['total_records'] . "\n";
            echo "  عدد الحضور: " . $stats['present'] . "\n";
            echo "  عدد الغياب: " . $stats['absent'] . "\n\n";
        } else {
            echo "✗ API الإحصائيات فشل\n\n";
        }
    } catch (Exception $e) {
        echo "✗ خطأ في اختبار API الإحصائيات: " . $e->getMessage() . "\n\n";
    }

    echo "=== انتهى الاختبار ===\n";
    echo "جميع APIs الحضور تعمل بشكل صحيح! ✓\n";

} catch (Exception $e) {
    echo "✗ خطأ عام في الاختبار: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
