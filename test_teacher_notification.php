<?php

require_once 'vendor/autoload.php';

use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 اختبار نظام إشعارات المعلمين الجدد\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. فحص إعدادات WhatsApp
echo "1️⃣ فحص إعدادات WhatsApp:\n";
$notifyEnabled = WhatsAppSetting::get('notify_teacher_added', 'false');
$teacherNotifications = WhatsAppSetting::get('teacher_notifications', 'false');
$apiUrl = WhatsAppSetting::get('api_url');
$apiToken = WhatsAppSetting::get('api_token');

echo "   - notify_teacher_added: {$notifyEnabled}\n";
echo "   - teacher_notifications: {$teacherNotifications}\n";
echo "   - API URL: " . ($apiUrl ? 'محدد' : 'غير محدد') . "\n";
echo "   - API Token: " . ($apiToken ? 'محدد' : 'غير محدد') . "\n\n";

// 2. عدد الرسائل قبل الإضافة
$messagesBefore = WhatsAppMessage::count();
echo "2️⃣ عدد رسائل WhatsApp قبل الإضافة: {$messagesBefore}\n\n";

// 3. الحصول على مسجد للمعلم الجديد
$mosque = Mosque::first();
if (!$mosque) {
    echo "❌ لا توجد مساجد في النظام. سأنشئ مسجداً جديداً...\n";
    $mosque = Mosque::create([
        'name' => 'مسجد الاختبار',
        'neighborhood' => 'حي الاختبار',
        'location_lat' => '24.7136',
        'location_long' => '46.6753',
    ]);
    echo "✅ تم إنشاء مسجد جديد: {$mosque->name}\n\n";
}

// 4. إنشاء معلم جديد
echo "3️⃣ إنشاء معلم جديد...\n";
try {
    $teacher = Teacher::create([
        'identity_number' => '1234567890',
        'name' => 'أحمد محمد الاختبار',
        'nationality' => 'سعودي',
        'phone' => '0530996778', // رقم هاتف صحيح للاختبار
        'mosque_id' => $mosque->id,
        'job_title' => 'معلم حفظ',
        'task_type' => 'معلم بمكافأة',
        'circle_type' => 'حلقة فردية',
        'work_time' => 'عصر',
        'is_active_user' => true,
        'must_change_password' => true,
    ]);
    
    echo "✅ تم إنشاء المعلم بنجاح:\n";
    echo "   - ID: {$teacher->id}\n";
    echo "   - الاسم: {$teacher->name}\n";
    echo "   - الهاتف: {$teacher->phone}\n";
    echo "   - المسجد: {$mosque->name}\n\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء المعلم: " . $e->getMessage() . "\n\n";
    exit;
}

// 5. انتظار قليل للسماح للـ Observer بالعمل
echo "4️⃣ انتظار معالجة الـ Observer...\n";
sleep(2);

// 6. فحص الرسائل بعد الإضافة
$messagesAfter = WhatsAppMessage::count();
echo "5️⃣ عدد رسائل WhatsApp بعد الإضافة: {$messagesAfter}\n";
$newMessages = $messagesAfter - $messagesBefore;
echo "   - رسائل جديدة: {$newMessages}\n\n";

// 7. فحص الرسائل الجديدة المرسلة للمعلم
$teacherMessages = WhatsAppMessage::where('user_type', 'teacher')
    ->where('user_id', $teacher->id)
    ->get();

echo "6️⃣ رسائل WhatsApp للمعلم الجديد:\n";
if ($teacherMessages->count() > 0) {
    foreach ($teacherMessages as $message) {
        echo "   ✅ رسالة موجودة:\n";
        echo "      - ID: {$message->id}\n";
        echo "      - النوع: {$message->message_type}\n";
        echo "      - الحالة: {$message->status}\n";
        echo "      - الهاتف: {$message->phone_number}\n";
        echo "      - المحتوى: " . substr($message->content, 0, 100) . "...\n";
        echo "      - التاريخ: {$message->created_at}\n\n";
    }
} else {
    echo "   ❌ لا توجد رسائل للمعلم الجديد\n\n";
}

// 8. تنظيف البيانات التجريبية
echo "7️⃣ تنظيف البيانات التجريبية...\n";
$teacher->delete();
echo "✅ تم حذف المعلم التجريبي\n\n";

echo "🏁 انتهى الاختبار!\n";
