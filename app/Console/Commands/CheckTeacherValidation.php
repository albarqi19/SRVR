<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTeacherValidation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:teacher-validation {teacher_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص صحة بيانات المعلم للتحقق من مشكلة validation.exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teacherId = $this->argument('teacher_id') ?? 89;
        
        $this->info('🔍 فحص بيانات المعلم...');
        $this->newLine();

        // 1. التحقق من وجود المعلم في جدول teachers
        $this->info("1️⃣ البحث عن المعلم ID: {$teacherId} في جدول teachers...");
        
        $teacher = DB::table('teachers')->where('id', $teacherId)->first();
        
        if ($teacher) {
            $this->info('✅ المعلم موجود في جدول teachers:');
            $this->info("   الاسم: {$teacher->name}");
            $this->info("   الهاتف: " . ($teacher->phone ?? 'غير محدد'));
            if (isset($teacher->mosque_id)) {
                $this->info("   المسجد ID: {$teacher->mosque_id}");
            }
        } else {
            $this->error('❌ المعلم غير موجود في جدول teachers');
        }
        
        $this->newLine();

        // 2. التحقق من وجود المعلم في جدول users
        $this->info("2️⃣ البحث عن المعلم في جدول users...");
        
        if ($teacher && isset($teacher->user_id)) {
            $user = DB::table('users')->where('id', $teacher->user_id)->first();
            if ($user) {
                $this->info('✅ المستخدم المرتبط موجود:');
                $this->info("   الاسم: {$user->name}");
                $this->info("   البريد الإلكتروني: {$user->email}");
            } else {
                $this->warn('⚠️ المستخدم المرتبط غير موجود');
            }
        } else {
            $this->warn('⚠️ لا يوجد user_id مرتبط بالمعلم');
        }
        
        $this->newLine();

        // 3. عرض جميع المعلمين المتاحين
        $this->info('3️⃣ عرض أول 10 معلمين متاحين:');
        
        $teachers = DB::table('teachers')
            ->select('id', 'name', 'phone', 'mosque_id')
            ->limit(10)
            ->get();

        if ($teachers->count() > 0) {
            $this->table(
                ['ID', 'الاسم', 'الهاتف', 'المسجد ID'],
                $teachers->map(function ($t) {
                    return [
                        $t->id,
                        $t->name,
                        $t->phone ?? 'غير محدد',
                        $t->mosque_id ?? 'غير محدد'
                    ];
                })->toArray()
            );
        } else {
            $this->warn('⚠️ لا يوجد معلمين في قاعدة البيانات');
        }
        
        $this->newLine();

        // 4. التحقق من الحلقات المرتبطة بالمعلم
        if ($teacher) {
            $this->info('4️⃣ التحقق من الحلقات المرتبطة بالمعلم...');
            
            $circles = DB::table('quran_circles')
                ->where('teacher_id', $teacherId)
                ->select('id', 'name')
                ->get();
                
            if ($circles->count() > 0) {
                $this->info("✅ المعلم مرتبط بـ {$circles->count()} حلقة:");
                foreach ($circles as $circle) {
                    $this->info("   - {$circle->name} (ID: {$circle->id})");
                }
            } else {
                $this->warn('⚠️ المعلم غير مرتبط بأي حلقة');
            }
        }
        
        $this->newLine();

        // 5. اقتراح حلول
        $this->info('5️⃣ اقتراحات الحلول:');
        
        if (!$teacher) {
            $this->warn('💡 يجب استخدام معلم موجود في قاعدة البيانات');
            $this->info('   - تحقق من قائمة المعلمين أعلاه');
            $this->info('   - أو أنشئ معلم جديد باستخدام: php artisan make:teacher');
        } else {
            $this->info('💡 المعلم موجود، تحقق من:');
            $this->info('   - أن المعلم مرتبط بحلقة نشطة');
            $this->info('   - أن البيانات المرسلة صحيحة');
        }
        
        // 6. اختبار API validation
        $this->newLine();
        $this->info('6️⃣ اختبار بيانات API للمعلم...');
        
        if ($teacher) {
            $this->info('✅ teacher_id صالح للاستخدام في API');
        } else {
            $validTeacher = DB::table('teachers')->first();
            if ($validTeacher) {
                $this->info("💡 يمكنك استخدام teacher_id: {$validTeacher->id} ({$validTeacher->name})");
            }
        }
        
        $this->newLine();
        $this->info('🎉 انتهى الفحص');
    }
}
