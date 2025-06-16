<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// إنشاء تطبيق Laravel
$app = new Application(realpath(__DIR__));
$app->singleton('app', Application::class);

// تحميل الإعدادات
$app['config'] = new \Illuminate\Config\Repository([
    'database' => [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'database' => 'garb_project',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]
        ]
    ]
]);

// تسجيل DatabaseManager
$app->singleton('db', function($app) {
    return new \Illuminate\Database\DatabaseManager($app, new \Illuminate\Database\Connectors\ConnectionFactory($app));
});

try {
    echo "=== فحص بنية جدول quran_circles ===\n";
    
    // الحصول على قائمة الأعمدة
    $columns = Schema::getColumnListing('quran_circles');
    
    echo "أعمدة جدول quran_circles:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    echo "\n=== عينة من البيانات ===\n";
    
    // الحصول على أول 3 سجلات
    $circles = DB::table('quran_circles')->take(3)->get();
    
    foreach ($circles as $circle) {
        echo "ID: {$circle->id}\n";
        echo "Name: {$circle->name}\n";
        
        // البحث عن أعمدة تحتوي على teacher
        foreach ($columns as $column) {
            if (str_contains(strtolower($column), 'teacher')) {
                echo "Teacher Field ({$column}): " . ($circle->$column ?? 'NULL') . "\n";
            }
        }
        
        echo "---\n";
    }
    
    echo "\n=== فحص جدول students للعلاقة ===\n";
    
    $studentColumns = Schema::getColumnListing('students');
    echo "أعمدة جدول students المرتبطة بالحلقات:\n";
    foreach ($studentColumns as $column) {
        if (str_contains(strtolower($column), 'circle') || str_contains(strtolower($column), 'teacher')) {
            echo "- {$column}\n";
        }
    }
    
    // عينة من الطلاب
    echo "\nعينة من الطلاب:\n";
    $students = DB::table('students')->take(2)->get();
    foreach ($students as $student) {
        echo "Student: {$student->name}\n";
        echo "Circle ID: " . ($student->quran_circle_id ?? 'NULL') . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
