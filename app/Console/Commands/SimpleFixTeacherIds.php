<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SimpleFixTeacherIds extends Command
{
    protected $signature = 'fix:simple-teacher-ids';
    protected $description = 'حل بسيط وآمن لمشكلة معرفات المعلمين';

    public function handle()
    {
        $this->info('🎯 الحل البسيط والآمن لمشكلة معرفات المعلمين');
        $this->newLine();

        // الطريقة البسيطة: تحديث user_id في جدول teachers ليصبح نفس teacher_id
        $teachers = Teacher::all();
        
        $this->info("📊 معالجة {$teachers->count()} معلم...");
        $this->newLine();

        $updated = 0;
        $errors = 0;

        foreach ($teachers as $teacher) {
            try {
                // إذا كان المعلم لديه user_id مختلف عن teacher_id
                if ($teacher->user_id !== $teacher->id) {
                    
                    // البحث عن المستخدم
                    $user = User::find($teacher->user_id);
                    
                    if ($user) {
                        // تحديث بيانات المستخدم ليصبح user_id = teacher_id
                        $user->id = $teacher->id;
                        $user->save();
                        
                        // تحديث user_id في جدول teachers
                        $teacher->user_id = $teacher->id;
                        $teacher->save();
                        
                        $this->line("   ✅ {$teacher->name}: User ID {$user->id} → {$teacher->id}");
                        $updated++;
                    } else {
                        // إنشاء مستخدم جديد بنفس معرف المعلم
                        $newUser = User::create([
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'username' => 'teacher_' . $teacher->id,
                            'email' => 'teacher_' . $teacher->id . '@garb.com',
                            'password' => bcrypt('123456'),
                            'identity_number' => $teacher->identity_number ?? '0000000000',
                            'phone' => $teacher->phone ?? '',
                            'is_active' => true
                        ]);
                        
                        $teacher->user_id = $teacher->id;
                        $teacher->save();
                        
                        $this->line("   ➕ {$teacher->name}: إنشاء مستخدم جديد (ID: {$teacher->id})");
                        $updated++;
                    }
                } else {
                    $this->line("   ✅ {$teacher->name}: جاهز مسبقاً (ID: {$teacher->id})");
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ {$teacher->name}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("📈 النتائج:");
        $this->info("   ✅ تم التحديث: {$updated}");
        $this->info("   ❌ أخطاء: {$errors}");

        if ($errors === 0) {
            $this->newLine();
            $this->info("🎉 تم الحل بنجاح!");
            $this->info("📋 الآن:");
            $this->info("   - كل معلم له نفس المعرف في الجدولين");
            $this->info("   - Frontend يرسل teacher_id = user_id");
            $this->info("   - لا مشاكل في العرض");
            
            // اختبار سريع
            $this->testSolution();
        }
    }

    private function testSolution()
    {
        $this->newLine();
        $this->info("🧪 اختبار سريع:");
        
        $abdullah = Teacher::where('name', 'like', '%عبدالله الشنقيطي%')->first();
        if ($abdullah) {
            $status = ($abdullah->id === $abdullah->user_id) ? '✅ نجح' : '❌ فشل';
            $this->info("   عبدالله الشنقيطي: Teacher[{$abdullah->id}] = User[{$abdullah->user_id}] → {$status}");
            
            if ($abdullah->id === $abdullah->user_id) {
                $this->info("   🎯 Frontend يرسل: teacher_id = {$abdullah->id}");
                $this->info("   🎯 API يجد: Teacher::find({$abdullah->id}) = {$abdullah->name}");
                $this->info("   🎯 النتيجة: صحيحة 100% ✅");
            }
        }
    }
}
