<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrueUnifyIds extends Command
{
    protected $signature = 'true:unify-ids';
    protected $description = 'التوحيد الحقيقي: جعل Teacher ID = User ID لكل معلم';

    public function handle()
    {
        $this->info('🎯 التوحيد الحقيقي للمعرفات');
        $this->info('الهدف: Teacher ID = User ID لكل معلم');
        $this->newLine();

        if (!$this->confirm('هذا سيجعل كل معلم له نفس الرقم في الجدولين. متأكد؟')) {
            return;
        }

        try {
            // إيقاف foreign key checks مؤقتاً
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::beginTransaction();

            $teachers = Teacher::with('user')->get();
            $this->info("📊 معالجة {$teachers->count()} معلم...");
            $this->newLine();

            $successCount = 0;
            $errorCount = 0;

            foreach ($teachers as $teacher) {
                try {
                    $teacherId = $teacher->id;
                    $oldUserId = $teacher->user_id;
                    
                    $this->line("🔧 {$teacher->name}: Teacher[{$teacherId}] ← User[{$oldUserId}]");
                    
                    if ($teacherId === $oldUserId) {
                        $this->line("   ✅ جاهز مسبقاً");
                        $successCount++;
                        continue;
                    }

                    // 1. حفظ بيانات المستخدم
                    $userData = $teacher->user->toArray();
                    
                    // 2. حذف المستخدم القديم
                    $teacher->user->delete();
                    
                    // 3. إنشاء مستخدم جديد بنفس teacher_id
                    unset($userData['id']);
                    $userData['id'] = $teacherId;
                    
                    // التأكد من وجود كلمة مرور
                    if (!isset($userData['password']) || empty($userData['password'])) {
                        $userData['password'] = bcrypt('123456'); // كلمة مرور افتراضية
                    }
                    
                    $newUser = User::create($userData);
                    
                    // 4. تحديث teacher.user_id
                    $teacher->user_id = $teacherId;
                    $teacher->save();
                    
                    // 5. تحديث الجداول المرتبطة
                    $this->updateRelatedRecords($oldUserId, $teacherId);
                    
                    $this->line("   ✅ نجح: الآن Teacher[{$teacherId}] = User[{$teacherId}]");
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $this->error("   ❌ فشل: {$e->getMessage()}");
                    $errorCount++;
                }
            }

            if ($errorCount === 0) {
                DB::commit();
                $this->newLine();
                $this->info("🎉 تم التوحيد بنجاح!");
                $this->info("   ✅ نجح: {$successCount}");
                $this->info("   ❌ فشل: {$errorCount}");
                
                $this->testUnification();
            } else {
                DB::rollBack();
                $this->error("❌ فشل التوحيد - تم التراجع");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ خطأ عام: {$e->getMessage()}");
        } finally {
            // إعادة تفعيل foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function updateRelatedRecords($oldUserId, $newUserId)
    {
        // تحديث جلسات التسميع (تستخدم teacher_id)
        DB::table('recitation_sessions')
            ->where('teacher_id', $oldUserId)
            ->update(['teacher_id' => $newUserId]);

        // تحديث رسائل الواتساب (تحقق من وجود العمود أولاً)
        if (Schema::hasColumn('whatsapp_messages', 'user_id')) {
            DB::table('whatsapp_messages')
                ->where('user_id', $oldUserId)
                ->update(['user_id' => $newUserId]);
        }

        // ملاحظة: جدول attendances لا يحتوي على user_id، لذا لا نحدثه
    }

    private function testUnification()
    {
        $this->newLine();
        $this->info("🧪 اختبار التوحيد:");
        
        $teachers = Teacher::limit(5)->get();
        $allUnified = true;
        
        foreach ($teachers as $teacher) {
            $isUnified = ($teacher->id === $teacher->user_id);
            $status = $isUnified ? '✅' : '❌';
            $this->line("   {$status} {$teacher->name}: Teacher[{$teacher->id}] = User[{$teacher->user_id}]");
            
            if (!$isUnified) {
                $allUnified = false;
            }
        }
        
        if ($allUnified) {
            $this->newLine();
            $this->info("🎉 التوحيد مكتمل!");
            $this->info("🎯 الآن Frontend يمكنه استخدام:");
            $this->info("   teacher_id: user?.id  // نفس الرقم دائماً!");
        }
    }
}
