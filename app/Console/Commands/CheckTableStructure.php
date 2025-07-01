<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTableStructure extends Command
{
    protected $signature = 'check:table-structure {table}';
    protected $description = 'فحص هيكل جدول محدد';

    public function handle()
    {
        $tableName = $this->argument('table');
        
        $this->info("🔍 فحص هيكل جدول: {$tableName}");
        $this->newLine();

        try {
            $columns = Schema::getColumnListing($tableName);
            
            if (empty($columns)) {
                $this->error("❌ الجدول {$tableName} غير موجود أو فارغ");
                return;
            }
            
            $this->info("✅ أعمدة جدول {$tableName}:");
            foreach ($columns as $column) {
                $this->info("   - {$column}");
            }
            
            $this->newLine();
            
            // عرض أول 3 سجلات
            $records = DB::table($tableName)->limit(3)->get();
            if ($records->count() > 0) {
                $this->info("📋 أول 3 سجلات:");
                $this->table(
                    $columns,
                    $records->map(function ($record) {
                        return (array) $record;
                    })->toArray()
                );
            } else {
                $this->warn("⚠️ الجدول فارغ");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ: " . $e->getMessage());
        }
    }
}
