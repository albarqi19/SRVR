<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // إنشاء الأدوار والصلاحيات
        $this->call([
            RoleSeeder::class,           // إنشاء الأدوار
            PermissionSeeder::class,      // إنشاء الصلاحيات
            RolePermissionSeeder::class,  // ربط الأدوار بالصلاحيات
        ]);

        // إنشاء مستخدم مدير النظام
        $admin = User::firstOrCreate(
            ['email' => 'admin@quran-center.com'],
            [
                'name' => 'مدير النظام',
                'username' => 'admin',
                'password' => Hash::make('password123'), // تغيير كلمة المرور إلى password123
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // إسناد دور مدير النظام للمستخدم
        $admin->assignRole('super_admin');
        
        // إنشاء مستخدم تجريبي لكل دور
        $roles = ['admin', 'supervisor', 'teacher', 'staff', 'student'];
        foreach ($roles as $role) {
            $user = User::firstOrCreate(
                ['email' => $role . '@quran-center.com'],
                [
                    'name' => 'مستخدم ' . $role,
                    'username' => $role,
                    'password' => Hash::make($role . '123'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );
            
            $user->assignRole($role);
        }
    }
}
