<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuranCircle;

class CheckTimePeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:time-periods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check time periods in quran_circles table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking time_period values in quran_circles table...');
        
        $periods = QuranCircle::select('time_period')->distinct()->pluck('time_period');
        
        $this->info('Found time periods:');
        foreach ($periods as $period) {
            $this->line('- ' . $period . ' (length: ' . strlen($period) . ')');
        }
        
        // Check circle_type values
        $this->info('');
        $this->info('Checking circle_type values...');
        
        $types = QuranCircle::select('circle_type')->distinct()->pluck('circle_type');
        
        $this->info('Found circle types:');
        foreach ($types as $type) {
            $this->line('- ' . $type . ' (length: ' . strlen($type) . ')');
        }
        
        // Check table structure
        $this->info('');
        $this->info('Checking table structure...');
        $columns = \DB::select('SHOW COLUMNS FROM quran_circles WHERE Field IN ("time_period", "circle_type")');
        
        foreach ($columns as $column) {
            $this->line('Column: ' . $column->Field);
            $this->line('Type: ' . $column->Type);
            $this->line('Null: ' . $column->Null);
            $this->line('Default: ' . $column->Default);
            $this->line('---');
        }
    }
}
