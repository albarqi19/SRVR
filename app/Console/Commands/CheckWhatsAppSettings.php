<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;

class CheckWhatsAppSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check WhatsApp settings and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking WhatsApp Settings...');
        
        // عرض جميع الإعدادات
        $settings = WhatsAppSetting::all();
        $this->info("Total settings: " . $settings->count());
        
        foreach ($settings as $setting) {
            $this->line("{$setting->setting_key}: {$setting->setting_value} (Active: " . ($setting->is_active ? 'Yes' : 'No') . ")");
        }
        
        $this->newLine();
        
        // فحص الإعدادات المهمة
        $apiUrl = WhatsAppSetting::get('api_url');
        $apiToken = WhatsAppSetting::get('api_token');
        $notificationsEnabled = WhatsAppSetting::notificationsEnabled();
        
        $this->info("API URL: " . ($apiUrl ?: 'Not set'));
        $this->info("API Token: " . ($apiToken ? 'Set' : 'Not set'));
        $this->info("Notifications Enabled: " . ($notificationsEnabled ? 'Yes' : 'No'));
        
        // اختبار WhatsAppService
        try {
            $service = new WhatsAppService();
            $this->info("WhatsAppService instantiated successfully");
        } catch (\Exception $e) {
            $this->error("WhatsAppService error: " . $e->getMessage());
        }
        
        return 0;
    }
}
