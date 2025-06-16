<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;

class TestTeacherNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار نظام إشعارات المعلمين الجدد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 اختبار نظام إشعارات المعلمين الجدد');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص إعدادات WhatsApp
        $this->info('1️⃣ فحص إعدادات WhatsApp:');
        $notifyEnabled = WhatsAppSetting::get('notify_teacher_added', 'false');
        $teacherNotifications = WhatsAppSetting::get('teacher_notifications', 'false');
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');

        $this->line("   - notify_teacher_added: {$notifyEnabled}");
        $this->line("   - teacher_notifications: {$teacherNotifications}");
        $this->line("   - API URL: " . ($apiUrl ? 'محدد' : 'غير محدد'));
        $this->line("   - API Token: " . ($apiToken ? 'محدد' : 'غير محدد'));

        // 2. عدد الرسائل قبل الإضافة
        $messagesBefore = WhatsAppMessage::count();
        $this->info("2️⃣ عدد رسائل WhatsApp قبل الإضافة: {$messagesBefore}");

        // 3. الحصول على مسجد للمعلم الجديد
        $mosque = Mosque::first();
        if (!$mosque) {
            $this->warn('❌ لا توجد مساجد في النظام. سأنشئ مسجداً جديداً...');
            $mosque = Mosque::create([
                'name' => 'مسجد الاختبار',
                'neighborhood' => 'حي الاختبار',
                'location_lat' => '24.7136',
                'location_long' => '46.6753',
            ]);
            $this->info("✅ تم إنشاء مسجد جديد: {$mosque->name}");
        }

        // 4. إنشاء معلم جديد
        $this->info('3️⃣ إنشاء معلم جديد...');
        try {
            // توليد كلمة مرور عشوائية
            $randomPassword = Teacher::generateRandomPassword();
            $this->line("   - كلمة المرور المولدة: {$randomPassword}");
            
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
                'password' => $randomPassword, // هذا سيحفظ كلمة المرور المشفرة و plain_password
            ]);
            
            $this->info('✅ تم إنشاء المعلم بنجاح:');
            $this->line("   - ID: {$teacher->id}");
            $this->line("   - الاسم: {$teacher->name}");
            $this->line("   - الهاتف: {$teacher->phone}");
            $this->line("   - المسجد: {$mosque->name}");
            
            // عرض كلمة المرور المولدة إن وجدت
            if (isset($teacher->plain_password)) {
                $this->line("   - كلمة المرور: {$teacher->plain_password}");
            } else {
                $this->warn("   - تحذير: كلمة المرور غير متوفرة في plain_password");
            }
            
            // فحص إضافي للحصول على معلومات المستخدم
            if ($teacher->user) {
                $this->line("   - معرف المستخدم: {$teacher->user->id}");
                $this->line("   - اسم المستخدم: {$teacher->user->name}");
                $this->line("   - البريد الإلكتروني: {$teacher->user->email}");
                $this->line("   - يجب تغيير كلمة المرور: " . ($teacher->user->must_change_password ? 'نعم' : 'لا'));
            } else {
                $this->warn("   - تحذير: لم يتم إنشاء حساب مستخدم للمعلم");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء المعلم: " . $e->getMessage());
            return;
        }

        // 5. انتظار قليل للسماح للـ Observer بالعمل
        $this->info('4️⃣ انتظار معالجة الـ Observer...');
        sleep(2);

        // تشخيص مفصل لنظام القوالب
        $this->info('🔍 تشخيص مفصل لنظام القوالب:');
        
        // فحص القالب في قاعدة البيانات
        $template = \App\Models\WhatsAppTemplate::findByKey('teacher_welcome_with_password');
        if ($template) {
            $this->line("   ✅ تم العثور على القالب في قاعدة البيانات:");
            $this->line("      - المفتاح: {$template->template_key}");
            $this->line("      - الاسم: {$template->template_name}");
            $this->line("      - المحتوى الخام:");
            $this->line("        " . str_replace("\n", "\n        ", $template->content ?? $template->template_content ?? 'غير محدد'));
            $this->line("      - نشط: " . ($template->is_active ? 'نعم' : 'لا'));
            
            // اختبار معالجة القالب
            $testVariables = [
                'teacher_name' => $teacher->name,
                'mosque_name' => $teacher->mosque->name,
                'password' => $teacher->plain_password ?? 'TEST_PASSWORD',
                'identity_number' => $teacher->identity_number
            ];
            
            $processedContent = $template->getProcessedContent($testVariables);
            $this->line("   🧪 اختبار معالجة القالب:");
            $this->line("      - المتغيرات المُمررة:");
            foreach ($testVariables as $key => $value) {
                $this->line("        * {$key}: {$value}");
            }
            $this->line("      - المحتوى بعد المعالجة:");
            $this->line("        " . str_replace("\n", "\n        ", $processedContent));
            
            // فحص إذا كانت كلمة المرور تم استبدالها
            if (str_contains($processedContent, '{password}')) {
                $this->error("      ❌ كلمة المرور لم يتم استبدالها!");
            } else {
                $this->info("      ✅ كلمة المرور تم استبدالها بنجاح!");
            }
        } else {
            $this->warn("   ⚠️ لم يتم العثور على القالب في قاعدة البيانات - سيتم استخدام القالب الثابت");
            
            // اختبار القالب الثابت
            $staticContent = \App\Services\WhatsAppTemplateService::teacherWelcomeWithPasswordMessage(
                $teacher->name,
                $teacher->mosque->name,
                $teacher->plain_password ?? 'TEST_PASSWORD',
                $teacher->identity_number
            );
            $this->line("   📝 القالب الثابت:");
            $this->line("        " . str_replace("\n", "\n        ", $staticContent));
        }

        // فحص خدمة WhatsApp Helper
        $this->line("   🔧 اختبار WhatsApp Helper:");
        $helperResult = \App\Helpers\WhatsAppHelper::sendTeacherWelcomeWithPassword($teacher, $teacher->plain_password);
        $this->line("      - نتيجة الإرسال: " . ($helperResult ? 'نجح' : 'فشل'));

        // 6. فحص الرسائل بعد الإضافة
        $messagesAfter = WhatsAppMessage::count();
        $this->info("5️⃣ عدد رسائل WhatsApp بعد الإضافة: {$messagesAfter}");
        $newMessages = $messagesAfter - $messagesBefore;
        $this->line("   - رسائل جديدة: {$newMessages}");

        // 7. فحص الرسائل الجديدة المرسلة للمعلم
        $teacherMessages = WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->get();

        $this->info('6️⃣ رسائل WhatsApp للمعلم الجديد:');
        if ($teacherMessages->count() > 0) {
            foreach ($teacherMessages as $message) {
                $this->info('   ✅ رسالة موجودة:');
                $this->line("      - ID: {$message->id}");
                $this->line("      - النوع: {$message->message_type}");
                $this->line("      - الحالة: {$message->status}");
                $this->line("      - الهاتف: {$message->phone_number}");
                $this->line("      - المحتوى الكامل:");
                $this->line("        " . str_replace("\n", "\n        ", $message->content));
                $this->line("      - التاريخ: {$message->created_at}");
            }
        } else {
            $this->error('   ❌ لا توجد رسائل للمعلم الجديد');
        }

        // 8. تنظيف البيانات التجريبية
        $this->info('7️⃣ تنظيف البيانات التجريبية...');
        $teacher->delete();
        $this->info('✅ تم حذف المعلم التجريبي');

        $this->info('🏁 انتهى الاختبار!');
    }
}
