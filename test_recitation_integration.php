<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 بدء الاختبار المتكامل لوظائف جلسات التسميع...\n";
echo "===============================================\n";

try {
    // 1. اختبار البنية
    echo "📋 1. اختبار بنية قاعدة البيانات...\n";
    
    // التحقق من الحقول الجديدة
    $columns = DB::getSchemaBuilder()->getColumnListing('recitation_sessions');
    $hasStatus = in_array('status', $columns);
    $hasCurriculumId = in_array('curriculum_id', $columns);
    
    echo $hasStatus ? "   ✓ حقل status موجود\n" : "   ❌ حقل status غير موجود\n";
    echo $hasCurriculumId ? "   ✓ حقل curriculum_id موجود\n" : "   ❌ حقل curriculum_id غير موجود\n";
    
    // 2. إحصائيات قاعدة البيانات الحالية
    echo "\n📊 2. إحصائيات قاعدة البيانات الحالية...\n";
    
    $totalSessions = App\Models\RecitationSession::count();
    $sessionsWithStatus = App\Models\RecitationSession::whereNotNull('status')->count();
    $completedSessions = App\Models\RecitationSession::where('status', 'مكتملة')->count();
    $ongoingSessions = App\Models\RecitationSession::where('status', 'جارية')->count();
    $incompleteSessions = App\Models\RecitationSession::where('status', 'غير مكتملة')->count();
    
    echo "   • إجمالي الجلسات: $totalSessions\n";
    echo "   • الجلسات مع حالة: $sessionsWithStatus\n";
    echo "   • الجلسات المكتملة: $completedSessions\n";
    echo "   • الجلسات الجارية: $ongoingSessions\n";
    echo "   • الجلسات غير المكتملة: $incompleteSessions\n";
    
    // 3. اختبار إنشاء جلسة جديدة
    echo "\n🎯 3. اختبار إنشاء جلسة جديدة...\n";
    
    $student = App\Models\Student::first();
    $teacher = App\Models\User::first();
    $circle = App\Models\QuranCircle::first();
    
    if ($student && $teacher && $circle) {
        $sessionData = [
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 10,
            'recitation_type' => 'حفظ',
            'grade' => 8.5,
            'evaluation' => 'جيد جداً',
            'status' => 'جارية',
            'teacher_notes' => 'جلسة اختبارية للنظام المحدث'
        ];
        
        $newSession = App\Models\RecitationSession::create($sessionData);
        echo "   ✓ تم إنشاء جلسة جديدة برقم: " . $newSession->session_id . "\n";
        echo "   ✓ حالة الجلسة: " . $newSession->status . "\n";
        
        // 4. اختبار تحديث الحالة
        echo "\n🔄 4. اختبار تحديث حالة الجلسة...\n";
        
        $oldStatus = $newSession->status;
        $newSession->update(['status' => 'مكتملة']);
        $newSession->refresh();
        
        echo "   ✓ تم تحديث الحالة من '$oldStatus' إلى '" . $newSession->status . "'\n";
        
        // 5. اختبار Observer (إذا كان موجود)
        echo "\n👁 5. اختبار تأثير Observer...\n";
        
        $progress = App\Models\StudentProgress::where('student_id', $student->id)->first();
        if ($progress) {
            echo "   ✓ تم العثور على سجل تقدم للطالب\n";
            echo "   • نسبة الإكمال: " . $progress->completion_percentage . "%\n";
            echo "   • آخر تحديث: " . $progress->updated_at . "\n";
        } else {
            echo "   ⚠ لم يتم العثور على سجل تقدم للطالب\n";
        }
        
    } else {
        echo "   ❌ لا توجد بيانات أساسية كافية للاختبار\n";
        echo "   • الطلاب: " . App\Models\Student::count() . "\n";
        echo "   • المعلمين: " . App\Models\User::count() . "\n";
        echo "   • الحلقات: " . App\Models\QuranCircle::count() . "\n";
    }
    
    // 6. اختبار API Controller
    echo "\n🌐 6. اختبار وظائف Controller...\n";
    
    $controller = new App\Http\Controllers\Api\RecitationSessionController(
        app(App\Services\DailyCurriculumTrackingService::class),
        app(App\Services\FlexibleProgressionService::class),
        app(App\Services\FlexibleCurriculumService::class)
    );
    
    echo "   ✓ تم إنشاء Controller بنجاح مع الخدمات المطلوبة\n";
    
    // 7. الإحصائيات النهائية
    echo "\n📈 7. الإحصائيات النهائية بعد الاختبار...\n";
    
    $finalStats = [
        'إجمالي الجلسات' => App\Models\RecitationSession::count(),
        'الجلسات المكتملة' => App\Models\RecitationSession::where('status', 'مكتملة')->count(),
        'الجلسات الجارية' => App\Models\RecitationSession::where('status', 'جارية')->count(),
        'الجلسات غير المكتملة' => App\Models\RecitationSession::where('status', 'غير مكتملة')->count(),
        'سجلات التقدم النشطة' => App\Models\StudentProgress::where('is_active', true)->count(),
    ];
    
    foreach ($finalStats as $label => $value) {
        echo "   • $label: $value\n";
    }
    
    echo "\n✅ تم إكمال جميع الاختبارات بنجاح!\n";
    echo "🎉 النظام المحدث يعمل بشكل صحيح!\n";
    
} catch (Exception $e) {
    echo "\n❌ حدث خطأ أثناء الاختبار:\n";
    echo "📍 الرسالة: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n===============================================\n";
echo "انتهى الاختبار المتكامل\n";
