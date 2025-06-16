<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixCurriculumTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:curriculum-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the student_curriculum_progress table and its foreign key constraints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting curriculum tables fix...');

        try {
            // Step 1: Check if tables exist
            $this->info('Checking if required tables exist...');
            
            $hasStudentCurricula = Schema::hasTable('student_curricula');
            $hasCurriculumPlans = Schema::hasTable('curriculum_plans');
            
            if (!$hasStudentCurricula) {
                $this->error('student_curricula table does not exist! Cannot proceed.');
                return 1;
            }
            
            if (!$hasCurriculumPlans) {
                $this->error('curriculum_plans table does not exist! Cannot proceed.');
                return 1;
            }
            
            $this->info('Required tables exist. Proceeding...');
            
            // Step 2: Drop the problematic table
            $this->info('Dropping student_curriculum_progress table if it exists...');
            Schema::dropIfExists('student_curriculum_progress');
            $this->info('Table dropped successfully.');
            
            // Step 3: Create the table with proper foreign keys
            $this->info('Creating student_curriculum_progress table with correct foreign key constraints...');
            
            DB::statement("CREATE TABLE student_curriculum_progress (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                student_curriculum_id BIGINT UNSIGNED NOT NULL,
                curriculum_plan_id BIGINT UNSIGNED NOT NULL,
                start_date DATE NOT NULL,
                completion_date DATE NULL,
                status ENUM('قيد التنفيذ', 'مكتمل') NOT NULL DEFAULT 'قيد التنفيذ',
                completion_percentage FLOAT NOT NULL DEFAULT '0',
                teacher_notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                CONSTRAINT student_curriculum_progress_student_curriculum_id_foreign
                    FOREIGN KEY (student_curriculum_id) REFERENCES student_curricula (id) ON DELETE CASCADE,
                CONSTRAINT student_curriculum_progress_curriculum_plan_id_foreign
                    FOREIGN KEY (curriculum_plan_id) REFERENCES curriculum_plans (id) ON DELETE CASCADE
            )");
            
            $this->info('Table created successfully with proper foreign key constraints.');
            
            $this->info('All curriculum tables fix completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
