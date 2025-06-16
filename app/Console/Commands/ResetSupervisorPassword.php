<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetSupervisorPassword extends Command
{
    protected $signature = 'supervisor:reset-password {email?}';
    protected $description = 'إعادة تعيين كلمة مرور المشرف';

    public function handle()
    {
        $email = $this->argument('email');
        
        if (!$email) {
            // عرض قائمة المشرفين المتاحين
            $supervisors = User::role('supervisor')->get();
            
            if ($supervisors->isEmpty()) {
                $this->error('لا يوجد مشرفون في النظام');
                return;
            }
            
            $this->info('المشرفون المتاحون:');
            foreach ($supervisors as $supervisor) {
                $this->line($supervisor->email . ' - ' . $supervisor->name);
            }
            
            $email = $this->ask('أدخل البريد الإلكتروني للمشرف');
        }
        
        $supervisor = User::role('supervisor')->where('email', $email)->first();
        
        if (!$supervisor) {
            $this->error('المشرف غير موجود');
            return;
        }
        
        $newPassword = $this->ask('أدخل كلمة المرور الجديدة (اتركها فارغة لاستخدام supervisor123)', 'supervisor123');
        
        $supervisor->password = Hash::make($newPassword);
        $supervisor->save();
        
        $this->info('تم تحديث كلمة المرور بنجاح');
        $this->line("المشرف: {$supervisor->name}");
        $this->line("البريد الإلكتروني: {$supervisor->email}");
        $this->line("رقم الهوية: {$supervisor->identity_number}");
        $this->line("كلمة المرور الجديدة: {$newPassword}");
    }
}
