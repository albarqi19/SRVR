<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class FindSupervisorCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervisor:find-credentials {--create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'العثور على بيانات تسجيل دخول المشرفين وإنشاء مشرف تجريبي إذا لزم الأمر';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== البحث عن المشرفين في النظام ===");
        $this->line("");

        // البحث عن المستخدمين الذين لديهم دور supervisor
        $this->info("=== البحث في جدول Users (نظام Filament) ===");
        
        try {
            $supervisorRole = Role::where('name', 'supervisor')->first();
            
            if ($supervisorRole) {
                $supervisorUsers = User::role('supervisor')->get();
                
                if ($supervisorUsers->count() > 0) {
                    $this->info("تم العثور على {$supervisorUsers->count()} مشرف في جدول Users:");
                    
                    $this->table(
                        ['ID', 'الاسم', 'البريد الإلكتروني', 'اسم المستخدم', 'رقم الهوية', 'نشط؟'],
                        $supervisorUsers->map(function ($user) {
                            return [
                                $user->id,
                                $user->name,
                                $user->email,
                                $user->username ?? 'غير محدد',
                                $user->identity_number ?? 'غير محدد',
                                $user->is_active ? 'نعم' : 'لا'
                            ];
                        })
                    );
                } else {
                    $this->warn("لا يوجد مستخدمون بدور supervisor في جدول Users");
                }
            } else {
                $this->warn("دور supervisor غير موجود في النظام");
            }
        } catch (\Exception $e) {
            $this->error("خطأ في البحث في جدول Users: " . $e->getMessage());
        }

        $this->line("");
        
        // البحث عن المعلمين الذين لديهم دور مشرف
        $this->info("=== البحث في جدول Teachers ===");
        
        try {
            $supervisorTeachers = Teacher::whereIn('task_type', ['مشرف', 'مساعد مشرف'])
                                        ->where('is_active_user', true)
                                        ->get();
            
            if ($supervisorTeachers->count() > 0) {
                $this->info("تم العثور على {$supervisorTeachers->count()} مشرف في جدول Teachers:");
                
                $this->table(
                    ['ID', 'الاسم', 'رقم الهوية', 'الهاتف', 'نوع المهمة', 'كلمة المرور', 'آخر دخول'],
                    $supervisorTeachers->map(function ($teacher) {
                        return [
                            $teacher->id,
                            $teacher->name,
                            $teacher->identity_number,
                            $teacher->phone ?? 'غير محدد',
                            $teacher->task_type,
                            $teacher->plain_password ?? 'غير محدد',
                            $teacher->last_login_at ? $teacher->last_login_at->format('Y-m-d H:i') : 'لم يسجل دخول'
                        ];
                    })
                );
                
                // عرض كيفية تسجيل الدخول عبر API
                $this->line("");
                $this->info("=== كيفية تسجيل الدخول عبر API ===");
                $this->info("مسارات API المتاحة للمشرفين:");
                $this->info("1. تسجيل دخول عام: POST /api/auth/login");
                $this->info("2. تسجيل دخول مشرف: POST /api/auth/supervisor/login");
                $this->line("");
                
                foreach ($supervisorTeachers as $teacher) {
                    $this->info("--- مشرف: {$teacher->name} ---");
                    $this->info("طريقة تسجيل الدخول عبر API:");
                    $this->line("");
                    $this->info("POST /api/auth/supervisor/login");
                    $this->info("Content-Type: application/json");
                    $this->line("");
                    $this->info("Body:");
                    $this->info(json_encode([
                        "identity_number" => $teacher->identity_number,
                        "password" => $teacher->plain_password ?? $teacher->identity_number
                    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    $this->line("");
                }
            } else {
                $this->warn("لا يوجد مشرفون نشطون في جدول Teachers");
            }
        } catch (\Exception $e) {
            $this->error("خطأ في البحث في جدول Teachers: " . $e->getMessage());
        }

        // إنشاء مشرف تجريبي إذا طُلب ذلك
        if ($this->option('create') || $this->confirm('هل تريد إنشاء مشرف تجريبي؟')) {
            $this->createTestSupervisor();
        }
        
        $this->line("");
        $this->info("=== ملخص مسارات API للمشرفين ===");
        $this->info("Base URL: http://127.0.0.1:8000/api");
        $this->info("");
        $this->info("🔐 مسارات المصادقة:");
        $this->info("POST /auth/login - تسجيل دخول عام");
        $this->info("POST /auth/supervisor/login - تسجيل دخول مشرف");
        $this->info("");
        $this->info("📊 مسارات المشرف:");
        $this->info("GET /supervisors - قائمة جميع المشرفين");
        $this->info("GET /supervisors/{id} - تفاصيل مشرف محدد");
        $this->info("GET /supervisors/statistics - إحصائيات المشرفين");
        $this->info("GET /supervisor/dashboard - لوحة تحكم المشرف");
        $this->info("GET /supervisor/circles - حلقات المشرف");
        $this->info("GET /supervisor/teachers - معلمين المشرف");
        $this->info("GET /supervisor/students - طلاب المشرف");
    }

    private function createTestSupervisor()
    {
        $this->info("=== إنشاء مشرف تجريبي ===");
        
        $identityNumber = '1234567890';
        $password = 'supervisor123';
        
        try {
            // إنشاء في جدول Teachers
            $teacher = Teacher::updateOrCreate(
                ['identity_number' => $identityNumber],
                [
                    'name' => 'مشرف تجريبي',
                    'nationality' => 'سعودي',
                    'phone' => '0501234567',
                    'task_type' => 'مشرف',
                    'cost_center' => 'المسجد النبوي',
                    'is_active' => true,
                    'is_active_user' => true,
                    'password' => Hash::make($password),
                    'plain_password' => $password,
                    'must_change_password' => false,
                ]
            );
            
            // إنشاء في جدول Users
            $user = User::updateOrCreate(
                ['identity_number' => $identityNumber],
                [
                    'name' => 'مشرف تجريبي',
                    'email' => 'supervisor@test.com',
                    'username' => 'supervisor_test',
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            
            // إضافة دور supervisor
            if (!$user->hasRole('supervisor')) {
                $user->assignRole('supervisor');
            }
            
            $this->info("✅ تم إنشاء المشرف التجريبي بنجاح!");
            $this->line("");
            $this->info("=== بيانات المشرف التجريبي ===");
            $this->info("الاسم: مشرف تجريبي");
            $this->info("رقم الهوية: {$identityNumber}");
            $this->info("كلمة المرور: {$password}");
            $this->info("البريد الإلكتروني: supervisor@test.com");
            $this->info("اسم المستخدم: supervisor_test");
            $this->line("");
            $this->info("=== اختبار API ===");
            $this->info("curl -X POST http://127.0.0.1:8000/api/auth/supervisor/login \\");
            $this->info("  -H 'Content-Type: application/json' \\");
            $this->info("  -d '{\"identity_number\":\"$identityNumber\",\"password\":\"$password\"}'");
            
        } catch (\Exception $e) {
            $this->error("❌ فشل في إنشاء المشرف التجريبي: " . $e->getMessage());
        }
    }
}
