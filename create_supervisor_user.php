<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creating supervisor user...\n";

try {
    // البحث عن مستخدم موجود
    $user = App\Models\User::where('email', 'supervisor@test.com')->first();
    
    if ($user) {
        echo "Supervisor user already exists with ID: " . $user->id . "\n";
    } else {
        // إنشاء مستخدم جديد
        $user = App\Models\User::create([
            'name' => 'Test Supervisor',
            'email' => 'supervisor@test.com',
            'password' => bcrypt('password123'),
            'phone' => '0501234567'
        ]);
        
        echo "Created supervisor user with ID: " . $user->id . "\n";
    }
    
    // تعيين دور المشرف
    if (!$user->hasRole('supervisor')) {
        $user->assignRole('supervisor');
        echo "Assigned supervisor role\n";
    } else {
        echo "User already has supervisor role\n";
    }
    
    // إنشاء تعيين مشرف على حلقة
    $circle = App\Models\QuranCircle::first();
    if ($circle) {
        $supervisor = App\Models\CircleSupervisor::where('supervisor_id', $user->id)
            ->where('quran_circle_id', $circle->id)
            ->first();
            
        if (!$supervisor) {
            App\Models\CircleSupervisor::create([
                'supervisor_id' => $user->id,
                'quran_circle_id' => $circle->id,
                'assignment_date' => now(),
                'is_active' => true
            ]);
            echo "Created circle supervisor assignment\n";
        } else {
            echo "Circle supervisor assignment already exists\n";
        }
    }
    
    echo "Setup completed successfully!\n";
    echo "User credentials:\n";
    echo "Email: supervisor@test.com\n";
    echo "Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
