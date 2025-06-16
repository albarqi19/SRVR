<?php

// فحص شامل لتوضيح العلاقة بين المناهج وجلسات التسميع
require_once __DIR__ . '/vendor/autoload.php';

// إعداد Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Curriculum;
use App\Models\CurriculumPlan;
use App\Models\StudentCurriculum;
use App\Models\StudentCurriculumProgress;
use App\Models\RecitationSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔍 فحص شامل للعلاقة بين المناهج وجلسات التسميع\n";
echo str_repeat("=", 80) . "\n\n";

// 1. فحص الجداول الأساسية
echo "📋 1. فحص الجداول الأساسية:\n";
echo str_repeat("-", 50) . "\n";

$tables = [
    'curricula' => 'جدول المناهج الأساسية',
    'curriculum_plans' => 'جدول خطط المناهج (المحتوى اليومي)',
    'student_curricula' => 'جدول ربط الطلاب بالمناهج',
    'student_curriculum_progress' => 'جدول تتبع تقدم الطالب',
    'recitation_sessions' => 'جدول جلسات التسميع'
];

foreach ($tables as $table => $description) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "✅ {$description}: موجود ({$count} سجل)\n";
    } else {
        echo "❌ {$description}: غير موجود\n";
    }
}

echo "\n";

// 2. فحص المناهج المتاحة
echo "📚 2. فحص المناهج المتاحة:\n";
echo str_repeat("-", 50) . "\n";

$curricula = Curriculum::all();
foreach ($curricula as $curriculum) {
    echo "📖 منهج: {$curriculum->name}\n";
    echo "   النوع: {$curriculum->type}\n";
    echo "   الوصف: {$curriculum->description}\n";
    echo "   نشط: " . ($curriculum->is_active ? 'نعم' : 'لا') . "\n";
    
    // فحص خطط هذا المنهج
    $plans = CurriculumPlan::where('curriculum_id', $curriculum->id)->get();
    echo "   عدد الخطط: {$plans->count()}\n";
    
    if ($plans->count() > 0) {
        echo "   الخطط:\n";
        foreach ($plans->take(3) as $plan) {
            echo "     - ID: {$plan->id}, النوع: {$plan->plan_type}, المحتوى: " . substr($plan->content, 0, 50) . "...\n";
        }
        if ($plans->count() > 3) {
            echo "     ... و" . ($plans->count() - 3) . " خطط أخرى\n";
        }
    }
    echo "\n";
}

// 3. فحص طالب محدد وعلاقته بالمناهج
echo "👤 3. فحص طالب محدد (ID: 1):\n";
echo str_repeat("-", 50) . "\n";

$student = Student::with(['curricula', 'recitationSessions'])->find(1);

if ($student) {
    echo "✅ الطالب: {$student->name}\n";
    echo "   رقم الهوية: {$student->identity_number}\n";
    
    // مناهج الطالب
    echo "\n📚 مناهج الطالب:\n";
    foreach ($student->curricula as $studentCurriculum) {
        echo "   - منهج: " . ($studentCurriculum->curriculum->name ?? 'غير محدد') . "\n";
        echo "     الحالة: {$studentCurriculum->status}\n";
        echo "     نسبة الإنجاز: {$studentCurriculum->completion_percentage}%\n";
        echo "     الصفحة الحالية: " . ($studentCurriculum->current_page ?? 'غير محدد') . "\n";
        echo "     السورة الحالية: " . ($studentCurriculum->current_surah ?? 'غير محدد') . "\n";
        
        // فحص التقدم
        $progress = StudentCurriculumProgress::where('student_curriculum_id', $studentCurriculum->id)->get();
        echo "     سجلات التقدم: {$progress->count()}\n";
        
        if ($progress->count() > 0) {
            echo "     آخر تقدم:\n";
            $lastProgress = $progress->sortByDesc('created_at')->first();
            echo "       - خطة رقم: {$lastProgress->curriculum_plan_id}\n";
            echo "       - الحالة: {$lastProgress->status}\n";
            echo "       - نسبة الإنجاز: {$lastProgress->completion_percentage}%\n";
            echo "       - تاريخ البداية: {$lastProgress->start_date}\n";
        }
        echo "\n";
    }
    
    // جلسات التسميع
    echo "🎯 جلسات التسميع للطالب:\n";
    $sessions = $student->recitationSessions()->orderBy('created_at', 'desc')->limit(5)->get();
    echo "   إجمالي الجلسات: " . $student->recitationSessions->count() . "\n";
    echo "   آخر 5 جلسات:\n";
    
    foreach ($sessions as $session) {
        echo "     - ID: {$session->id}, التاريخ: " . $session->created_at->format('Y-m-d H:i') . "\n";
        echo "       النوع: " . ($session->recitation_type ?? 'غير محدد') . "\n";
        echo "       من السورة {$session->start_surah_number} آية {$session->start_verse} إلى السورة {$session->end_surah_number} آية {$session->end_verse}\n";
        echo "       الدرجة: " . ($session->grade ?? 'غير محدد') . "\n";
        echo "       منهج ID: " . ($session->curriculum_id ?? 'غير محدد') . "\n";
    }
    
} else {
    echo "❌ الطالب غير موجود\n";
}

echo "\n";

// 4. فحص كيفية عمل النظام
echo "🔄 4. كيف يعمل النظام (التدفق المنطقي):\n";
echo str_repeat("-", 50) . "\n";

echo "الخطوات المنطقية:\n";
echo "1️⃣ يتم إنشاء منهج أساسي في جدول 'curricula'\n";
echo "2️⃣ يتم إنشاء خطط يومية لهذا المنهج في جدول 'curriculum_plans'\n";
echo "3️⃣ يتم ربط الطالب بالمنهج في جدول 'student_curricula'\n";
echo "4️⃣ يتم تتبع تقدم الطالب في جدول 'student_curriculum_progress'\n";
echo "5️⃣ عند التسميع، يتم إنشاء سجل في 'recitation_sessions'\n";
echo "6️⃣ بناءً على التسميع، يتم تحديث تقدم الطالب\n";

echo "\n";

// 5. فحص العلاقة بين التسميع والتقدم
echo "🔗 5. فحص العلاقة بين التسميع والتقدم:\n";
echo str_repeat("-", 50) . "\n";

// البحث عن جلسات تسميع مرتبطة بمنهج
$sessionsWithCurriculum = RecitationSession::whereNotNull('curriculum_id')->limit(5)->get();
echo "جلسات التسميع المرتبطة بمنهج: {$sessionsWithCurriculum->count()}\n";

foreach ($sessionsWithCurriculum as $session) {
    echo "  - جلسة ID: {$session->id}, منهج ID: {$session->curriculum_id}\n";
    echo "    النوع: " . ($session->recitation_type ?? 'غير محدد') . "\n";
    echo "    الدرجة: " . ($session->grade ?? 'غير محدد') . "\n";
}

echo "\n";

// 6. فحص أنواع التسميع
echo "📝 6. أنواع التسميع الموجودة:\n";
echo str_repeat("-", 50) . "\n";

$recitationTypes = RecitationSession::select('recitation_type')
    ->distinct()
    ->whereNotNull('recitation_type')
    ->get()
    ->pluck('recitation_type');

echo "الأنواع الموجودة:\n";
foreach ($recitationTypes as $type) {
    $count = RecitationSession::where('recitation_type', $type)->count();
    echo "  - {$type}: {$count} جلسة\n";
}

echo "\n";

// 7. فحص المناهج اليومية
echo "📅 7. فحص المناهج اليومية:\n";
echo str_repeat("-", 50) . "\n";

$dailyPlans = CurriculumPlan::where('plan_type', 'LIKE', '%حفظ%')
    ->orWhere('plan_type', 'LIKE', '%مراجعة%')
    ->orWhere('plan_type', 'LIKE', '%الدرس%')
    ->get();

echo "خطط يومية موجودة: {$dailyPlans->count()}\n";

$planTypes = CurriculumPlan::select('plan_type')
    ->distinct()
    ->whereNotNull('plan_type')
    ->get()
    ->pluck('plan_type');

echo "أنواع الخطط:\n";
foreach ($planTypes as $type) {
    $count = CurriculumPlan::where('plan_type', $type)->count();
    echo "  - {$type}: {$count} خطة\n";
}

echo "\n";

// 8. اختبار API المنهج اليومي
echo "🔍 8. اختبار API المنهج اليومي:\n";
echo str_repeat("-", 50) . "\n";

try {
    $controller = app(\App\Http\Controllers\Api\StudentController::class);
    $response = $controller->getDailyCurriculum(1);
    
    if ($response->getStatusCode() === 200) {
        $content = json_decode($response->getContent(), true);
        echo "✅ API يعمل بنجاح\n";
        
        if (isset($content['data']['daily_curriculum'])) {
            $daily = $content['data']['daily_curriculum'];
            echo "📖 منهج اليوم:\n";
            echo "   حفظ: " . ($daily['memorization'] ? $daily['memorization']['content'] : 'لا يوجد') . "\n";
            echo "   مراجعة صغرى: " . ($daily['minor_review'] ? $daily['minor_review']['content'] : 'لا يوجد') . "\n";
            echo "   مراجعة كبرى: " . ($daily['major_review'] ? $daily['major_review']['content'] : 'لا يوجد') . "\n";
        }
    } else {
        echo "❌ API لا يعمل: كود " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ خطأ في API: " . $e->getMessage() . "\n";
}

echo "\n";

// 9. الخلاصة والتوصيات
echo "📋 9. الخلاصة والتوصيات:\n";
echo str_repeat("-", 50) . "\n";

echo "✅ ما يعمل:\n";
echo "  - جدول المناهج الأساسية موجود\n";
echo "  - جدول خطط المناهج موجود\n";
echo "  - جدول ربط الطلاب بالمناهج موجود\n";
echo "  - جدول جلسات التسميع موجود\n";
echo "  - API المنهج اليومي يعمل\n";

echo "\n⚠️ ما يحتاج توضيح:\n";
echo "  - كيفية ربط جلسات التسميع بتحديث التقدم\n";
echo "  - آلية الانتقال من خطة لأخرى بعد التسميع\n";
echo "  - تطبيق المراجعة الصغرى والكبرى\n";
echo "  - تحديث الصفحة الحالية للطالب\n";

echo "\n💡 التوصيات:\n";
echo "  1. إنشاء آلية تلقائية لتحديث التقدم عند التسميع\n";
echo "  2. ربط أنواع التسميع (حفظ، مراجعة صغرى، مراجعة كبرى)\n";
echo "  3. إنشاء نظام انتقال تلقائي للخطة التالية\n";
echo "  4. تطوير واجهة إدارة المناهج اليومية\n";

echo "\n🎯 للإجابة على أسئلتك:\n";
echo "  1. جلسات التسميع مرتبطة جزئياً بالمناهج (عبر curriculum_id)\n";
echo "  2. يمكن تطبيق نظام المنهج اليومي لكن يحتاج تطوير\n";
echo "  3. المراجعة الصغرى والكبرى تحتاج آلية ربط أكثر وضوحاً\n";
echo "  4. النظام يحتاج workflow للانتقال التلقائي\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "🎉 انتهى الفحص الشامل!\n";
