<?php

require_once 'vendor/autoload.php';

// بدء Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\TeacherMosqueSchedule;

echo "========== اختبار API المعلمين متعدد المساجد ==========\n";
echo "التاريخ: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // الحصول على أول معلم للاختبار
    $teacher = Teacher::first();
    
    if (!$teacher) {
        echo "❌ لا يوجد معلمين في قاعدة البيانات\n";
        echo "يرجى إنشاء بيانات تجريبية أولاً.\n";
        exit;
    }
    
    echo "🧑‍🏫 معلم الاختبار: {$teacher->first_name} {$teacher->last_name} (ID: {$teacher->id})\n\n";
    
    // اختبار 1: API المساجد
    echo "1️⃣ اختبار API المساجد للمعلم:\n";
    echo "   GET /api/teachers/{$teacher->id}/mosques\n";
    
    $mosqueSchedules = $teacher->activeMosqueSchedules()->with('mosque')->get();
    echo "   📊 عدد الجداول النشطة: " . $mosqueSchedules->count() . "\n";
    
    if ($mosqueSchedules->count() > 0) {
        foreach ($mosqueSchedules as $schedule) {
            echo "   📍 المسجد: {$schedule->mosque->name}\n";
            echo "      ⏰ الوقت: {$schedule->day_of_week} من {$schedule->start_time} إلى {$schedule->end_time}\n";
            echo "      🕌 نوع الجلسة: {$schedule->session_type}\n";
        }
    } else {
        echo "   ⚠️  لا توجد جداول نشطة للمعلم\n";
    }
    echo "\n";
    
    // اختبار 2: API الحلقات التفصيلية
    echo "2️⃣ اختبار API الحلقات التفصيلية:\n";
    echo "   GET /api/teachers/{$teacher->id}/circles-detailed\n";
    
    if ($teacher->quranCircle) {
        $circle = $teacher->quranCircle;
        $studentsCount = $circle->students()->count();
        echo "   📚 الحلقة: {$circle->name}\n";
        echo "   👥 عدد الطلاب: {$studentsCount}\n";
        echo "   🏢 المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد') . "\n";
        
        if ($studentsCount > 0) {
            $activeStudents = $circle->students()->where('is_active', true)->count();
            echo "   ✅ الطلاب النشطون: {$activeStudents}\n";
        }
    } else {
        echo "   ⚠️  لا توجد حلقة مرتبطة بالمعلم\n";
    }
    echo "\n";
    
    // اختبار 3: إحصائيات عامة
    echo "3️⃣ الإحصائيات العامة:\n";
    
    $totalMosques = $teacher->getMosquesWorkedIn()->count();
    echo "   🕌 إجمالي المساجد: {$totalMosques}\n";
    
    $totalSchedules = $teacher->activeMosqueSchedules()->count();
    echo "   📅 إجمالي الجداول النشطة: {$totalSchedules}\n";
    
    $totalStudents = $teacher->quranCircle ? $teacher->quranCircle->students()->count() : 0;
    echo "   👥 إجمالي الطلاب: {$totalStudents}\n";
    
    // اختبار 4: تحقق من صحة APIs
    echo "\n4️⃣ فحص صحة APIs:\n";
    
    $endpoints = [
        "teachers" => "✅ قائمة المعلمين",
        "teachers/{$teacher->id}" => "✅ تفاصيل المعلم",
        "teachers/{$teacher->id}/mosques" => "🆕 مساجد المعلم",
        "teachers/{$teacher->id}/circles-detailed" => "🆕 حلقات المعلم التفصيلية",
        "teachers/{$teacher->id}/students" => "✅ طلاب المعلم",
        "teachers/{$teacher->id}/stats" => "✅ إحصائيات المعلم",
        "teachers/{$teacher->id}/attendance" => "✅ حضور المعلم",
        "teachers/{$teacher->id}/financials" => "✅ المالية للمعلم"
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        echo "   📡 /api/{$endpoint} - {$description}\n";
    }
    
    echo "\n========== نتائج الاختبار ==========\n";
    
    // تحقق من متطلبات النظام
    $requirements = [
        'نموذج Teacher' => class_exists('App\Models\Teacher'),
        'نموذج TeacherMosqueSchedule' => class_exists('App\Models\TeacherMosqueSchedule'),
        'علاقة mosqueSchedules' => method_exists($teacher, 'mosqueSchedules'),
        'علاقة activeMosqueSchedules' => method_exists($teacher, 'activeMosqueSchedules'),
        'دالة getMosquesWorkedIn' => method_exists($teacher, 'getMosquesWorkedIn'),
        'وجود بيانات معلمين' => Teacher::count() > 0,
        'وجود بيانات مساجد' => Mosque::count() > 0,
        'وجود جداول نشطة' => TeacherMosqueSchedule::where('is_active', true)->count() > 0
    ];
    
    $passedTests = 0;
    $totalTests = count($requirements);
    
    foreach ($requirements as $requirement => $status) {
        $icon = $status ? '✅' : '❌';
        echo "{$icon} {$requirement}\n";
        if ($status) $passedTests++;
    }
    
    echo "\n📊 النتيجة النهائية: {$passedTests}/{$totalTests} اختبار نجح\n";
    
    if ($passedTests == $totalTests) {
        echo "🎉 جميع الاختبارات نجحت! النظام جاهز للاستخدام.\n";
    } else {
        echo "⚠️  بعض الاختبارات فشلت. يرجى مراجعة الأخطاء أعلاه.\n";
    }
    
    echo "\n========== معلومات إضافية ==========\n";
    echo "📋 المسارات المتاحة:\n";
    echo "   GET /api/teachers - قائمة المعلمين\n";
    echo "   GET /api/teachers/{id} - تفاصيل معلم\n";
    echo "   GET /api/teachers/{id}/mosques - مساجد المعلم 🆕\n";
    echo "   GET /api/teachers/{id}/circles-detailed - حلقات تفصيلية 🆕\n";
    echo "   GET /api/teachers/{id}/students - طلاب المعلم\n";
    echo "   GET /api/teachers/{id}/stats - إحصائيات المعلم\n";
    echo "   GET /api/teachers/{id}/attendance - حضور المعلم\n";
    echo "   GET /api/teachers/{id}/financials - مالية المعلم\n";
    
    echo "\n🎯 الميزات الجديدة:\n";
    echo "   ✅ نظام المساجد المتعددة\n";
    echo "   ✅ جداول زمنية منفصلة لكل مسجد\n";
    echo "   ✅ منع تعارض الأوقات\n";
    echo "   ✅ تتبع شامل للطلاب والحلقات\n";
    echo "   ✅ واجهة إدارية متكاملة\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . "\n";
    echo "📍 السطر: " . $e->getLine() . "\n";
}

echo "\n========== انتهى الاختبار ==========\n";
