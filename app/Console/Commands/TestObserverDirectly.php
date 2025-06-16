<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Mosque;
use App\Models\WhatsAppMessage;
use App\Observers\TeacherObserver;
use Illuminate\Support\Facades\Log;

class TestObserverDirectly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:observer-directly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'اختبار Observer مباشرة ومراجعة الـ logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 اختبار Observer مباشرة');
        $this->info('=' . str_repeat('=', 50));

        // 1. إنشاء معلم جديد مع مراقبة logs
        $this->info('1️⃣ إنشاء معلم جديد:');
        
        $mosque = Mosque::first();
        if (!$mosque) {
            $this->error('❌ لا توجد مساجد');
            return;
        }

        // تفعيل log للاختبار
        Log::info('=== بدء اختبار Observer ===');

        $messagesBefore = WhatsAppMessage::count();
        
        $teacher = Teacher::create([
            'identity_number' => 'TEST' . time(),
            'name' => 'معلم اختبار Observer',
            'nationality' => 'سعودي',
            'phone' => '966501234567',
            'mosque_id' => $mosque->id,
            'job_title' => 'معلم حفظ',
            'task_type' => 'معلم بمكافأة',
            'circle_type' => 'حلقة فردية',
            'work_time' => 'عصر',
            'is_active_user' => true,
            'must_change_password' => true,
        ]);

        $this->info("✅ تم إنشاء المعلم: {$teacher->name} - ID: {$teacher->id}");

        // انتظار قصير
        sleep(2);

        $messagesAfter = WhatsAppMessage::count();
        $newMessages = $messagesAfter - $messagesBefore;
        
        $this->line("   - الرسائل قبل: {$messagesBefore}");
        $this->line("   - الرسائل بعد: {$messagesAfter}");
        $this->line("   - رسائل جديدة: {$newMessages}");

        // 2. اختبار Observer يدوياً
        $this->info('2️⃣ اختبار Observer يدوياً:');
        
        try {
            $observer = new TeacherObserver();
            
            // استدعاء created method مباشرة
            $observer->created($teacher);
            
            $this->info('✅ تم استدعاء Observer->created() مباشرة');
            
            // فحص الرسائل مرة أخرى
            $messagesAfterManual = WhatsAppMessage::count();
            $manualMessages = $messagesAfterManual - $messagesAfter;
            
            $this->line("   - الرسائل بعد الاستدعاء اليدوي: {$messagesAfterManual}");
            $this->line("   - رسائل جديدة من الاستدعاء اليدوي: {$manualMessages}");
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاستدعاء اليدوي: " . $e->getMessage());
        }

        // 3. فحص آخر logs
        $this->info('3️⃣ فحص آخر logs:');
        $this->displayRecentLogs();

        // 4. فحص الرسائل للمعلم
        $teacherMessages = WhatsAppMessage::where('user_type', 'teacher')
            ->where('user_id', $teacher->id)
            ->get();

        if ($teacherMessages->count() > 0) {
            $this->info("4️⃣ رسائل المعلم ({$teacherMessages->count()}):");
            foreach ($teacherMessages as $msg) {
                $this->line("   - ID: {$msg->id}, النوع: {$msg->message_type}, الحالة: {$msg->status}");
            }
        } else {
            $this->error('4️⃣ ❌ لا توجد رسائل للمعلم');
        }

        // تنظيف
        $teacher->delete();
        $this->info('🧹 تم حذف المعلم التجريبي');
        
        Log::info('=== انتهاء اختبار Observer ===');
        
        $this->info('🏁 انتهى الاختبار!');
    }

    private function displayRecentLogs()
    {
        // قراءة آخر سطور من log file
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            $this->warn('   ⚠️  ملف log غير موجود');
            return;
        }

        // قراءة آخر 10 أسطر
        $lines = [];
        $file = new \SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - 20);
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $line = $file->current();
            if (strpos($line, 'Observer') !== false || strpos($line, 'المعلم') !== false) {
                $lines[] = trim($line);
            }
            $file->next();
        }

        if (empty($lines)) {
            $this->warn('   ⚠️  لا توجد logs متعلقة بـ Observer');
        } else {
            $this->line('   📋 آخر logs متعلقة بـ Observer:');
            foreach (array_slice($lines, -5) as $line) {
                $this->line("      " . substr($line, 0, 100) . "...");
            }
        }
    }
}
