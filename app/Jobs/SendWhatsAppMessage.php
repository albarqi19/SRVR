<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $messageId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = WhatsAppMessage::find($this->messageId);
        
        if (!$message) {
            Log::error("WhatsApp message not found: {$this->messageId}");
            return;
        }

        // Check if notifications are enabled
        if (!WhatsAppSetting::notificationsEnabled()) {
            Log::info("WhatsApp notifications are disabled, skipping message {$this->messageId}");
            $message->update(['status' => 'skipped', 'error_message' => 'Notifications disabled']);
            return;
        }

        try {
            $this->sendMessage($message);
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message {$this->messageId}: " . $e->getMessage());
            $message->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => null
            ]);
            
            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Send the WhatsApp message via API.
     */
    private function sendMessage(WhatsAppMessage $message): void
    {
        $apiConfig = WhatsAppSetting::getApiConfig();
        
        if (empty($apiConfig['url']) || empty($apiConfig['token'])) {
            throw new \Exception('WhatsApp API configuration is incomplete');
        }

        // Prepare the message payload
        $payload = [
            'action' => 'send_message',
            'phone' => $message->phone_number,
            'message' => $message->content
        ];

        // Add template data if available
        if ($message->template_data) {
            $templateData = json_decode($message->template_data, true);
            if ($templateData) {
                $payload['template_data'] = $templateData;
            }
        }

        // Send the HTTP request
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiConfig['token'],
            'Content-Type' => 'application/json',
        ])
        ->timeout(30)
        ->post($apiConfig['url'], $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            
            $message->update([
                'status' => 'sent',
                'sent_at' => now(),
                'api_response' => json_encode($responseData),
                'error_message' => null
            ]);
            
            Log::info("WhatsApp message sent successfully: {$message->id}");
        } else {
            $errorMessage = $response->body();
            
            throw new \Exception("API request failed: {$response->status()} - {$errorMessage}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $message = WhatsAppMessage::find($this->messageId);
        
        if ($message) {
            $message->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'sent_at' => null
            ]);
        }
        
        Log::error("WhatsApp message job failed permanently: {$this->messageId} - " . $exception->getMessage());
    }
}
