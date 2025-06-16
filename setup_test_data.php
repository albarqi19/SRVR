<?php

require_once 'vendor/autoload.php';

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "تحقق من الاتصال بقاعدة البيانات...\n";

try {
    // التحقق من الاتصال
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✅ الاتصال بقاعدة البيانات ناجح\n\n";
    
    // فحص الجداول
    echo "فحص الجداول:\n";
    
    $teachersCount = \Illuminate\Support\Facades\DB::table('teachers')->count();
    echo "عدد المعلمين: {$teachersCount}\n";
    
    $studentsCount = \Illuminate\Support\Facades\DB::table('students')->count();
    echo "عدد الطلاب: {$studentsCount}\n";
    
    $circlesCount = \Illuminate\Support\Facades\DB::table('quran_circles')->count();
    echo "عدد الحلقات: {$circlesCount}\n";
    
    $mosquesCount = \Illuminate\Support\Facades\DB::table('mosques')->count();
    echo "عدد المساجد: {$mosquesCount}\n";
    
    // إذا لم تكن هناك بيانات، أنشئ بيانات تجريبية
    if ($teachersCount == 0) {
        echo "\n⚠️ لا توجد بيانات. سأنشئ بيانات تجريبية...\n";
        
        // إنشاء مسجد تجريبي
        $mosqueId = \Illuminate\Support\Facades\DB::table('mosques')->insertGetId([
            'name' => 'مسجد التجريب',
            'neighborhood' => 'حي التجريب',
            'street' => 'شارع التجريب',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إنشاء حلقة تجريبية
        $circleId = \Illuminate\Support\Facades\DB::table('quran_circles')->insertGetId([
            'name' => 'حلقة التجريب',
            'mosque_id' => $mosqueId,
            'circle_type' => 'حلقة جماعية',
            'circle_status' => 'نشطة',
            'time_period' => 'عصر',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إنشاء معلم تجريبي
        $teacherId = \Illuminate\Support\Facades\DB::table('teachers')->insertGetId([
            'name' => 'أحمد محمد (تجريبي)',
            'identity_number' => '1234567890',
            'phone' => '0501234567',
            'mosque_id' => $mosqueId,
            'quran_circle_id' => $circleId,
            'job_title' => 'معلم',
            'task_type' => 'معلم بمكافأة',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إنشاء حلقة فرعية
        $circleGroupId = \Illuminate\Support\Facades\DB::table('circle_groups')->insertGetId([
            'name' => 'المجموعة الأولى',
            'quran_circle_id' => $circleId,
            'teacher_id' => $teacherId,
            'status' => 'نشطة',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // إنشاء طلاب تجريبيين
        for ($i = 1; $i <= 5; $i++) {
            \Illuminate\Support\Facades\DB::table('students')->insert([
                'name' => "الطالب التجريبي {$i}",
                'identity_number' => "987654321{$i}",
                'phone' => "05012345{$i}{$i}",
                'quran_circle_id' => $circleId,
                'mosque_id' => $mosqueId,
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'birth_date' => now()->subYears(10 + $i),
                'enrollment_date' => now()->subMonths($i),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // إنشاء طلاب للحلقة الفرعية
        for ($i = 6; $i <= 8; $i++) {
            \Illuminate\Support\Facades\DB::table('students')->insert([
                'name' => "طالب المجموعة {$i}",
                'identity_number' => "987654321{$i}",
                'phone' => "05012345{$i}{$i}",
                'quran_circle_id' => $circleId,
                'circle_group_id' => $circleGroupId,
                'mosque_id' => $mosqueId,
                'gender' => $i % 2 == 0 ? 'female' : 'male',
                'birth_date' => now()->subYears(8 + $i),
                'enrollment_date' => now()->subMonths($i),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        echo "✅ تم إنشاء البيانات التجريبية:\n";
        echo "  - معلم: أحمد محمد (ID: {$teacherId})\n";
        echo "  - مسجد: مسجد التجريب (ID: {$mosqueId})\n";
        echo "  - حلقة: حلقة التجريب (ID: {$circleId})\n";
        echo "  - حلقة فرعية: المجموعة الأولى (ID: {$circleGroupId})\n";
        echo "  - 8 طلاب (5 في الحلقة الأساسية + 3 في الحلقة الفرعية)\n\n";
        
        echo "🌐 يمكنك الآن اختبار APIs التالية:\n";
        echo "GET /api/teachers/{$teacherId}/students\n";
        echo "GET /api/teachers/{$teacherId}/mosques/{$mosqueId}/students\n";
    } else {
        // عرض أول معلم
        $teacher = \Illuminate\Support\Facades\DB::table('teachers')->first();
        if ($teacher) {
            echo "\n📋 معلم متاح للاختبار:\n";
            echo "ID: {$teacher->id}\n";
            echo "الاسم: {$teacher->name}\n";
            echo "المسجد ID: {$teacher->mosque_id}\n";
            echo "الحلقة ID: {$teacher->quran_circle_id}\n\n";
            
            echo "🌐 URLs للاختبار:\n";
            echo "GET /api/teachers/{$teacher->id}/students\n";
            if ($teacher->mosque_id) {
                echo "GET /api/teachers/{$teacher->id}/mosques/{$teacher->mosque_id}/students\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
