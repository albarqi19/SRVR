<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Str;

class WhatsAppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // إعدادات API أساسية
            [
                'setting_key' => 'api_url',
                'setting_value' => '',
                'description' => 'رابط API للواتساب (Twilio أو أي خدمة أخرى)',
                'is_active' => false
            ],
            [
                'setting_key' => 'api_token',
                'setting_value' => '',
                'description' => 'رمز المصادقة لـ API الواتساب',
                'is_active' => false
            ],
            [
                'setting_key' => 'webhook_verify_token',
                'setting_value' => 'whatsapp_webhook_' . Str::random(20),
                'description' => 'رمز التحقق من صحة الويب هوك',
                'is_active' => true
            ],
            
            // إعدادات الإشعارات (مفعلة بشكل افتراضي)
            [
                'setting_key' => 'notifications_enabled',
                'setting_value' => 'true',
                'description' => 'تفعيل إرسال الإشعارات عبر الواتساب',
                'is_active' => true
            ],
            [
                'setting_key' => 'teacher_notifications',
                'setting_value' => 'true',
                'description' => 'إرسال إشعارات للمعلمين',
                'is_active' => true
            ],
            [
                'setting_key' => 'student_notifications',
                'setting_value' => 'true',
                'description' => 'إرسال إشعارات للطلاب',
                'is_active' => true
            ],
            [
                'setting_key' => 'parent_notifications',
                'setting_value' => 'true',
                'description' => 'إرسال إشعارات لأولياء الأمور',
                'is_active' => true
            ],
            
            // أنواع الإشعارات المحددة
            [
                'setting_key' => 'notify_teacher_added',
                'setting_value' => 'true',
                'description' => 'إشعار عند إضافة معلم جديد',
                'is_active' => true
            ],
            [
                'setting_key' => 'notify_student_added',
                'setting_value' => 'true',
                'description' => 'إشعار عند إضافة طالب جديد',
                'is_active' => true
            ],
            [
                'setting_key' => 'notify_attendance_recorded',
                'setting_value' => 'true',
                'description' => 'إشعار عند تسجيل الحضور',
                'is_active' => true
            ],
            [
                'setting_key' => 'notify_absence_recorded',
                'setting_value' => 'true',
                'description' => 'إشعار عند تسجيل الغياب',
                'is_active' => true
            ],
            [
                'setting_key' => 'notify_session_completed',
                'setting_value' => 'true',
                'description' => 'إشعار عند إكمال جلسة تسميع',
                'is_active' => true
            ],
            
            // إعدادات أخرى
            [
                'setting_key' => 'rate_limit_per_minute',
                'setting_value' => '30',
                'description' => 'عدد الرسائل المسموح إرسالها في الدقيقة',
                'is_active' => true
            ],
            [
                'setting_key' => 'retry_failed_messages',
                'setting_value' => 'true',
                'description' => 'إعادة محاولة إرسال الرسائل الفاشلة',
                'is_active' => true
            ],
            [
                'setting_key' => 'max_retry_attempts',
                'setting_value' => '3',
                'description' => 'عدد محاولات إعادة الإرسال للرسائل الفاشلة',
                'is_active' => true
            ]
        ];

        foreach ($settings as $setting) {
            WhatsAppSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }

        $this->command->info('تم إنشاء إعدادات الواتساب الأساسية بنجاح!');
    }
}
