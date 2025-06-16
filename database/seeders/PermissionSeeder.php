<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إضافة الصلاحيات حسب الوحدات الوظيفية
     */
    public function run(): void
    {
        // تعريف الصلاحيات حسب المجموعات الوظيفية
        $permissionGroups = [
            // إدارة المستخدمين
            'users' => [
                'view_users' => 'عرض المستخدمين',
                'create_users' => 'إضافة مستخدمين',
                'edit_users' => 'تعديل المستخدمين',
                'delete_users' => 'حذف المستخدمين',
                'activate_users' => 'تفعيل المستخدمين',
                'deactivate_users' => 'تعطيل المستخدمين',
                'assign_roles' => 'إسناد الأدوار للمستخدمين',
                'view_roles' => 'عرض الأدوار',
                'edit_roles' => 'تعديل الأدوار',
            ],

            // إدارة الحلقات
            'circles' => [
                'view_circles' => 'عرض الحلقات',
                'create_circles' => 'إنشاء حلقات جديدة',
                'edit_circles' => 'تعديل الحلقات',
                'delete_circles' => 'حذف الحلقات',
                'manage_circle_students' => 'إدارة طلاب الحلقات',
                'view_circle_reports' => 'عرض تقارير الحلقات',
                'view_circle_requests' => 'عرض طلبات الحلقات',
                'approve_circle_requests' => 'اعتماد طلبات الحلقات',
                'reject_circle_requests' => 'رفض طلبات الحلقات',
            ],

            // إدارة المساجد
            'mosques' => [
                'view_mosques' => 'عرض المساجد',
                'create_mosques' => 'إضافة مساجد جديدة',
                'edit_mosques' => 'تعديل المساجد',
                'delete_mosques' => 'حذف المساجد',
                'assign_circles_to_mosques' => 'إسناد الحلقات للمساجد',
            ],

            // إدارة الطلاب
            'students' => [
                'view_students' => 'عرض الطلاب',
                'create_students' => 'إضافة طلاب',
                'edit_students' => 'تعديل بيانات الطلاب',
                'delete_students' => 'حذف الطلاب',
                'view_student_progress' => 'عرض تقدم الطلاب',
                'manage_student_attendance' => 'إدارة حضور الطلاب',
                'view_own_progress' => 'عرض التقدم الشخصي (للطالب نفسه)',
                'view_own_attendance' => 'عرض سجل الحضور الشخصي (للطالب نفسه)',
                'transfer_students' => 'نقل الطلاب بين الحلقات',
                'approve_student_transfers' => 'الموافقة على طلبات نقل الطلاب',
                'view_student_transfer_requests' => 'عرض طلبات نقل الطلاب',
            ],

            // إدارة المعلمين
            'teachers' => [
                'view_teachers' => 'عرض المعلمين',
                'create_teachers' => 'إضافة معلمين',
                'edit_teachers' => 'تعديل بيانات المعلمين',
                'delete_teachers' => 'حذف المعلمين',
                'assign_circles_to_teachers' => 'إسناد الحلقات للمعلمين',
                'view_teacher_performance' => 'عرض أداء المعلمين',
            ],

            // إدارة الحضور
            'attendance' => [
                'view_attendance' => 'عرض سجلات الحضور',
                'record_attendance' => 'تسجيل الحضور',
                'edit_attendance' => 'تعديل سجلات الحضور',
                'delete_attendance' => 'حذف سجلات الحضور',
                'generate_attendance_reports' => 'إنشاء تقارير الحضور',
            ],

            // إدارة التسميع
            'memorization' => [
                'view_memorization' => 'عرض سجلات التسميع',
                'record_memorization' => 'تسجيل التسميع',
                'edit_memorization' => 'تعديل سجلات التسميع',
                'delete_memorization' => 'حذف سجلات التسميع',
                'generate_memorization_reports' => 'إنشاء تقارير التسميع',
            ],

            // إدارة الموظفين
            'employees' => [
                'view_employees' => 'عرض الموظفين',
                'create_employees' => 'إضافة موظفين',
                'edit_employees' => 'تعديل بيانات الموظفين',
                'delete_employees' => 'حذف الموظفين',
                'manage_employee_attendance' => 'إدارة حضور الموظفين',
            ],

            // إدارة المالية
            'finance' => [
                'view_finances' => 'عرض البيانات المالية',
                'add_revenues' => 'إضافة إيرادات',
                'edit_revenues' => 'تعديل الإيرادات',
                'delete_revenues' => 'حذف الإيرادات',
                'add_expenses' => 'إضافة مصروفات',
                'edit_expenses' => 'تعديل المصروفات',
                'delete_expenses' => 'حذف المصروفات',
                'manage_salaries' => 'إدارة الرواتب',
                'manage_incentives' => 'إدارة الحوافز',
                'manage_budgets' => 'إدارة الميزانيات',
                'generate_financial_reports' => 'إنشاء تقارير مالية',
            ],

            // التقارير
            'reports' => [
                'view_reports' => 'عرض التقارير',
                'generate_reports' => 'إنشاء تقارير جديدة',
                'export_reports' => 'تصدير التقارير',
                'print_reports' => 'طباعة التقارير',
            ],

            // الإعدادات
            'settings' => [
                'view_settings' => 'عرض الإعدادات',
                'edit_system_settings' => 'تعديل إعدادات النظام',
                'manage_backup' => 'إدارة النسخ الاحتياطي',
                'manage_system_logs' => 'إدارة سجلات النظام',
            ],
        ];

        // إنشاء الصلاحيات في قاعدة البيانات
        $createdCount = 0;
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $name => $arabicName) {
                Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'guard_name' => 'web',
                    ]
                );
                $createdCount++;
            }
            
            // مطبوع لمتابعة تقدم العملية
            $this->command->info("تم إنشاء {$createdCount} صلاحية في مجموعة {$group}");
            $createdCount = 0;
        }
    }
}
