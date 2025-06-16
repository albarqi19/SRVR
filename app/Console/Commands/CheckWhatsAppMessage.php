<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppMessage;

class CheckWhatsAppMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:check-message {id : Message ID to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check WhatsApp message status and details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messageId = $this->argument('id');
        
        $message = WhatsAppMessage::find($messageId);
        
        if (!$message) {
            $this->error("Message not found: {$messageId}");
            return 1;
        }
        
        $this->info("Message Details:");
        $this->line("ID: {$message->id}");
        $this->line("Phone: {$message->phone_number}");
        $this->line("Type: {$message->message_type}");
        $this->line("Status: {$message->status}");
        $this->line("Content: {$message->content}");
        $this->line("Created: {$message->created_at}");
        $this->line("Sent: " . ($message->sent_at ?: 'Not sent'));
        
        if ($message->error_message) {
            $this->error("Error: {$message->error_message}");
        }
        
        if ($message->api_response) {
            $this->info("API Response: {$message->api_response}");
        }
        
        return 0;
    }
}
