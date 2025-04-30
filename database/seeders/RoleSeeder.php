<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إضافة الأدوار الرئيسية للنظام
     */
    public function run(): void
    {
        // إنشاء الأدوار الرئيسية في النظام
        $roles = [
            [
                'name' => 'super_admin',
                'arabic_name' => 'مدير النظام',
                'description' => 'لديه جميع الصلاحيات في النظام',
            ],
            [
                'name' => 'admin',
                'arabic_name' => 'مدير المركز',
                'description' => 'صلاحيات إدارية واسعة لإدارة المركز',
            ],
            [
                'name' => 'supervisor',
                'arabic_name' => 'المشرف التربوي',
                'description' => 'الإشراف على الحلقات والمعلمين',
            ],
            [
                'name' => 'teacher',
                'arabic_name' => 'المعلم',
                'description' => 'إدارة الحلقة وطلابها',
            ],
            [
                'name' => 'staff',
                'arabic_name' => 'الموظف الإداري',
                'description' => 'مهام إدارية محددة',
            ],
            [
                'name' => 'student',
                'arabic_name' => 'الطالب',
                'description' => 'الاطلاع على منهجه وإنجازاته',
            ],
        ];

        // إنشاء كل دور في قاعدة البيانات
        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'guard_name' => 'web',
                ]
            );
            
            // مطبوع لمتابعة تقدم العملية
            $this->command->info('تم إنشاء الدور: ' . $role['arabic_name']);
        }
    }
}
