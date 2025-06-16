<?php

require __DIR__ . '/vendor/autoload.php';

// استدعاء ملف التطبيق
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== فحص المشرفين في النظام ===\n\n";

try {
    // التحقق من جدول المستخدمين الذين لديهم دور مشرف
    echo "1. البحث عن المستخدمين الذين لديهم دور 'supervisor':\n";
    echo str_repeat("-", 50) . "\n";
    
    $supervisors = \App\Models\User::role('supervisor')->get();
    
    if ($supervisors->count() > 0) {
        foreach ($supervisors as $supervisor) {
            echo "ID: {$supervisor->id}\n";
            echo "الاسم: {$supervisor->name}\n";
            echo "البريد الإلكتروني: {$supervisor->email}\n";
            echo "اسم المستخدم: " . ($supervisor->username ?? 'غير محدد') . "\n";
            echo "نشط: " . ($supervisor->is_active ? 'نعم' : 'لا') . "\n";
            echo "تاريخ الإنشاء: {$supervisor->created_at}\n";
            echo str_repeat("-", 30) . "\n";
        }
        echo "إجمالي عدد المشرفين: " . $supervisors->count() . "\n\n";
    } else {
        echo "❌ لا يوجد مستخدمين لديهم دور 'supervisor'\n\n";
    }
    
    // التحقق من تعيينات المشرفين على الحلقات
    echo "2. فحص تعيينات المشرفين على الحلقات القرآنية:\n";
    echo str_repeat("-", 50) . "\n";
    
    if (Schema::hasTable('circle_supervisors')) {
        $assignments = \App\Models\CircleSupervisor::with(['supervisor', 'quranCircle', 'quranCircle.mosque'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($assignments->count() > 0) {
            foreach ($assignments as $assignment) {
                echo "ID التعيين: {$assignment->id}\n";
                echo "المشرف: " . ($assignment->supervisor->name ?? 'غير محدد') . " (ID: {$assignment->supervisor_id})\n";
                echo "الحلقة: " . ($assignment->quranCircle->name ?? 'غير محدد') . " (ID: {$assignment->quran_circle_id})\n";
                echo "المسجد: " . ($assignment->quranCircle->mosque->name ?? 'غير محدد') . "\n";
                echo "تاريخ التكليف: {$assignment->assignment_date}\n";
                echo "تاريخ الإنتهاء: " . ($assignment->end_date ?? 'مستمر') . "\n";
                echo "نشط: " . ($assignment->is_active ? 'نعم' : 'لا') . "\n";
                echo "ملاحظات: " . ($assignment->notes ?? 'لا توجد') . "\n";
                echo str_repeat("-", 30) . "\n";
            }
            echo "إجمالي عدد التعيينات: " . $assignments->count() . "\n\n";
        } else {
            echo "❌ لا توجد تعيينات مشرفين على الحلقات\n\n";
        }
    } else {
        echo "❌ جدول circle_supervisors غير موجود\n\n";
    }
    
    // التحقق من الحلقات التي لديها مشرف مباشر
    echo "3. فحص الحلقات التي لديها مشرف مباشر (supervisor_id):\n";
    echo str_repeat("-", 50) . "\n";
    
    $circlesWithSupervisor = \App\Models\QuranCircle::with(['supervisor', 'mosque'])
        ->whereNotNull('supervisor_id')
        ->get();
    
    if ($circlesWithSupervisor->count() > 0) {
        foreach ($circlesWithSupervisor as $circle) {
            echo "ID الحلقة: {$circle->id}\n";
            echo "اسم الحلقة: {$circle->name}\n";
            echo "المسجد: " . ($circle->mosque->name ?? 'غير محدد') . "\n";
            echo "نوع الحلقة: {$circle->circle_type}\n";
            echo "المشرف: " . ($circle->supervisor->name ?? 'غير محدد') . " (ID: {$circle->supervisor_id})\n";
            echo str_repeat("-", 30) . "\n";
        }
        echo "إجمالي الحلقات التي لديها مشرف: " . $circlesWithSupervisor->count() . "\n\n";
    } else {
        echo "❌ لا توجد حلقات لديها مشرف مباشر\n\n";
    }
    
    // التحقق من جميع الأدوار المتاحة
    echo "4. جميع الأدوار المتاحة في النظام:\n";
    echo str_repeat("-", 50) . "\n";
    
    if (Schema::hasTable('roles')) {
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            $usersCount = $role->users()->count();
            echo "- {$role->name} (عدد المستخدمين: {$usersCount})\n";
        }
        echo "\n";
    } else {
        echo "❌ جدول roles غير موجود\n\n";
    }
    
    // إحصائيات عامة
    echo "5. إحصائيات عامة:\n";
    echo str_repeat("-", 50) . "\n";
    echo "إجمالي المستخدمين: " . \App\Models\User::count() . "\n";
    echo "إجمالي الحلقات القرآنية: " . \App\Models\QuranCircle::count() . "\n";
    echo "الحلقات النشطة: " . \App\Models\QuranCircle::where('circle_status', 'نشطة')->count() . "\n";
    
    if (Schema::hasTable('circle_supervisors')) {
        echo "التعيينات النشطة: " . \App\Models\CircleSupervisor::where('is_active', true)->count() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "التفاصيل: " . $e->getFile() . " على السطر " . $e->getLine() . "\n";
}

echo "\n=== انتهى الفحص ===\n";
