<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckWhatsAppTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-whats-app-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص بنية جدول WhatsApp Messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 فحص بنية جدول whatsapp_messages');
        $this->info('=' . str_repeat('=', 50));

        // 1. فحص بنية الجدول
        $this->info('1️⃣ أعمدة الجدول:');
        $columns = Schema::getColumnListing('whatsapp_messages');
        foreach ($columns as $column) {
            $type = Schema::getColumnType('whatsapp_messages', $column);
            $this->line("   - {$column}: {$type}");
        }

        // 2. فحص حجم عمود message_type
        $this->info('2️⃣ تفاصيل عمود message_type:');
        $columnInfo = DB::select("SHOW COLUMNS FROM whatsapp_messages WHERE Field = 'message_type'");
        if (!empty($columnInfo)) {
            $this->line("   - النوع: " . $columnInfo[0]->Type);
            $this->line("   - القيم المسموحة: " . ($columnInfo[0]->Null ?? 'NULL'));
            $this->line("   - القيمة الافتراضية: " . ($columnInfo[0]->Default ?? 'NULL'));
        }

        // 3. اختبار إنشاء رسالة جديدة
        $this->info('3️⃣ اختبار إنشاء رسالة:');
        try {
            $message = WhatsAppMessage::create([
                'user_type' => 'teacher',
                'user_id' => 1,
                'phone_number' => '966501234567',
                'message_type' => 'welcome_test',
                'content' => 'رسالة اختبار',
                'status' => 'pending'
            ]);
            
            $this->info("✅ تم إنشاء رسالة - ID: {$message->id}");
            $this->line("   - message_type حُفظ كـ: '{$message->message_type}'");
            
            // حذف الرسالة التجريبية
            $message->delete();
            $this->line("   - تم حذف الرسالة التجريبية");
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في إنشاء الرسالة: " . $e->getMessage());
        }

        // 4. فحص Observer Registration
        $this->info('4️⃣ فحص تسجيل Observer:');
        
        // التحقق من AppServiceProvider
        $appServiceProvider = file_get_contents(app_path('Providers/AppServiceProvider.php'));
        if (strpos($appServiceProvider, 'TeacherObserver') !== false) {
            $this->info("✅ TeacherObserver مسجل في AppServiceProvider");
        } else {
            $this->error("❌ TeacherObserver غير مسجل في AppServiceProvider");
        }

        $this->info('🏁 انتهى الفحص!');
    }
}
