<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppSetting;

class FixWhatsAppSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:whatsapp-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إصلاح وإعداد إعدادات WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 إصلاح إعدادات WhatsApp');
        $this->info('=' . str_repeat('=', 40));

        // قائمة الإعدادات المطلوبة مع القيم الافتراضية
        $defaultSettings = [
            'notify_teacher_added' => 'true',
            'teacher_notifications' => 'true',
            'student_notifications' => 'true',
            'api_url' => 'https://api.whatsapp.local/send',
            'api_token' => 'dummy_token_12345',
            'notifications_enabled' => 'true',
            'send_welcome_messages' => 'true',
            'send_attendance_confirmations' => 'true',
            'phone_format' => '+966',
            'timeout' => '10',
            'retry_attempts' => '3',
            'queue_enabled' => 'false',
            'test_mode' => 'true',
            'log_messages' => 'true',
            'auto_format_phone' => 'true',
            'webhook_url' => '',
        ];

        $this->info('1️⃣ فحص الإعدادات الحالية:');
        $existingSettings = WhatsAppSetting::pluck('value', 'key')->toArray();
        
        if (empty($existingSettings)) {
            $this->warn('   ❌ لا توجد إعدادات WhatsApp');
        } else {
            $this->info('   ✅ يوجد ' . count($existingSettings) . ' إعداد');
        }

        $this->info('2️⃣ إنشاء/تحديث الإعدادات:');
        $created = 0;
        $updated = 0;

        foreach ($defaultSettings as $key => $defaultValue) {
            $setting = WhatsAppSetting::firstOrNew(['key' => $key]);
            
            if (!$setting->exists) {
                $setting->value = $defaultValue;
                $setting->description = $this->getSettingDescription($key);
                $setting->save();
                $created++;
                $this->line("   ✅ تم إنشاء: {$key} = {$defaultValue}");
            } else {
                if (empty($setting->value)) {
                    $setting->value = $defaultValue;
                    $setting->save();
                    $updated++;
                    $this->line("   🔄 تم تحديث: {$key} = {$defaultValue}");
                } else {
                    $this->line("   ⏭️  موجود: {$key} = {$setting->value}");
                }
            }
        }

        $this->info('3️⃣ ملخص العملية:');
        $this->line("   - إعدادات جديدة: {$created}");
        $this->line("   - إعدادات محدثة: {$updated}");
        $this->line("   - إجمالي الإعدادات: " . WhatsAppSetting::count());

        $this->info('4️⃣ اختبار الإعدادات:');
        
        // اختبار الدوال الأساسية
        $notificationsEnabled = WhatsAppSetting::notificationsEnabled();
        $teacherNotificationsEnabled = WhatsAppSetting::isNotificationEnabled('notify_teacher_added');
        
        $this->line("   - الإشعارات مفعلة: " . ($notificationsEnabled ? 'نعم ✅' : 'لا ❌'));
        $this->line("   - إشعارات المعلمين مفعلة: " . ($teacherNotificationsEnabled ? 'نعم ✅' : 'لا ❌'));
        
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        
        $this->line("   - API URL: " . ($apiUrl ? '✅ محدد' : '❌ غير محدد'));
        $this->line("   - API Token: " . ($apiToken ? '✅ محدد' : '❌ غير محدد'));

        if ($notificationsEnabled && $teacherNotificationsEnabled && $apiUrl && $apiToken) {
            $this->info('🎉 جميع الإعدادات صحيحة! يمكن الآن اختبار النظام.');
        } else {
            $this->warn('⚠️ بعض الإعدادات مفقودة. تأكد من الإعدادات أعلاه.');
        }

        $this->info('🏁 انتهى إصلاح الإعدادات!');
    }

    /**
     * Get description for setting key
     */
    private function getSettingDescription(string $key): string
    {
        return match ($key) {
            'notify_teacher_added' => 'إرسال إشعار عند إضافة معلم جديد',
            'teacher_notifications' => 'تفعيل إشعارات المعلمين',
            'student_notifications' => 'تفعيل إشعارات الطلاب',
            'api_url' => 'رابط API لـ WhatsApp',
            'api_token' => 'رمز التوثيق لـ API',
            'notifications_enabled' => 'تفعيل النظام بالكامل',
            'send_welcome_messages' => 'إرسال رسائل الترحيب',
            'send_attendance_confirmations' => 'إرسال تأكيدات الحضور',
            'phone_format' => 'تنسيق أرقام الهاتف',
            'timeout' => 'مهلة انتظار الطلبات (ثانية)',
            'retry_attempts' => 'عدد محاولات الإعادة',
            'queue_enabled' => 'تفعيل نظام الطوابير',
            'test_mode' => 'وضع التجريب',
            'log_messages' => 'تسجيل الرسائل في اللوج',
            'auto_format_phone' => 'تنسيق أرقام الهاتف تلقائياً',
            'webhook_url' => 'رابط Webhook للتحديثات',
            default => 'إعداد WhatsApp'
        };
    }
}
