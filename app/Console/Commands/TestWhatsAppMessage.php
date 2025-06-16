<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Jobs\SendWhatsAppMessage;
use App\Services\WhatsAppService;

class TestWhatsAppMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone : Phone number to send test message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test WhatsApp message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        
        $this->info("Creating test message for: {$phone}");
        
        try {
            // إنشاء رسالة اختبار
            $message = WhatsAppMessage::createNotification(
                'admin',
                null,
                $phone,
                'هذه رسالة اختبار من نظام مركز القرآن الكريم - ' . now()->format('Y-m-d H:i:s'),
                'test',
                ['test' => true, 'sent_via_command' => true]
            );
            
            $this->info("Message created with ID: {$message->id}");
            
            // إرسال الرسالة مباشرة
            $this->info("Dispatching job to send message...");
            SendWhatsAppMessage::dispatch($message->id);
            
            $this->info("Job dispatched successfully!");
            
            // انتظار قليل ثم فحص الحالة
            sleep(2);
            
            $message->refresh();
            $this->info("Message status: {$message->status}");
            
            if ($message->status === 'failed' && $message->error_message) {
                $this->error("Error: " . $message->error_message);
            }
            
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
        }
        
        return 0;
    }
}
