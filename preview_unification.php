<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 معاينة وضع المعرفات قبل التوحيد\n";
echo str_repeat("=", 60) . "\n\n";

// عرض حالة عبدالله الشنقيطي
echo "1️⃣ حالة عبدالله الشنقيطي:\n";
$abdullah = Teacher::where('name', 'like', '%عبدالله الشنقيطي%')->first();

if ($abdullah) {
    echo "   📋 الوضع الحالي:\n";
    echo "      - Teacher ID: {$abdullah->id}\n";
    echo "      - User ID: {$abdullah->user_id}\n";
    echo "      - اسم المعلم: {$abdullah->name}\n";
    
    if ($abdullah->user) {
        echo "      - اسم المستخدم: {$abdullah->user->name}\n";
        echo "      - بريد المستخدم: {$abdullah->user->email}\n";
    }
    
    echo "\n   🎯 بعد التوحيد سيصبح:\n";
    echo "      - Teacher ID: {$abdullah->id}\n";
    echo "      - User ID: {$abdullah->id} (نفس teacher_id)\n";
    echo "      - Frontend يرسل: {$abdullah->id}\n";
    echo "      - API يستقبل: {$abdullah->id}\n";
    echo "      - النتيجة: {$abdullah->name} ✅\n";
}

echo "\n";

// عرض جدول المقارنة
echo "2️⃣ جدول المقارنة للمعلمين الأوائل:\n";
$teachers = Teacher::whereNotNull('user_id')->with('user')->limit(5)->get();

echo "+--------+------------------------+----------+----------+\n";
echo "| الاسم   | Teacher ID             | User ID  | سيصبح    |\n";
echo "+--------+------------------------+----------+----------+\n";

foreach ($teachers as $teacher) {
    $name = substr($teacher->name, 0, 20);
    echo sprintf("| %-20s | %-10s | %-8s | %-8s |\n", 
        $name, 
        $teacher->id, 
        $teacher->user_id, 
        $teacher->id
    );
}
echo "+--------+------------------------+----------+----------+\n";

echo "\n";

// عرض الفوائد
echo "3️⃣ فوائد التوحيد:\n";
echo "   ✅ بساطة: معرف واحد لكل شخص\n";
echo "   ✅ وضوح: لا خلط بين المعرفات\n";
echo "   ✅ أمان: تطابق تام بين Frontend و Backend\n";
echo "   ✅ سهولة: لا حاجة لدوال تحويل معقدة\n";
echo "   ✅ مستقبلي: يحل المشكلة نهائياً\n";

echo "\n";

echo "4️⃣ كيفية عمل النظام بعد التوحيد:\n";
echo "   📤 Frontend:\n";
echo "      const sessionData = {\n";
echo "        teacher_id: user?.id,  // نفس المعرف\n";
echo "        // ... باقي البيانات\n";
echo "      };\n\n";
echo "   📥 Backend:\n";
echo "      \$teacher = Teacher::find(\$request->teacher_id);\n";
echo "      // سيجد المعلم مباشرة بدون تعقيدات\n";

echo "\n";

echo "5️⃣ خطوات التنفيذ:\n";
echo "   1. تشغيل: php artisan unify:teacher-user-ids\n";
echo "   2. تحديث Frontend ليستخدم المعرف الموحد\n";
echo "   3. اختبار النتيجة\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "جاهز للتوحيد؟ 🚀\n";
