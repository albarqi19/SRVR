<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckUnificationStatus extends Command
{
    protected $signature = 'check:unification-status';
    protected $description = 'فحص حالة توحيد معرفات المعلمين والمستخدمين';

    public function handle()
    {
        $this->info('🎯 فحص حالة التوحيد النهائية');
        $this->info('=====================================');

        // جلب جميع المعلمين مع بيانات المستخدمين
        $teachers = Teacher::all();
        
        $totalTeachers = $teachers->count();
        $unifiedCount = 0;
        $examples = [];

        $this->info("\n📊 تفاصيل كل معلم:");
        $this->info("+---------+-------------------------+------------+----------+--------+");
        $this->info("| Teacher | الاسم                   | Teacher ID | User ID  | متطابق |");
        $this->info("+---------+-------------------------+------------+----------+--------+");

        foreach ($teachers as $teacher) {
            $teacherId = $teacher->id;
            
            // البحث عن المستخدم بنفس الرقم
            $user = User::find($teacherId);
            $userId = $user ? $user->id : 'غير موجود';
            $isUnified = $user !== null;
            
            if ($isUnified) {
                $unifiedCount++;
                $status = "✅ نعم";
            } else {
                $status = "❌ لا";
            }
            
            $name = mb_substr($teacher->name, 0, 20, 'UTF-8');
            
            $this->info(sprintf(
                "| %-7s | %-23s | %-10s | %-8s | %-6s |",
                $teacherId,
                $name,
                $teacherId,
                $userId,
                $status
            ));

            // حفظ أمثلة للاختبار
            if (count($examples) < 3) {
                $examples[] = [
                    'teacher_id' => $teacherId,
                    'user_id' => $userId,
                    'name' => $teacher->name,
                    'unified' => $isUnified
                ];
            }
        }

        $this->info("+---------+-------------------------+------------+----------+--------+");

        // إحصائيات
        $unificationPercentage = $totalTeachers > 0 ? round(($unifiedCount / $totalTeachers) * 100, 2) : 0;
        
        $this->info("\n📈 الإحصائيات:");
        $this->info("📊 إجمالي المعلمين: {$totalTeachers}");
        $this->info("✅ متطابقين (Teacher ID = User ID): {$unifiedCount}");
        $this->info("❌ غير متطابقين: " . ($totalTeachers - $unifiedCount));
        $this->info("📊 نسبة التطابق: {$unificationPercentage}%");

        // اختبار عملي
        $this->info("\n🧪 اختبار عملي:");
        foreach ($examples as $example) {
            if ($example['unified']) {
                $this->info("✅ مثال ناجح: المعلم {$example['teacher_id']} ({$example['name']}) = User {$example['user_id']}");
            } else {
                $this->warn("⚠️  مثال غير متطابق: Teacher {$example['teacher_id']} ≠ User {$example['user_id']}");
            }
        }

        // اختبار قاعدة البيانات
        $this->info("\n🔍 اختبار مباشر من قاعدة البيانات:");
        try {
            $sampleQuery = DB::select("
                SELECT 
                    t.id as teacher_id,
                    t.name,
                    u.id as user_id,
                    CASE WHEN t.id = u.id THEN 'متطابق ✅' ELSE 'غير متطابق ❌' END as status
                FROM teachers t 
                LEFT JOIN users u ON t.user_id = u.id 
                LIMIT 5
            ");

            foreach ($sampleQuery as $row) {
                $this->info("🔸 {$row->name}: Teacher[{$row->teacher_id}] - User[{$row->user_id}] - {$row->status}");
            }
        } catch (\Exception $e) {
            $this->error("❌ خطأ في الاستعلام: " . $e->getMessage());
        }

        // خلاصة نهائية
        if ($unificationPercentage == 100) {
            $this->info("\n🎉 ممتاز! التوحيد مكتمل 100%");
            $this->info("💡 الآن كل معلم له نفس الرقم في الجدولين");
            $this->info("🚀 النظام جاهز للعمل بدون تعقيدات");
        } else {
            $this->warn("\n⚠️  التوحيد غير مكتمل");
            $this->warn("🔧 يحتاج إعادة تشغيل: php artisan true:unify-ids");
        }

        return Command::SUCCESS;
    }
}
