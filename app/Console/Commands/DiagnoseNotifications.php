<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Services\WhatsAppService;
use App\Observers\AttendanceObserver;
use App\Observers\StudentObserver;
use App\Observers\TeacherObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DiagnoseNotifications extends Command
{
    protected $signature = 'notifications:diagnose {--test : تشغيل اختبارات تفاعلية}';
    protected $description = 'تشخيص شامل لجميع أنواع الإشعارات والرسائل';

    public function handle()
    {
        $this->info('🔍 بدء التشخيص الشامل لنظام الإشعارات');
        $this->info(str_repeat('=', 60));

        // 1. فحص الإعدادات العامة
        $this->checkGeneralSettings();

        // 2. فحص الـ Observers
        $this->checkObservers();

        // 3. فحص قاعدة البيانات
        $this->checkDatabaseStructure();

        // 4. تحليل الرسائل الموجودة
        $this->analyzeExistingMessages();

        // 5. اختبار كل نوع إشعار
        if ($this->option('test')) {
            $this->testNotificationTypes();
        }

        // 6. التوصيات
        $this->showRecommendations();

        $this->info('✅ انتهى التشخيص');
    }

    private function checkGeneralSettings()
    {
        $this->info("\n📋 1. فحص الإعدادات العامة:");
        
        // فحص إعدادات الواتساب
        $notificationsEnabled = WhatsAppSetting::notificationsEnabled();
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        
        $this->line("   - الإشعارات مفعلة: " . ($notificationsEnabled ? '✅ نعم' : '❌ لا'));
        $this->line("   - رابط API: " . ($apiUrl ? '✅ موجود' : '❌ غير موجود'));
        $this->line("   - رمز API: " . ($apiToken ? '✅ موجود' : '❌ غير موجود'));
        
        // فحص إعدادات الإشعارات المحددة
        $settings = [
            'notify_student_added' => 'إشعار إضافة طالب',
            'notify_teacher_added' => 'إشعار إضافة معلم',
            'notify_attendance' => 'إشعار الحضور/الغياب',
            'notify_session_completion' => 'إشعار انتهاء الجلسة',
            'notify_teacher_assignment' => 'إشعار تكليف معلم'
        ];
        
        foreach ($settings as $key => $name) {
            $enabled = WhatsAppSetting::isNotificationEnabled($key);
            $this->line("   - {$name}: " . ($enabled ? '✅ مفعل' : '❌ غير مفعل'));
        }
    }

    private function checkObservers()
    {
        $this->info("\n🔍 2. فحص الـ Observers:");
        
        // فحص تسجيل الـ Observers
        $observers = [
            'Student' => \App\Models\Student::class,
            'Teacher' => \App\Models\Teacher::class,
            'Attendance' => \App\Models\Attendance::class,
        ];
        
        foreach ($observers as $name => $modelClass) {
            $observerClass = "App\\Observers\\{$name}Observer";
            
            if (class_exists($observerClass)) {
                $this->line("   - {$name}Observer: ✅ موجود");
                
                // فحص الطرق المطلوبة
                $reflection = new \ReflectionClass($observerClass);
                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                $methodNames = array_map(fn($method) => $method->getName(), $methods);
                
                $requiredMethods = ['created', 'updated'];
                foreach ($requiredMethods as $method) {
                    $exists = in_array($method, $methodNames);
                    $this->line("     - طريقة {$method}: " . ($exists ? '✅' : '❌'));
                }
            } else {
                $this->line("   - {$name}Observer: ❌ غير موجود");
            }
        }
    }

    private function checkDatabaseStructure()
    {
        $this->info("\n🗄️ 3. فحص قاعدة البيانات:");
        
        // فحص جدول whatsapp_messages
        if (Schema::hasTable('whatsapp_messages')) {
            $this->line("   - جدول whatsapp_messages: ✅ موجود");
            
            $columns = Schema::getColumnListing('whatsapp_messages');
            $requiredColumns = ['user_type', 'user_id', 'phone_number', 'message_type', 'content', 'status'];
            
            foreach ($requiredColumns as $column) {
                $exists = in_array($column, $columns);
                $this->line("     - عمود {$column}: " . ($exists ? '✅' : '❌'));
            }
            
            // فحص أنواع البيانات
            try {
                $columnInfo = DB::select("DESCRIBE whatsapp_messages");
                foreach ($columnInfo as $col) {
                    if ($col->Field === 'user_type') {
                        $this->line("     - نوع عمود user_type: {$col->Type}");
                        if (strpos($col->Type, 'enum') === false && strpos($col->Type, 'varchar') === false) {
                            $this->warn("     ⚠️ نوع العمود قد يسبب مشاكل");
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("   خطأ في فحص أعمدة الجدول: " . $e->getMessage());
            }
        } else {
            $this->error("   - جدول whatsapp_messages: ❌ غير موجود");
        }
    }

    private function analyzeExistingMessages()
    {
        $this->info("\n📊 4. تحليل الرسائل الموجودة:");
        
        try {
            $totalMessages = WhatsAppMessage::count();
            $this->line("   - إجمالي الرسائل: {$totalMessages}");
            
            // تحليل حسب النوع
            $messageTypes = DB::table('whatsapp_messages')
                ->select('message_type', DB::raw('count(*) as count'))
                ->groupBy('message_type')
                ->get();
            
            $this->line("   - تحليل حسب النوع:");
            foreach ($messageTypes as $type) {
                $this->line("     * {$type->message_type}: {$type->count} رسالة");
            }
            
            // تحليل حسب الحالة
            $statuses = DB::table('whatsapp_messages')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();
                
            $this->line("   - تحليل حسب الحالة:");
            foreach ($statuses as $status) {
                $this->line("     * {$status->status}: {$status->count} رسالة");
            }
            
            // آخر الرسائل
            $recentMessages = WhatsAppMessage::orderBy('created_at', 'desc')->take(5)->get();
            $this->line("   - آخر 5 رسائل:");
            foreach ($recentMessages as $msg) {
                $this->line("     * ID:{$msg->id} | {$msg->message_type} | {$msg->status} | {$msg->created_at}");
            }
            
        } catch (\Exception $e) {
            $this->error("   خطأ في تحليل الرسائل: " . $e->getMessage());
        }
    }

    private function testNotificationTypes()
    {
        $this->info("\n🧪 5. اختبار أنواع الإشعارات:");
        
        if (!$this->confirm('هل تريد تشغيل اختبارات تفاعلية؟ (قد تنشئ بيانات تجريبية)')) {
            return;
        }
        
        // اختبار إشعار الغياب
        $this->testAbsenceNotification();
        
        // اختبار إشعار الجلسة
        $this->testSessionNotification();
        
        // اختبار إشعار تكليف المعلم
        $this->testTeacherAssignmentNotification();
    }

    private function testAbsenceNotification()
    {
        $this->line("\n   🎯 اختبار إشعار الغياب:");
        
        try {
            // البحث عن طالب موجود
            $student = Student::whereNotNull('phone')->first();
            if (!$student) {
                $this->warn("     - لا يوجد طالب بهاتف لاختبار الإشعار");
                return;
            }
            
            $this->line("     - الطالب المختار: {$student->name}");
            
            // إنشاء سجل غياب
            $attendance = new Attendance([
                'attendable_type' => Student::class,
                'attendable_id' => $student->id,
                'date' => now(),
                'period' => 'العصر',
                'status' => 'غائب'
            ]);
            
            // محاكاة تشغيل Observer
            $observer = new AttendanceObserver();
            
            $messagesCountBefore = WhatsAppMessage::count();
            
            // محاولة إنشاء السجل وتشغيل Observer
            $attendance->save();
            
            $messagesCountAfter = WhatsAppMessage::count();
            $newMessages = $messagesCountAfter - $messagesCountBefore;
            
            $this->line("     - تم إنشاء {$newMessages} رسالة جديدة");
            
            if ($newMessages > 0) {
                $latestMessage = WhatsAppMessage::latest()->first();
                $this->line("     - آخر رسالة: {$latestMessage->message_type} | {$latestMessage->status}");
            }
            
        } catch (\Exception $e) {
            $this->error("     - خطأ في اختبار الغياب: " . $e->getMessage());
        }
    }

    private function testSessionNotification()
    {
        $this->line("\n   🎯 اختبار إشعار الجلسة:");
        // TODO: تنفيذ اختبار جلسة التسميع
        $this->warn("     - لم يتم تنفيذ اختبار جلسة التسميع بعد");
    }

    private function testTeacherAssignmentNotification()
    {
        $this->line("\n   🎯 اختبار إشعار تكليف المعلم:");
        // TODO: تنفيذ اختبار تكليف المعلم
        $this->warn("     - لم يتم تنفيذ اختبار تكليف المعلم بعد");
    }

    private function showRecommendations()
    {
        $this->info("\n💡 6. التوصيات:");
        
        $recommendations = [];
        
        // فحص الإعدادات
        if (!WhatsAppSetting::notificationsEnabled()) {
            $recommendations[] = "تفعيل الإشعارات العامة";
        }
        
        if (!WhatsAppSetting::get('api_url')) {
            $recommendations[] = "إعداد رابط API للواتساب";
        }
        
        if (!WhatsAppSetting::isNotificationEnabled('notify_attendance')) {
            $recommendations[] = "تفعيل إشعارات الحضور/الغياب";
        }
        
        // فحص الجداول
        if (!Schema::hasTable('whatsapp_messages')) {
            $recommendations[] = "إنشاء جدول whatsapp_messages";
        }
        
        if (empty($recommendations)) {
            $this->line("   ✅ لا توجد توصيات - النظام يبدو سليماً");
        } else {
            foreach ($recommendations as $recommendation) {
                $this->line("   🔧 {$recommendation}");
            }
        }
        
        // إرشادات الإصلاح
        $this->info("\n📝 إرشادات الإصلاح:");
        $this->line("   1. تفعيل الإشعارات: php artisan tinker");
        $this->line("      WhatsAppSetting::set('notifications_enabled', true)");
        $this->line("      WhatsAppSetting::set('notify_attendance', true)");
        $this->line("");
        $this->line("   2. فحص الـ Observers: تأكد من تسجيلها في AppServiceProvider");
        $this->line("");
        $this->line("   3. اختبار يدوي: إنشاء سجل غياب وفحص الرسائل");
    }
}
