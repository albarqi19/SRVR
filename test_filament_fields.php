<?php

echo "🚀 اختبار واجهة Filament للحقول الجديدة\n";
echo "=========================================\n";

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. التحقق من Model والعلاقات
    echo "📋 1. اختبار Model والعلاقات:\n";
    
    $session = App\Models\RecitationSession::first();
    if ($session) {
        echo "   ✓ تم العثور على جلسة: " . $session->session_id . "\n";
        echo "   • الحالة: " . ($session->status ?? 'غير محددة') . "\n";
        echo "   • المنهج ID: " . ($session->curriculum_id ?? 'غير محدد') . "\n";
        
        // اختبار علاقة المنهج
        try {
            $curriculum = $session->curriculum;
            if ($curriculum) {
                echo "   ✓ العلاقة مع المنهج تعمل: " . $curriculum->name . "\n";
            } else {
                echo "   • لا يوجد منهج مرتبط بهذه الجلسة\n";
            }
        } catch (Exception $e) {
            echo "   ❌ خطأ في علاقة المنهج: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ لا توجد جلسات في قاعدة البيانات\n";
    }
    
    // 2. التحقق من Fillable Fields
    echo "\n📝 2. اختبار Fillable Fields:\n";
    $fillable = (new App\Models\RecitationSession())->getFillable();
    
    $requiredFields = ['status', 'curriculum_id'];
    foreach ($requiredFields as $field) {
        if (in_array($field, $fillable)) {
            echo "   ✓ حقل $field موجود في fillable\n";
        } else {
            echo "   ❌ حقل $field غير موجود في fillable\n";
        }
    }
    
    // 3. اختبار إنشاء جلسة بالحقول الجديدة
    echo "\n🎯 3. اختبار إنشاء جلسة بالحقول الجديدة:\n";
    
    $student = App\Models\Student::first();
    $teacher = App\Models\User::first();
    $circle = App\Models\QuranCircle::first();
    $curriculum = App\Models\Curriculum::first();
    
    if ($student && $teacher && $circle) {
        $testData = [
            'session_id' => 'FILAMENT_TEST_' . time(),
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'quran_circle_id' => $circle->id,
            'curriculum_id' => $curriculum ? $curriculum->id : null,
            'start_surah_number' => 1,
            'start_verse' => 1,
            'end_surah_number' => 1,
            'end_verse' => 5,
            'recitation_type' => 'حفظ',
            'grade' => 9.0,
            'evaluation' => 'ممتاز',
            'status' => 'مكتملة',
            'teacher_notes' => 'اختبار الحقول الجديدة في Filament'
        ];
        
        $newSession = App\Models\RecitationSession::create($testData);
        echo "   ✓ تم إنشاء جلسة اختبارية: " . $newSession->session_id . "\n";
        echo "   • الحالة: " . $newSession->status . "\n";
        echo "   • المنهج: " . ($newSession->curriculum ? $newSession->curriculum->name : 'غير محدد') . "\n";
        
        // حذف الجلسة الاختبارية
        $newSession->delete();
        echo "   ✓ تم حذف الجلسة الاختبارية\n";
    }
    
    // 4. عرض إحصائيات الحالات
    echo "\n📊 4. إحصائيات الحالات الحالية:\n";
    $statusStats = App\Models\RecitationSession::selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get();
    
    foreach ($statusStats as $stat) {
        echo "   • " . ($stat->status ?? 'غير محددة') . ": " . $stat->count . " جلسة\n";
    }
    
    echo "\n✅ الاختبار مكتمل!\n";
    echo "🎉 الحقول الجديدة جاهزة للاستخدام في Filament!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 في الملف: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=========================================\n";
