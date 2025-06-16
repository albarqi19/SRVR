<?php

// Bootstrap Laravel application
require_once __DIR__ . '/bootstrap/app.php';

// Import necessary models
use App\Models\Teacher;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\Mosque;

echo "=== تحليل العلاقات بين المعلم والطلاب ===\n\n";

// Test database connection and get counts
echo "🔍 إحصائيات قاعدة البيانات:\n";
echo "- عدد المعلمين: " . Teacher::count() . "\n";
echo "- عدد الطلاب: " . Student::count() . "\n";
echo "- عدد الحلقات: " . QuranCircle::count() . "\n";
echo "- عدد المساجد: " . Mosque::count() . "\n\n";

// Get first teacher
$teacher = Teacher::first();

if (!$teacher) {
    echo "❌ لا يوجد معلمين في قاعدة البيانات\n";
    exit;
}

echo "👨‍🏫 بيانات المعلم الأول:\n";
echo "- ID: {$teacher->id}\n";
echo "- الاسم: {$teacher->name}\n";
echo "- معرف الحلقة: " . ($teacher->quran_circle_id ?? 'غير محدد') . "\n";
echo "- معرف المسجد: " . ($teacher->mosque_id ?? 'غير محدد') . "\n\n";

echo "📊 طرق جلب الطلاب للمعلم:\n\n";

// Method 1: Students via QuranCircle
if ($teacher->quran_circle_id) {
    $studentsViaCircle = Student::where('quran_circle_id', $teacher->quran_circle_id)->get();
    echo "1️⃣ الطلاب عبر الحلقة (quran_circle_id = {$teacher->quran_circle_id}):\n";
    echo "   عدد الطلاب: " . $studentsViaCircle->count() . "\n";
    
    if ($studentsViaCircle->count() > 0) {
        echo "   أسماء الطلاب:\n";
        foreach ($studentsViaCircle as $student) {
            echo "   - {$student->name} (ID: {$student->id})\n";
        }
    }
    echo "\n";
} else {
    echo "1️⃣ الطلاب عبر الحلقة: غير متاح (المعلم غير مرتبط بحلقة)\n\n";
}

// Method 2: Students via Mosque
if ($teacher->mosque_id) {
    $studentsViaMosque = Student::where('mosque_id', $teacher->mosque_id)->get();
    echo "2️⃣ الطلاب عبر المسجد (mosque_id = {$teacher->mosque_id}):\n";
    echo "   عدد الطلاب: " . $studentsViaMosque->count() . "\n";
    
    if ($studentsViaMosque->count() > 0) {
        echo "   أول 5 طلاب:\n";
        foreach ($studentsViaMosque->take(5) as $student) {
            echo "   - {$student->name} (ID: {$student->id})\n";
        }
    }
    echo "\n";
} else {
    echo "2️⃣ الطلاب عبر المسجد: غير متاح (المعلم غير مرتبط بمسجد)\n\n";
}

echo "🔧 المشكلة الحالية في الـ API:\n";
echo "- endpoint /api/teachers/{id}/students يحاول استخدام علاقة غير موجودة\n";
echo "- يجب تعديل TeacherController::getStudents() للاستعلام المباشر\n";
echo "- أو إضافة علاقة جديدة في Teacher model\n\n";

echo "💡 الحلول المقترحة:\n";
echo "1. استخدام Students عبر QuranCircle إذا كان المعلم مرتبط بحلقة\n";
echo "2. استخدام Students عبر Mosque إذا كان المعلم مرتبط بمسجد\n";
echo "3. دمج النتائج من كلا الطريقتين\n";
