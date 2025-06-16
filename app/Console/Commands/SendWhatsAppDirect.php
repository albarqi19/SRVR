<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;

class SendWhatsAppDirect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-direct {phone : Phone number} {message : Message to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp message directly without queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $messageText = $this->argument('message');
        
        $this->info("Sending direct WhatsApp message to: {$phone}");
        
        // الحصول على إعدادات API
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        
        if (!$apiUrl || !$apiToken) {
            $this->error("API URL or Token not configured");
            return 1;
        }
        
        $this->info("API URL: {$apiUrl}");
        $this->info("API Token: " . (strlen($apiToken) > 4 ? substr($apiToken, 0, 4) . '***' : $apiToken));
        
        // إعداد البيانات للإرسال
        $payload = [
            'action' => 'send_message',
            'phone' => $phone,
            'message' => $messageText
        ];
        
        $this->info("Payload: " . json_encode($payload, JSON_UNESCAPED_UNICODE));
        
        try {
            // إرسال الطلب
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($apiUrl, $payload);
            
            $this->info("Response Status: " . $response->status());
            $this->info("Response Body: " . $response->body());
            
            if ($response->successful()) {
                $this->info("✅ Message sent successfully!");
                
                // إنشاء سجل في قاعدة البيانات
                $message = WhatsAppMessage::create([
                    'user_type' => 'admin',
                    'user_id' => null,
                    'phone_number' => $phone,
                    'message_type' => 'test',
                    'content' => $messageText,
                    'direction' => 'outgoing',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'api_response' => $response->body(),
                ]);
                
                $this->info("Message saved with ID: {$message->id}");
                
            } else {
                $this->error("❌ Failed to send message");
                $this->error("Status: " . $response->status());
                $this->error("Body: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("Exception occurred: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
        }
        
        return 0;
    }
}
