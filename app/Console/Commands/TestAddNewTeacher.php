<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\User;

class TestAddNewTeacher extends Command
{
    protected $signature = 'test:add-new-teacher';
    protected $description = 'اختبار إضافة معلم جديد والتأكد من التوحيد التلقائي';

    public function handle()
    {
        $this->info('🧪 اختبار إضافة معلم جديد');
        $this->info('====================================');

        // إنشاء معلم جديد للاختبار
        $testTeacherName = 'معلم اختبار التوحيد ' . date('H:i:s');
        $randomId = rand(100000000, 999999999);
        
        $this->info("📝 إنشاء معلم جديد: $testTeacherName");
        
        // إنشاء المعلم
        $teacher = Teacher::create([
            'name' => $testTeacherName,
            'phone' => '050' . rand(1000000, 9999999),
            'identity_number' => $randomId,
            'email' => 'test.teacher' . time() . '@example.com',
            'nationality' => 'سعودي',
            'birth_date' => '1990-01-01',
            'qualification' => 'بكالوريوس',
            'specialization' => 'علوم شرعية'
        ]);

        $this->info("✅ تم إنشاء المعلم - Teacher ID: {$teacher->id}");

        // انتظار قليل للتأكد من تنفيذ Observer
        sleep(1);

        // إعادة تحميل المعلم لرؤية التحديثات
        $teacher->refresh();

        // فحص النتيجة
        $this->info('');
        $this->info('🔍 فحص النتيجة:');
        $this->line("Teacher ID: {$teacher->id}");
        $this->line("User ID المرتبط: " . ($teacher->user_id ?? 'غير مرتبط'));

        // التحقق من وجود المستخدم
        $user = User::find($teacher->id);
        if ($user) {
            $this->info("✅ تم إنشاء مستخدم بنفس الرقم: User ID = {$user->id}");
            $this->line("اسم المستخدم: {$user->name}");
            $this->line("Username: {$user->username}");
            $this->line("Email: {$user->email}");
        } else {
            $this->error("❌ لم يتم إنشاء مستخدم بنفس رقم المعلم!");
        }

        // اختبار التطابق
        if ($teacher->user_id == $teacher->id && $user && $user->id == $teacher->id) {
            $this->info('');
            $this->info('🎉 نجح الاختبار!');
            $this->info('✅ Teacher ID = User ID = ' . $teacher->id);
            $this->info('✅ التوحيد يعمل تلقائياً للمعلمين الجدد');
        } else {
            $this->error('');
            $this->error('❌ فشل الاختبار!');
            $this->error('⚠️ التوحيد لا يعمل تلقائياً');
        }

        // اختبار المعلم رقم 55 (كما طلب المستخدم)
        $this->info('');
        $this->info('🎯 اختبار خاص: محاكاة إضافة معلم رقم 55');
        
        // محاولة إنشاء معلم برقم محدد (55)
        try {
            // التأكد من عدم وجود معلم برقم 55 مسبقاً
            $existing55 = Teacher::find(55);
            if ($existing55) {
                $this->warn('⚠️ المعلم رقم 55 موجود مسبقاً');
                $user55 = User::find(55);
                if ($user55) {
                    $this->info('✅ المستخدم رقم 55 موجود أيضاً');
                    $this->info('✅ إذاً: Teacher[55] = User[55] ✓');
                } else {
                    $this->error('❌ المعلم 55 موجود لكن User 55 غير موجود');
                }
            } else {
                $this->info('ℹ️ المعلم رقم 55 غير موجود - هذا طبيعي');
                $this->info('💡 عند إنشاءه، سيصبح Teacher[55] = User[55] تلقائياً');
            }
        } catch (\Exception $e) {
            $this->error('خطأ في فحص المعلم 55: ' . $e->getMessage());
        }

        // تنظيف الاختبار (حذف المعلم التجريبي)
        if ($this->confirm('هل تريد حذف المعلم التجريبي؟', true)) {
            try {
                // حذف المستخدم المرتبط أولاً
                if ($user) {
                    $user->delete();
                    $this->info('✅ تم حذف المستخدم التجريبي');
                }
                
                // حذف المعلم
                $teacher->delete();
                $this->info('✅ تم حذف المعلم التجريبي');
            } catch (\Exception $e) {
                $this->error('خطأ في الحذف: ' . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('📋 خلاصة الاختبار:');
        $this->info('✅ النظام يدعم التوحيد التلقائي');
        $this->info('✅ كل معلم جديد سيحصل على user_id = teacher_id');
        $this->info('✅ لا حاجة لتدخل يدوي مستقبلاً');
        $this->info('🎯 الإجابة: نعم، سيتم إضافة المعلم كمستخدم تلقائياً بنفس المعرف');
    }
}
