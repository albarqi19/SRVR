<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShowUserCredentials extends Command
{
    protected $signature = 'show:user-credentials {--export : تصدير إلى ملف}';
    protected $description = 'عرض بيانات تسجيل الدخول لجميع المستخدمين';

    public function handle()
    {
        $this->info('📋 قائمة بيانات تسجيل الدخول لجميع المستخدمين');
        $this->newLine();

        $users = DB::table('users')
            ->select('id', 'name', 'email', 'username', 'created_at')
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('لا يوجد مستخدمين في النظام');
            return;
        }

        // عرض الجدول
        $tableData = [];
        foreach ($users as $user) {
            $tableData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->username ?? 'غير محدد',
                '123456', // كلمة المرور الافتراضية
                $user->created_at
            ];
        }

        $this->table(
            ['ID', 'الاسم', 'البريد الإلكتروني', 'اسم المستخدم', 'كلمة المرور', 'تاريخ الإنشاء'],
            $tableData
        );

        $this->newLine();
        $this->info('📊 إحصائيات:');
        $this->info("   👥 إجمالي المستخدمين: {$users->count()}");
        
        // تجميع حسب نوع البريد
        $teacherEmails = $users->filter(function($user) {
            return strpos($user->email, 'teacher_') === 0;
        });
        
        $adminEmails = $users->filter(function($user) {
            return strpos($user->email, 'admin') !== false || strpos($user->email, 'test') !== false;
        });
        
        $otherEmails = $users->filter(function($user) {
            return strpos($user->email, 'teacher_') !== 0 && 
                   strpos($user->email, 'admin') === false && 
                   strpos($user->email, 'test') === false;
        });

        $this->info("   👨‍🏫 حسابات المعلمين: {$teacherEmails->count()}");
        $this->info("   🔧 حسابات الاختبار/الإدارة: {$adminEmails->count()}");
        $this->info("   👤 حسابات أخرى: {$otherEmails->count()}");

        $this->newLine();
        $this->info('💡 معلومات مهمة:');
        $this->info('   🔑 كلمة المرور الافتراضية: 123456');
        $this->info('   📧 يمكن تسجيل الدخول بالبريد الإلكتروني');
        $this->info('   👤 يمكن تسجيل الدخول باسم المستخدم (إذا كان محدد)');
        $this->info('   ⚠️  يُنصح بتغيير كلمات المرور في بيئة الإنتاج');

        // تصدير إلى ملف إذا طُلب
        if ($this->option('export')) {
            $this->exportToFile($users);
        }

        $this->newLine();
        $this->info('🎯 للنشر على Railway:');
        $this->info('   1. تأكد من تغيير APP_DEBUG=false');
        $this->info('   2. استخدم كلمات مرور قوية للحسابات المهمة');
        $this->info('   3. قم بإنشاء حساب admin منفصل للإنتاج');
    }

    private function exportToFile($users)
    {
        $filename = 'user_credentials_' . date('Y-m-d_H-i-s') . '.txt';
        $filepath = storage_path('logs/' . $filename);
        
        $content = "📋 بيانات تسجيل الدخول - " . date('Y-m-d H:i:s') . "\n";
        $content .= str_repeat('=', 60) . "\n\n";
        
        foreach ($users as $user) {
            $content .= "ID: {$user->id}\n";
            $content .= "الاسم: {$user->name}\n";
            $content .= "البريد الإلكتروني: {$user->email}\n";
            $content .= "اسم المستخدم: " . ($user->username ?? 'غير محدد') . "\n";
            $content .= "كلمة المرور: 123456\n";
            $content .= "تاريخ الإنشاء: {$user->created_at}\n";
            $content .= str_repeat('-', 40) . "\n\n";
        }
        
        $content .= "💡 ملاحظات:\n";
        $content .= "- كلمة المرور الافتراضية لجميع المستخدمين: 123456\n";
        $content .= "- يمكن تسجيل الدخول بالبريد الإلكتروني أو اسم المستخدم\n";
        $content .= "- يُنصح بتغيير كلمات المرور في بيئة الإنتاج\n";
        
        file_put_contents($filepath, $content);
        
        $this->info("📄 تم تصدير البيانات إلى: {$filepath}");
    }
}
