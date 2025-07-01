<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UnifyTeacherUserIds extends Command
{
    protected $signature = 'unify:teacher-user-ids';
    protected $description = 'توحيد معرفات المعلمين والمستخدمين لحل المشكلة نهائياً';

    public function handle()
    {
        $this->info('🔧 بدء عملية توحيد معرفات المعلمين والمستخدمين...');
        $this->newLine();

        if (!$this->confirm('هذه العملية ستغير معرفات المستخدمين لتطابق معرفات المعلمين. هل تريد المتابعة؟')) {
            $this->info('تم إلغاء العملية');
            return;
        }

        try {
            DB::beginTransaction();

            // الحصول على جميع المعلمين المرتبطين بمستخدمين
            $teachers = Teacher::whereNotNull('user_id')->with('user')->get();
            
            $this->info("📊 تم العثور على {$teachers->count()} معلم مرتبط بمستخدمين");
            $this->newLine();

            $successCount = 0;
            $errorCount = 0;

            foreach ($teachers as $teacher) {
                try {
                    $oldUserId = $teacher->user_id;
                    $teacherId = $teacher->id;
                    
                    // التحقق من عدم وجود تضارب
                    $existingUser = User::find($teacherId);
                    if ($existingUser && $existingUser->id !== $oldUserId) {
                        $this->warn("   ⚠️ تضارب: User ID {$teacherId} موجود مسبقاً للمستخدم: {$existingUser->name}");
                        continue;
                    }

                    // تحديث معرف المستخدم
                    DB::table('users')
                        ->where('id', $oldUserId)
                        ->update(['id' => $teacherId]);

                    // تحديث جميع الجداول المرتبطة
                    $this->updateRelatedTables($oldUserId, $teacherId);

                    // تحديث user_id في جدول teachers (الآن سيكون نفس teacher_id)
                    $teacher->user_id = $teacherId;
                    $teacher->save();

                    $this->line("   ✅ {$teacher->name}: User ID {$oldUserId} → {$teacherId}");
                    $successCount++;

                } catch (\Exception $e) {
                    $this->error("   ❌ {$teacher->name}: {$e->getMessage()}");
                    $errorCount++;
                }
            }

            if ($errorCount === 0) {
                DB::commit();
                $this->newLine();
                $this->info("🎉 تم توحيد المعرفات بنجاح!");
                $this->info("   ✅ نجح: {$successCount}");
                $this->info("   ❌ فشل: {$errorCount}");
                
                $this->newLine();
                $this->info("📋 الآن:");
                $this->info("   - كل معلم له نفس المعرف في جدولي teachers و users");
                $this->info("   - Frontend يمكنه استخدام teacher_id مباشرة");
                $this->info("   - لا حاجة لتحويلات معقدة");
            } else {
                DB::rollBack();
                $this->error("❌ تم إلغاء العملية بسبب وجود أخطاء");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ خطأ عام: {$e->getMessage()}");
        }
    }

    private function updateRelatedTables($oldUserId, $newUserId)
    {
        // قائمة الجداول التي تحتوي على user_id أو teacher_id
        $tables = [
            // جداول المستخدمين
            'recitation_sessions' => 'teacher_id',
            'attendances' => 'user_id',
            'whatsapp_messages' => 'user_id',
            // أضف جداول أخرى حسب الحاجة
        ];

        foreach ($tables as $table => $column) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table) && 
                    DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    
                    DB::table($table)
                        ->where($column, $oldUserId)
                        ->update([$column => $newUserId]);
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠️ تحديث {$table}: {$e->getMessage()}");
            }
        }
    }
}
