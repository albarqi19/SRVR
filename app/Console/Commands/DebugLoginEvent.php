<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Events\TeacherLoginEvent;
use App\Listeners\SendLoginNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class DebugLoginEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-login-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تشخيص مشكلة حدث تسجيل الدخول';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 تشخيص مشكلة حدث تسجيل الدخول');
        $this->info('=' . str_repeat('=', 50));

        // 1. البحث عن معلم بهاتف للاختبار
        $this->info('1️⃣ البحث عن معلم بهاتف:');
        $teacher = Teacher::with('mosque')->whereNotNull('phone')->where('phone', '!=', '')->first();
        
        if (!$teacher) {
            $this->warn('   ⚠️  لا يوجد معلمين برقم هاتف. سأقوم بإنشاء معلم تجريبي...');
            
            $mosque = \App\Models\Mosque::first();
            $teacher = Teacher::create([
                'identity_number' => 'LOGIN_TEST_' . time(),
                'name' => 'معلم تسجيل الدخول التجريبي',
                'nationality' => 'سعودي',
                'phone' => '966501234567',
                'mosque_id' => $mosque->id,
                'job_title' => 'معلم حفظ',
                'task_type' => 'معلم بمكافأة',
                'circle_type' => 'حلقة فردية',
                'work_time' => 'عصر',
            ]);
            $this->info("✅ تم إنشاء معلم تجريبي: {$teacher->name}");
        } else {
            $this->info("✅ تم العثور على معلم: {$teacher->name}");
        }
        
        $this->line("   - الهاتف: {$teacher->phone}");
        $this->line("   - المسجد: " . ($teacher->mosque ? $teacher->mosque->name : 'غير محدد'));

        // 2. فحص تسجيل Event Listeners
        $this->info('2️⃣ فحص تسجيل Event Listeners:');
        $listeners = Event::getListeners('App\\Events\\TeacherLoginEvent');
        
        if (empty($listeners)) {
            $this->error('❌ لا توجد Listeners مسجلة للحدث TeacherLoginEvent');
            $this->line('   سأحاول تسجيل Listener يدوياً...');
            
            Event::listen('App\\Events\\TeacherLoginEvent', 'App\\Listeners\\SendLoginNotification');
            
            $listeners = Event::getListeners('App\\Events\\TeacherLoginEvent');
            if (!empty($listeners)) {
                $this->info('✅ تم تسجيل Listener يدوياً');
            }
        } else {
            $this->info('✅ Listeners مسجلة للحدث:');
            foreach ($listeners as $listener) {
                $this->line("   - " . (is_string($listener) ? $listener : get_class($listener)));
            }
        }

        // 3. اختبار Listener مباشرة
        $this->info('3️⃣ اختبار Listener مباشرة:');
        try {
            $event = new TeacherLoginEvent($teacher, '192.168.1.100', 'Test Browser');
            $listener = new SendLoginNotification();
            
            Log::info('=== بدء اختبار Listener مباشرة ===');
            $listener->handle($event);
            Log::info('=== انتهاء اختبار Listener ===');
            
            $this->info('✅ تم تشغيل Listener مباشرة بدون أخطاء');
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Listener: " . $e->getMessage());
            $this->line("   - الملف: " . $e->getFile());
            $this->line("   - السطر: " . $e->getLine());
        }

        // 4. اختبار Event مع Listener
        $this->info('4️⃣ اختبار Event مع تسجيل Listener:');
        try {
            // تأكد من تسجيل Listener
            Event::listen('App\\Events\\TeacherLoginEvent', function($event) {
                $this->line("   🎯 تم استقبال الحدث للمعلم: {$event->teacher->name}");
                
                $listener = new SendLoginNotification();
                $listener->handle($event);
            });
            
            $event = new TeacherLoginEvent($teacher, '192.168.1.100', 'Test Browser');
            event($event);
            
            $this->info('✅ تم إطلاق Event مع Listener');
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في Event: " . $e->getMessage());
        }

        // 5. فحص الرسائل الجديدة
        $this->info('5️⃣ فحص الرسائل الجديدة:');
        $loginMessages = \App\Models\WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->latest()
            ->limit(3)
            ->get();

        if ($loginMessages->count() > 0) {
            $this->info("✅ توجد {$loginMessages->count()} رسالة للمعلم:");
            foreach ($loginMessages as $msg) {
                $this->line("   - ID: {$msg->id}, النوع: {$msg->message_type}, الحالة: {$msg->status}");
                $this->line("     الوقت: {$msg->created_at}");
            }
        } else {
            $this->error('❌ لا توجد رسائل للمعلم');
        }

        // 6. فحص آخر logs
        $this->info('6️⃣ فحص آخر logs:');
        $this->displayRecentLogs();

        // تنظيف إذا كان معلم تجريبي
        if (str_contains($teacher->identity_number, 'LOGIN_TEST_')) {
            $teacher->delete();
            $this->info('🧹 تم حذف المعلم التجريبي');
        }

        $this->info('🏁 انتهى التشخيص!');
    }

    private function displayRecentLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $this->warn('   ⚠️  ملف log غير موجود');
            return;
        }

        $lines = [];
        $file = new \SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - 10);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = $file->current();
            if (strpos($line, 'login') !== false || strpos($line, 'Login') !== false || strpos($line, 'تسجيل') !== false) {
                $lines[] = trim($line);
            }
            $file->next();
        }

        if (empty($lines)) {
            $this->warn('   ⚠️  لا توجد logs متعلقة بتسجيل الدخول');
        } else {
            $this->line('   📋 آخر logs متعلقة بتسجيل الدخول:');
            foreach (array_slice($lines, -3) as $line) {
                $this->line("      " . substr($line, 0, 100) . "...");
            }
        }
    }
}
