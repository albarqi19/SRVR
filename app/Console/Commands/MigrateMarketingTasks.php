<?php

namespace App\Console\Commands;

use App\Http\Controllers\MarketingTaskMigrationController;
use Illuminate\Console\Command;

class MigrateMarketingTasks extends Command
{
    /**
     * اسم وتوصيف الأمر.
     *
     * @var string
     */
    protected $signature = 'marketing:migrate-tasks';

    /**
     * وصف الأمر.
     *
     * @var string
     */
    protected $description = 'ترحيل بيانات المهام التسويقية من النظام القديم إلى النظام الجديد';

    /**
     * تنفيذ الأمر.
     */
    public function handle()
    {
        $this->info('بدء عملية ترحيل بيانات المهام التسويقية...');
        
        $controller = new MarketingTaskMigrationController();
        $result = $controller->migrateFromCli();
        
        if ($result === 0) {
            $this->info('تم ترحيل البيانات بنجاح!');
            return 0;
        } else {
            $this->error('حدث خطأ أثناء ترحيل البيانات!');
            return 1;
        }
    }
}