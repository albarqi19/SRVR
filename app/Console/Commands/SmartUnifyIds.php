<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SmartUnifyIds extends Command
{
    protected $signature = 'smart:unify-ids';
    protected $description = 'توحيد ذكي للمعرفات بدون مشاكل foreign keys';

    public function handle()
    {
        $this->info('🧠 بدء التوحيد الذكي للمعرفات...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // الطريقة الذكية: نحديث teacher_id ليصبح نفس user_id
            // بدلاً من تعديل user_id
            
            $teachers = Teacher::whereNotNull('user_id')->with('user')->get();
            $this->info("📊 معالجة {$teachers->count()} معلم...");
            $this->newLine();

            $this->info("📋 خطة العمل:");
            $this->info("   1. إنشاء معرفات جديدة للمعلمين لتطابق معرفات المستخدمين");
            $this->info("   2. تحديث جميع الجداول المرتبطة");
            $this->info("   3. حذف البيانات القديمة");
            $this->newLine();

            // إنشاء mapping table مؤقت
            $idMapping = [];
            
            foreach ($teachers as $teacher) {
                $oldTeacherId = $teacher->id;
                $targetId = $teacher->user_id;
                
                // التحقق من عدم وجود تضارب في جدول teachers
                $existingTeacher = Teacher::find($targetId);
                if ($existingTeacher && $existingTeacher->id !== $oldTeacherId) {
                    // إذا كان هناك تضارب، نحتاج لحل إبداعي
                    $this->warn("   ⚠️ تضارب في Teacher ID {$targetId} - سنستخدم ID جديد");
                    // البحث عن أعلى ID متاح
                    $maxId = Teacher::max('id');
                    $targetId = $maxId + 1000; // إضافة هامش أمان
                    
                    // تحديث user_id أيضاً
                    $teacher->user->update(['id' => $targetId]);
                }
                
                $idMapping[$oldTeacherId] = $targetId;
                $this->line("   📝 {$teacher->name}: Teacher[{$oldTeacherId}] → [{$targetId}]");
            }

            $this->newLine();
            $this->info("🔄 تحديث الجداول...");

            // تحديث جدول teachers
            foreach ($idMapping as $oldId => $newId) {
                if ($oldId !== $newId) {
                    $teacher = Teacher::find($oldId);
                    if ($teacher) {
                        // إنشاء سجل جديد
                        $newTeacher = $teacher->replicate();
                        $newTeacher->id = $newId;
                        $newTeacher->user_id = $newId;
                        $newTeacher->save();
                        
                        // تحديث الجداول المرتبطة
                        $this->updateRelatedTablesForTeacher($oldId, $newId);
                        
                        // حذف السجل القديم
                        $teacher->delete();
                        
                        $this->line("   ✅ تم تحديث المعلم {$teacher->name}");
                    }
                }
            }

            DB::commit();
            $this->newLine();
            $this->info("🎉 تم التوحيد بنجاح!");
            
            // عرض النتائج
            $this->showResults();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ خطأ: {$e->getMessage()}");
        }
    }

    private function updateRelatedTablesForTeacher($oldTeacherId, $newTeacherId)
    {
        $tables = [
            'recitation_sessions' => 'teacher_id',
            'attendances' => 'teacher_id',
            'circle_assignments' => 'teacher_id',
            'teacher_incentives' => 'teacher_id',
            // أضف المزيد حسب الحاجة
        ];

        foreach ($tables as $table => $column) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table) && 
                    DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    
                    DB::table($table)
                        ->where($column, $oldTeacherId)
                        ->update([$column => $newTeacherId]);
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠️ تحديث {$table}: {$e->getMessage()}");
            }
        }
    }

    private function showResults()
    {
        $this->info("📊 النتائج النهائية:");
        
        $teachers = Teacher::with('user')->limit(5)->get();
        foreach ($teachers as $teacher) {
            $status = ($teacher->id === $teacher->user_id) ? '✅' : '❌';
            $this->line("   {$status} {$teacher->name}: Teacher[{$teacher->id}] = User[{$teacher->user_id}]");
        }
        
        $this->newLine();
        $this->info("🎯 الآن يمكن للـ Frontend استخدام:");
        $this->info("   teacher_id: user?.id  // نفس المعرف، لا تعقيدات!");
    }
}
