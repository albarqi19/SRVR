<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\WhatsAppSetting;

echo "🔧 إعداد إعدادات WhatsApp...\n\n";

try {
    // إعداد رابط API
    WhatsAppSetting::set('api_url', 'http://localhost:3000/api/webhook/GuAl6n67NJGLeJ9NgomDVCL0uemnfveS');
    echo "✅ تم حفظ رابط API\n";

    // إعداد رمز API
    WhatsAppSetting::set('api_token', 'test_token');
    echo "✅ تم حفظ رمز API\n";

    // تفعيل الإشعارات
    WhatsAppSetting::set('notifications_enabled', 'true');
    echo "✅ تم تفعيل الإشعارات\n";

    // إعدادات إضافية
    WhatsAppSetting::set('teacher_notifications', 'true');
    WhatsAppSetting::set('student_notifications', 'true');
    WhatsAppSetting::set('attendance_notifications', 'true');
    echo "✅ تم حفظ جميع إعدادات الإشعارات\n";

    echo "\n🎉 تم حفظ جميع الإعدادات بنجاح!\n\n";

    // التحقق من الإعدادات
    echo "📋 الإعدادات المحفوظة:\n";
    echo "- API URL: " . WhatsAppSetting::get('api_url') . "\n";
    echo "- API Token: " . WhatsAppSetting::get('api_token') . "\n";
    echo "- Notifications: " . WhatsAppSetting::get('notifications_enabled') . "\n";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}

echo "\n✨ انتهى الإعداد!\n";
