<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 اختبار حل مشكلة معرف المعلم في API\n";
echo str_repeat("=", 60) . "\n\n";

// محاكاة دالة resolveTeacherId
function resolveTeacherId($inputId): array
{
    // أولاً: التحقق إذا كان المعرف موجود في جدول teachers مباشرة
    $directTeacher = Teacher::find($inputId);
    if ($directTeacher) {
        return [
            'teacher_id' => $directTeacher->id,
            'user_id' => $directTeacher->user_id ?? $inputId,
            'teacher_name' => $directTeacher->name,
            'method' => 'direct_teacher_lookup'
        ];
    }
    
    // ثانياً: البحث عن معلم بـ user_id
    $teacherByUserId = Teacher::where('user_id', $inputId)->first();
    if ($teacherByUserId) {
        return [
            'teacher_id' => $teacherByUserId->id,
            'user_id' => $inputId,
            'teacher_name' => $teacherByUserId->name,
            'method' => 'user_id_lookup'
        ];
    }
    
    // ثالثاً: التحقق من وجود المعرف في جدول users
    $user = User::find($inputId);
    if ($user) {
        return [
            'teacher_id' => null,
            'user_id' => $inputId,
            'teacher_name' => $user->name,
            'method' => 'user_only',
            'error' => 'المستخدم موجود لكن لا يوجد معلم مرتبط به'
        ];
    }
    
    return [
        'teacher_id' => null,
        'user_id' => null,
        'teacher_name' => null,
        'method' => 'not_found',
        'error' => 'المعرف غير موجود'
    ];
}

// اختبار حالات مختلفة
$testCases = [
    [
        'name' => 'إرسال teacher_id مباشر (عبدالله الشنقيطي)',
        'input' => 89,
        'expected' => 'عبدالله الشنقيطي'
    ],
    [
        'name' => 'إرسال user_id (عبدالله الشنقيطي)',
        'input' => 34,
        'expected' => 'عبدالله الشنقيطي'
    ],
    [
        'name' => 'إرسال معرف غير موجود',
        'input' => 999,
        'expected' => 'خطأ'
    ]
];

foreach ($testCases as $index => $testCase) {
    echo ($index + 1) . "️⃣ " . $testCase['name'] . ":\n";
    echo "   Input: {$testCase['input']}\n";
    
    $result = resolveTeacherId($testCase['input']);
    
    echo "   Result: " . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
    $success = ($testCase['expected'] === 'خطأ' && isset($result['error'])) ||
               ($testCase['expected'] !== 'خطأ' && $result['teacher_name'] === $testCase['expected']);
    
    echo "   Status: " . ($success ? '✅ نجح' : '❌ فشل') . "\n\n";
}

// اختبار السيناريو الحقيقي
echo "🎯 اختبار السيناريو الحقيقي:\n";
echo "   Frontend يرسل: user_id = 34 (عبدالله الشنقيطي)\n";

$realScenario = resolveTeacherId(34);
echo "   النتيجة المتوقعة:\n";
echo "     - المعلم: عبدالله الشنقيطي\n";
echo "     - teacher_id للحفظ في DB: {$realScenario['user_id']}\n";
echo "     - طريقة الحل: {$realScenario['method']}\n";

if ($realScenario['teacher_name'] === 'عبدالله الشنقيطي') {
    echo "   ✅ تم حل المشكلة بنجاح!\n";
} else {
    echo "   ❌ المشكلة لم تُحل\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "انتهى الاختبار\n";
