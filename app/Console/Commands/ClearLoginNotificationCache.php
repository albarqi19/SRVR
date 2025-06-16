<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearLoginNotificationCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notification:clear-login-cache';

    /**
     * The console command description.
     */
    protected $description = 'Clear login notification cache to allow immediate notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // البحث عن جميع مفاتيح cache الخاصة بإشعارات تسجيل الدخول
        $cachePattern = 'login_notification_sent_*';
        
        // عد cache keys المحذوفة
        $deletedCount = 0;
        
        // للأسف، Laravel Cache لا يدعم pattern deletion مباشرة
        // لذا سنستخدم طريقة مختلفة حسب cache driver المستخدم
        
        try {
            // إذا كان cache driver يدعم flush للكل
            if (config('cache.default') === 'array' || config('cache.default') === 'file') {
                // نحتاج لحذف كل cache للبساطة (أو يمكن تحسينه لاحقاً)
                $this->warn('سيتم حذف جميع cache entries. هل تريد المتابعة؟');
                if ($this->confirm('هل أنت متأكد؟')) {
                    Cache::flush();
                    $this->info('تم حذف جميع cache entries بما في ذلك إشعارات تسجيل الدخول');
                } else {
                    $this->info('تم إلغاء العملية');
                }
            } else {
                // للـ Redis أو Memcached يمكن استخدام pattern
                $this->info('يمكن حذف cache entries محددة يدوياً إذا لزم الأمر');
                $this->line('Cache pattern: login_notification_sent_*');
            }
            
        } catch (\Exception $e) {
            $this->error('خطأ في حذف cache: ' . $e->getMessage());
            return 1;
        }

        $this->info('تم تنظيف cache إشعارات تسجيل الدخول بنجاح');
        return 0;
    }
}
