<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\QuranCircle;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;

class TestStudentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:student-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار نظام إشعارات الطلاب الجدد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 اختبار نظام إشعارات الطلاب الجدد');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص إعدادات WhatsApp
        $this->info('1️⃣ فحص إعدادات WhatsApp:');
        $notifyEnabled = WhatsAppSetting::get('notify_student_added', 'false');
        $studentNotifications = WhatsAppSetting::get('student_notifications', 'false');
        $parentNotifications = WhatsAppSetting::get('parent_notifications', 'false');
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');

        $this->line("   - notify_student_added: {$notifyEnabled}");
        $this->line("   - student_notifications: {$studentNotifications}");
        $this->line("   - parent_notifications: {$parentNotifications}");
        $this->line("   - API URL: " . ($apiUrl ? 'محدد' : 'غير محدد'));
        $this->line("   - API Token: " . ($apiToken ? 'محدد' : 'غير محدد'));

        // 2. عدد الرسائل قبل الإضافة
        $messagesBefore = WhatsAppMessage::count();
        $this->info("2️⃣ عدد رسائل WhatsApp قبل الإضافة: {$messagesBefore}");

        // 3. الحصول على حلقة للطالب الجديد
        $circle = QuranCircle::first();
        if (!$circle) {
            $this->warn('❌ لا توجد حلقات قرآنية في النظام. سأنشئ حلقة جديدة...');
            $circle = QuranCircle::create([
                'name' => 'حلقة الاختبار',
                'period' => 'العصر',
                'capacity' => 15,
                'current_students' => 0,
                'is_active' => true,
            ]);
            $this->info("✅ تم إنشاء حلقة جديدة: {$circle->name}");
        }

        // 4. إنشاء طالب جديد
        $this->info('3️⃣ إنشاء طالب جديد...');
        try {
            // توليد كلمة مرور عشوائية
            $randomPassword = Student::generateRandomPassword();
            $this->line("   - كلمة المرور المولدة: {$randomPassword}");
            
            $student = Student::create([
                'identity_number' => '9876543210',
                'name' => 'محمد أحمد الاختبار',
                'nationality' => 'سعودي',
                'birth_date' => '2010-01-15',
                'phone' => '0530996778', // رقم هاتف صحيح للاختبار
                'guardian_name' => 'أحمد محمد الاختبار (ولي الأمر)',
                'guardian_phone' => '0530996779', // رقم هاتف ولي الأمر
                'education_level' => 'المرحلة المتوسطة',
                'quran_circle_id' => $circle->id,
                'enrollment_date' => now(),
                'is_active' => true,
                'is_active_user' => true,
                'must_change_password' => true,
                'password' => $randomPassword, // هذا سيحفظ كلمة المرور المشفرة و plain_password
            ]);
            
            $this->info('✅ تم إنشاء الطالب بنجاح:');
            $this->line("   - ID: {$student->id}");
            $this->line("   - الاسم: {$student->name}");
            $this->line("   - الهاتف: {$student->phone}");
            $this->line("   - هاتف ولي الأمر: {$student->guardian_phone}");
            $this->line("   - الحلقة: {$circle->name}");
            
            // عرض كلمة المرور المولدة إن وجدت
            if (isset($student->plain_password)) {
                $this->line("   - كلمة المرور: {$student->plain_password}");
            } else {
                $this->warn("   - تحذير: كلمة المرور غير متوفرة في plain_password");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء الطالب: " . $e->getMessage());
            return;
        }

        // 5. انتظار قليل للسماح للـ Observer بالعمل
        $this->info('4️⃣ انتظار معالجة الـ Observer...');
        sleep(2);

        // تشخيص مفصل لنظام القوالب
        $this->info('🔍 تشخيص مفصل لنظام القوالب:');
        
        // فحص قالب ترحيب الطالب
        $studentTemplate = \App\Models\WhatsAppTemplate::findByKey('student_welcome');
        if ($studentTemplate) {
            $this->line("   ✅ تم العثور على قالب ترحيب الطالب:");
            $this->line("      - المفتاح: {$studentTemplate->template_key}");
            $this->line("      - الاسم: {$studentTemplate->template_name}");
            $this->line("      - المحتوى الخام:");
            $this->line("        " . str_replace("\n", "\n        ", $studentTemplate->content ?? $studentTemplate->template_content ?? 'غير محدد'));
            $this->line("      - نشط: " . ($studentTemplate->is_active ? 'نعم' : 'لا'));
            
            // اختبار معالجة قالب الطالب
            $testVariables = [
                'student_name' => $student->name,
                'circle_name' => $student->quranCircle->name ?? 'غير محدد',
                'password' => $student->plain_password ?? 'TEST_PASSWORD',
                'identity_number' => $student->identity_number
            ];
            
            $processedContent = $studentTemplate->getProcessedContent($testVariables);
            $this->line("   🧪 اختبار معالجة قالب ترحيب الطالب:");
            $this->line("      - المتغيرات المُمررة:");
            foreach ($testVariables as $key => $value) {
                $this->line("        * {$key}: {$value}");
            }
            $this->line("      - المحتوى بعد المعالجة:");
            $this->line("        " . str_replace("\n", "\n        ", $processedContent));
        } else {
            $this->warn("   ⚠️ لم يتم العثور على قالب ترحيب الطالب في قاعدة البيانات");
        }

        // فحص قالب إشعار ولي الأمر
        $parentTemplate = \App\Models\WhatsAppTemplate::findByKey('parent_notification');
        if ($parentTemplate) {
            $this->line("   ✅ تم العثور على قالب إشعار ولي الأمر:");
            $this->line("      - المفتاح: {$parentTemplate->template_key}");
            $this->line("      - الاسم: {$parentTemplate->template_name}");
            $this->line("      - المحتوى الخام:");
            $this->line("        " . str_replace("\n", "\n        ", $parentTemplate->content ?? $parentTemplate->template_content ?? 'غير محدد'));
            $this->line("      - نشط: " . ($parentTemplate->is_active ? 'نعم' : 'لا'));
            
            // اختبار معالجة قالب ولي الأمر
            $parentVariables = [
                'student_name' => $student->name,
                'guardian_name' => $student->guardian_name,
                'circle_name' => $student->quranCircle->name ?? 'غير محدد',
                'enrollment_date' => $student->enrollment_date ? $student->enrollment_date->format('Y-m-d') : now()->format('Y-m-d')
            ];
            
            $processedParentContent = $parentTemplate->getProcessedContent($parentVariables);
            $this->line("   🧪 اختبار معالجة قالب إشعار ولي الأمر:");
            $this->line("      - المتغيرات المُمررة:");
            foreach ($parentVariables as $key => $value) {
                $this->line("        * {$key}: {$value}");
            }
            $this->line("      - المحتوى بعد المعالجة:");
            $this->line("        " . str_replace("\n", "\n        ", $processedParentContent));
        } else {
            $this->warn("   ⚠️ لم يتم العثور على قالب إشعار ولي الأمر في قاعدة البيانات");
        }        // فحص خدمة WhatsApp Helper
        $this->line("   🔧 اختبار WhatsApp Helper:");
        
        // ملاحظة: لا نحتاج لاختبار WhatsAppHelper لأن StudentObserver يرسل الرسائل مباشرة
        $this->line("      - StudentObserver يرسل الرسائل تلقائياً عند إنشاء الطالب");

        // 6. فحص الرسائل بعد الإضافة
        $messagesAfter = WhatsAppMessage::count();
        $this->info("5️⃣ عدد رسائل WhatsApp بعد الإضافة: {$messagesAfter}");
        $newMessages = $messagesAfter - $messagesBefore;
        $this->line("   - رسائل جديدة: {$newMessages}");

        // 7. فحص الرسائل الجديدة المرسلة للطالب
        $studentMessages = WhatsAppMessage::where('user_type', 'student')
            ->where('user_id', $student->id)
            ->get();

        $this->info('6️⃣ رسائل WhatsApp للطالب الجديد:');
        if ($studentMessages->count() > 0) {
            foreach ($studentMessages as $message) {
                $this->info('   ✅ رسالة موجودة للطالب:');
                $this->line("      - ID: {$message->id}");
                $this->line("      - النوع: {$message->message_type}");
                $this->line("      - الحالة: {$message->status}");
                $this->line("      - الهاتف: {$message->phone_number}");
                $this->line("      - المحتوى الكامل:");
                $this->line("        " . str_replace("\n", "\n        ", $message->content));
                $this->line("      - التاريخ: {$message->created_at}");
            }
        } else {
            $this->warn('   ⚠️ لا توجد رسائل للطالب الجديد');
        }

        // 8. فحص الرسائل المرسلة لولي الأمر
        $guardianMessages = WhatsAppMessage::where('phone_number', 'LIKE', '%' . substr($student->guardian_phone, -10) . '%')
            ->orWhere('phone_number', '=', $student->guardian_phone)
            ->orWhere('phone_number', '=', '+966' . substr($student->guardian_phone, 1))
            ->get();

        $this->info('7️⃣ رسائل WhatsApp لولي الأمر:');
        if ($guardianMessages->count() > 0) {
            foreach ($guardianMessages as $message) {
                $this->info('   ✅ رسالة موجودة لولي الأمر:');
                $this->line("      - ID: {$message->id}");
                $this->line("      - النوع: {$message->message_type}");
                $this->line("      - الحالة: {$message->status}");
                $this->line("      - الهاتف: {$message->phone_number}");
                $this->line("      - المحتوى الكامل:");
                $this->line("        " . str_replace("\n", "\n        ", $message->content));
                $this->line("      - التاريخ: {$message->created_at}");
            }
        } else {
            $this->warn('   ⚠️ لا توجد رسائل لولي الأمر');
        }

        // 9. تشخيص حالة الرسائل
        $this->info('8️⃣ تحليل حالة الرسائل:');
        $pendingMessages = WhatsAppMessage::where('status', 'pending')->count();
        $sentMessages = WhatsAppMessage::where('status', 'sent')->count();
        $failedMessages = WhatsAppMessage::where('status', 'failed')->count();
        
        $this->line("   - رسائل في الانتظار (pending): {$pendingMessages}");
        $this->line("   - رسائل مرسلة (sent): {$sentMessages}");
        $this->line("   - رسائل فاشلة (failed): {$failedMessages}");
          if ($pendingMessages > 0) {
            $this->warn("   ⚠️ يوجد {$pendingMessages} رسائل في الانتظار - هذا يدل على أن الرسائل لا تُرسل مباشرة!");
            
            // تشخيص مفصل للمشكلة
            $this->info('🔧 تشخيص مفصل للمشكلة:');
            
            // فحص إعدادات API
            $apiConfig = \App\Models\WhatsAppSetting::getApiConfig();
            $this->line("   📡 إعدادات WhatsApp API:");
            $this->line("      - URL: " . ($apiConfig['url'] ?? 'غير محدد'));
            $this->line("      - Token: " . (isset($apiConfig['token']) && !empty($apiConfig['token']) ? 'محدد (' . strlen($apiConfig['token']) . ' أحرف)' : 'غير محدد'));
            
            // اختبار الاتصال بـ API
            $this->line("   🌐 اختبار الاتصال بـ WhatsApp API:");
            try {
                if (!empty($apiConfig['url'])) {
                    $response = \Illuminate\Support\Facades\Http::timeout(10)->get($apiConfig['url']);
                    $this->line("      - حالة الاستجابة: " . $response->status());
                    if ($response->successful()) {
                        $this->info("      ✅ API متاح ويستجيب");
                    } else {
                        $this->warn("      ⚠️ API يستجيب لكن بحالة خطأ: " . $response->status());
                        $this->line("      - محتوى الاستجابة: " . substr($response->body(), 0, 200));
                    }
                } else {
                    $this->error("      ❌ URL غير محدد");
                }
            } catch (\Exception $e) {
                $this->error("      ❌ فشل في الاتصال: " . $e->getMessage());
            }
            
            // فحص الـ Queue Jobs
            $this->line("   ⚙️ فحص الـ Queue Jobs:");
            $queueJobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $this->line("      - عدد المهام في الانتظار: {$queueJobs}");
            
            if ($queueJobs > 0) {
                $this->line("   🔍 تفاصيل المهام في الانتظار:");
                $jobs = \Illuminate\Support\Facades\DB::table('jobs')
                    ->select('id', 'queue', 'payload', 'attempts', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
                
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $this->line("      - المهمة #{$job->id}:");
                    $this->line("        * Queue: {$job->queue}");
                    $this->line("        * Class: " . ($payload['displayName'] ?? 'غير محدد'));
                    $this->line("        * المحاولات: {$job->attempts}");
                    $this->line("        * تاريخ الإنشاء: {$job->created_at}");
                }
                
                // محاولة تشغيل مهمة واحدة مع تسجيل مفصل
                $this->line("   🚀 محاولة تشغيل مهمة واحدة:");
                try {
                    // أخذ أول مهمة وتشغيلها يدوياً
                    $firstJob = \Illuminate\Support\Facades\DB::table('jobs')->first();
                    if ($firstJob) {
                        $this->line("      - تشغيل المهمة #{$firstJob->id}...");
                        
                        // تشغيل المهمة
                        $result = \Illuminate\Support\Facades\Artisan::call('queue:work', [
                            '--once' => true,
                            '--tries' => 1,
                            '--timeout' => 30
                        ]);
                        
                        $this->line("      - كود نتيجة التشغيل: {$result}");
                        
                        // فحص الرسائل الفاشلة
                        $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
                        $this->line("      - المهام الفاشلة: {$failedJobs}");
                        
                        if ($failedJobs > 0) {
                            $lastFailedJob = \Illuminate\Support\Facades\DB::table('failed_jobs')
                                ->orderBy('failed_at', 'desc')
                                ->first();
                            
                            if ($lastFailedJob) {
                                $this->error("      ❌ تفاصيل آخر مهمة فاشلة:");
                                $this->line("        * الاستثناء: " . substr($lastFailedJob->exception, 0, 200) . '...');
                            }
                        }
                        
                        // فحص إذا تم تحديث الرسائل
                        $updatedPendingMessages = WhatsAppMessage::where('status', 'pending')->count();
                        $this->line("      - الرسائل المُعلقة بعد التشغيل: {$updatedPendingMessages}");
                        
                        if ($updatedPendingMessages < $pendingMessages) {
                            $this->info("      ✅ تم معالجة " . ($pendingMessages - $updatedPendingMessages) . " رسالة");
                        } else {
                            $this->warn("      ⚠️ لم يتم معالجة أي رسالة - المهمة فشلت");
                        }
                    }
                    
                } catch (\Exception $e) {
                    $this->error("      ❌ خطأ في تشغيل Queue: " . $e->getMessage());
                }
            }
        }

        // 10. تنظيف البيانات التجريبية
        $this->info('9️⃣ تنظيف البيانات التجريبية...');
        $student->delete();
        $this->info('✅ تم حذف الطالب التجريبي');

        $this->info('🏁 انتهى الاختبار!');
        
        // 11. ملخص النتائج والتوصيات
        $this->info('📋 ملخص النتائج:');
        if ($pendingMessages > 0) {
            $this->error('❌ المشكلة الرئيسية: الرسائل تُضاف بحالة "pending" ولا تُرسل مباشرة');
            $this->line('💡 التوصيات:');
            $this->line('   1. تحقق من إعدادات queue في ملف .env');
            $this->line('   2. تأكد من تشغيل queue worker: php artisan queue:work');
            $this->line('   3. تحقق من إعدادات WhatsApp API');
            $this->line('   4. راجع ملفات الـ jobs للتأكد من صحة معالجة الرسائل');
        } else {
            $this->info('✅ النظام يعمل بشكل صحيح - الرسائل تُرسل مباشرة');
        }
    }
}
