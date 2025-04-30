<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * ربط الأدوار بالصلاحيات المناسبة
     */
    public function run(): void
    {
        // تعريف صلاحيات كل دور
        $rolePermissions = [
            // مدير النظام - لديه كل الصلاحيات
            'super_admin' => [], // سيتم إعطاؤه كل الصلاحيات لاحقاً

            // مدير المركز
            'admin' => [
                // إدارة المستخدمين (عدا الأدوار)
                'view_users', 'create_users', 'edit_users', 'activate_users', 'deactivate_users', 'view_roles',
                // كاملة على الحلقات
                'view_circles', 'create_circles', 'edit_circles', 'delete_circles', 'manage_circle_students',
                'view_circle_reports', 'view_circle_requests', 'approve_circle_requests', 'reject_circle_requests',
                // كاملة على المساجد
                'view_mosques', 'create_mosques', 'edit_mosques', 'delete_mosques', 'assign_circles_to_mosques',
                // كاملة على الطلاب
                'view_students', 'create_students', 'edit_students', 'delete_students', 'view_student_progress', 'manage_student_attendance',
                // كاملة على المعلمين
                'view_teachers', 'create_teachers', 'edit_teachers', 'delete_teachers', 'assign_circles_to_teachers', 'view_teacher_performance',
                // كاملة على الحضور
                'view_attendance', 'record_attendance', 'edit_attendance', 'delete_attendance', 'generate_attendance_reports',
                // كاملة على التسميع
                'view_memorization', 'record_memorization', 'edit_memorization', 'delete_memorization', 'generate_memorization_reports',
                // كاملة على الموظفين
                'view_employees', 'create_employees', 'edit_employees', 'delete_employees', 'manage_employee_attendance',
                // كاملة على المالية
                'view_finances', 'add_revenues', 'edit_revenues', 'delete_revenues', 'add_expenses',
                'edit_expenses', 'delete_expenses', 'manage_salaries', 'manage_incentives', 'manage_budgets', 
                'generate_financial_reports',
                // كاملة على التقارير
                'view_reports', 'generate_reports', 'export_reports', 'print_reports',
                // محدودة على الإعدادات
                'view_settings',
            ],

            // المشرف التربوي
            'supervisor' => [
                // محدودة على المستخدمين
                'view_users',
                // محدودة على الحلقات
                'view_circles', 'view_circle_reports', 'view_circle_requests',
                // محدودة على المساجد
                'view_mosques',
                // محدودة على الطلاب
                'view_students', 'view_student_progress',
                // محدودة على المعلمين
                'view_teachers', 'view_teacher_performance',
                // محدودة على الحضور
                'view_attendance', 'record_attendance', 'generate_attendance_reports',
                // محدودة على التسميع
                'view_memorization', 'record_memorization', 'generate_memorization_reports',
                // محدودة على التقارير
                'view_reports', 'generate_reports', 'export_reports', 'print_reports',
            ],

            // المعلم
            'teacher' => [
                // محدودة على الحلقات (الخاصة به فقط)
                'view_circles', 'view_circle_reports',
                // محدودة على الطلاب (طلاب حلقته فقط)
                'view_students', 'view_student_progress', 'manage_student_attendance',
                // محدودة على الحضور (حلقته فقط)
                'view_attendance', 'record_attendance', 'edit_attendance',
                // محدودة على التسميع (حلقته فقط)
                'view_memorization', 'record_memorization', 'edit_memorization',
                // محدودة على التقارير (تقارير حلقته فقط)
                'view_reports',
            ],

            // الموظف الإداري
            'staff' => [
                // محدودة على الحلقات
                'view_circles',
                // محدودة على المساجد
                'view_mosques',
                // محدودة على الطلاب
                'view_students',
                // محدودة على المعلمين
                'view_teachers',
                // محدودة على التقارير
                'view_reports',
            ],

            // الطالب
            'student' => [
                // صلاحيات خاصة بالطالب
                'view_own_progress',
                'view_own_attendance',
            ],
        ];

        // ربط الصلاحيات بالأدوار
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName);

            if ($roleName === 'super_admin') {
                // إعطاء مدير النظام جميع الصلاحيات
                $allPermissions = Permission::all();
                $role->syncPermissions($allPermissions);
                $this->command->info("تم إعطاء دور مدير النظام {$allPermissions->count()} صلاحية");
            } else {
                // إعطاء الأدوار الأخرى الصلاحيات المحددة لها
                $role->syncPermissions($permissions);
                $this->command->info("تم إعطاء دور {$roleName} " . count($permissions) . " صلاحية");
            }
        }
    }
}
