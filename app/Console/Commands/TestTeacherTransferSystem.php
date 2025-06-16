<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherTransferRequest;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\Mosque;
use Illuminate\Support\Facades\DB;

class TestTeacherTransferSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:teacher-transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص واختبار نظام نقل المعلمين';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 بدء فحص نظام نقل المعلمين...');
        $this->newLine();

        // فحص وجود الجداول المطلوبة
        $this->checkTables();
        
        // فحص البيانات الموجودة
        $this->checkExistingData();
        
        // اختبار إنشاء طلب نقل
        $this->testCreateTransferRequest();
        
        // اختبار تحديث حالة الطلب
        $this->testUpdateRequestStatus();
        
        // اختبار تنفيذ النقل
        $this->testExecuteTransfer();
        
        $this->newLine();
        $this->info('✅ تم الانتهاء من فحص نظام نقل المعلمين!');
    }

    private function checkTables()
    {
        $this->info('📋 1. فحص الجداول المطلوبة:');
        
        $tables = [
            'teacher_transfer_requests' => 'جدول طلبات نقل المعلمين',
            'teacher_transfer_request_activities' => 'جدول أنشطة طلبات النقل',
            'teachers' => 'جدول المعلمين',
            'quran_circles' => 'جدول الحلقات القرآنية',
            'mosques' => 'جدول المساجد'
        ];

        foreach ($tables as $table => $description) {
            try {
                $exists = DB::getSchemaBuilder()->hasTable($table);
                if ($exists) {
                    $count = DB::table($table)->count();
                    $this->info("   ✅ {$description}: موجود ({$count} سجل)");
                } else {
                    $this->error("   ❌ {$description}: غير موجود");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ خطأ في فحص {$description}: " . $e->getMessage());
            }
        }
        $this->newLine();
    }

    private function checkExistingData()
    {
        $this->info('📊 2. فحص البيانات الموجودة:');
        
        try {
            // فحص المعلمين
            $teachersCount = Teacher::count();
            $this->info("   👨‍🏫 عدد المعلمين: {$teachersCount}");
            
            if ($teachersCount > 0) {
                $firstTeacher = Teacher::with(['mosque', 'quranCircle'])->first();
                $this->info("   مثال - المعلم الأول:");
                $this->info("     الاسم: {$firstTeacher->name}");
                $this->info("     المسجد: " . ($firstTeacher->mosque->name ?? 'غير محدد'));
                $this->info("     الحلقة: " . ($firstTeacher->quranCircle->name ?? 'غير محدد'));
            }
            
            // فحص الحلقات
            $circlesCount = QuranCircle::count();
            $this->info("   🏫 عدد الحلقات القرآنية: {$circlesCount}");
            
            // فحص المساجد
            $mosquesCount = Mosque::count();
            $this->info("   🕌 عدد المساجد: {$mosquesCount}");
            
            // فحص طلبات النقل الموجودة
            if (DB::getSchemaBuilder()->hasTable('teacher_transfer_requests')) {
                $transferRequestsCount = TeacherTransferRequest::count();
                $this->info("   📋 عدد طلبات النقل الموجودة: {$transferRequestsCount}");
                
                if ($transferRequestsCount > 0) {
                    $this->info("   طلبات النقل حسب الحالة:");
                    $statusCounts = TeacherTransferRequest::select('status', DB::raw('count(*) as count'))
                        ->groupBy('status')
                        ->get();
                    
                    foreach ($statusCounts as $status) {
                        $this->info("     - {$status->status}: {$status->count}");
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ خطأ في فحص البيانات: " . $e->getMessage());
        }
        $this->newLine();
    }

    private function testCreateTransferRequest()
    {
        $this->info('🧪 3. اختبار إنشاء طلب نقل:');
        
        try {
            // البحث عن معلم ومسجدين مختلفين للاختبار
            $teacher = Teacher::first();
            if (!$teacher) {
                $this->error("   ❌ لا يوجد معلمين للاختبار");
                return;
            }

            $circles = QuranCircle::take(2)->get();
            if ($circles->count() < 2) {
                $this->error("   ❌ يحتاج إلى حلقتين على الأقل للاختبار");
                return;
            }

            $currentCircle = $circles->first();
            $requestedCircle = $circles->last();

            // إنشاء طلب نقل تجريبي
            $transferRequest = TeacherTransferRequest::create([
                'teacher_id' => $teacher->id,
                'current_circle_id' => $currentCircle->id,
                'requested_circle_id' => $requestedCircle->id,
                'current_mosque_id' => $currentCircle->mosque_id,
                'requested_mosque_id' => $requestedCircle->mosque_id,
                'request_date' => now(),
                'transfer_reason' => 'اختبار نظام النقل',
                'status' => 'قيد المراجعة', // استخدام القيمة العربية الصحيحة
                'notes' => 'طلب تجريبي لفحص النظام'
            ]);

            $this->info("   ✅ تم إنشاء طلب نقل تجريبي بنجاح");
            $this->info("     رقم الطلب: {$transferRequest->id}");
            $this->info("     المعلم: {$teacher->name}");
            $this->info("     من الحلقة: {$currentCircle->name}");
            $this->info("     إلى الحلقة: {$requestedCircle->name}");
            $this->info("     الحالة: {$transferRequest->status}");

        } catch (\Exception $e) {
            $this->error("   ❌ فشل إنشاء طلب النقل: " . $e->getMessage());
            $this->error("   التفاصيل: " . $e->getFile() . ':' . $e->getLine());
        }
        $this->newLine();
    }

    private function testUpdateRequestStatus()
    {
        $this->info('🔄 4. اختبار تحديث حالة الطلب:');
        
        try {
            $transferRequest = TeacherTransferRequest::latest()->first();
            if (!$transferRequest) {
                $this->error("   ❌ لا يوجد طلبات نقل للاختبار");
                return;
            }

            $oldStatus = $transferRequest->status;
            
            // اختبار method updateStatus إذا كان موجوداً
            if (method_exists($transferRequest, 'updateStatus')) {
                $result = $transferRequest->updateStatus('موافقة نهائية', 1, 'مدير النظام', 'تمت الموافقة للاختبار');
                
                $this->info("   ✅ تم تحديث حالة الطلب بنجاح");
                $this->info("     من: {$oldStatus}");
                $this->info("     إلى: {$transferRequest->status}");
            } else {
                // التحديث اليدوي
                $transferRequest->status = 'موافقة نهائية';
                $transferRequest->response_date = now();
                $transferRequest->approved_by = 1;
                $transferRequest->response_notes = 'تمت الموافقة للاختبار';
                $transferRequest->save();
                
                $this->info("   ✅ تم تحديث حالة الطلب يدوياً");
                $this->info("     من: {$oldStatus}");
                $this->info("     إلى: موافقة نهائية");
                $this->warn("   ⚠️ method updateStatus غير موجود في النموذج");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ فشل تحديث حالة الطلب: " . $e->getMessage());
        }
        $this->newLine();
    }

    private function testExecuteTransfer()
    {
        $this->info('⚡ 5. اختبار تنفيذ النقل:');
        
        try {
            $transferRequest = TeacherTransferRequest::where('status', 'موافقة نهائية')->latest()->first();
            if (!$transferRequest) {
                $this->error("   ❌ لا يوجد طلبات موافق عليها للاختبار");
                return;
            }

            $teacher = $transferRequest->teacher;
            $oldCircleId = $teacher->quran_circle_id;
            $oldMosqueId = $teacher->mosque_id;

            // اختبار method executeTransfer إذا كان موجوداً
            if (method_exists($transferRequest, 'executeTransfer')) {
                $result = $transferRequest->executeTransfer();
                
                if ($result) {
                    $teacher->refresh();
                    $this->info("   ✅ تم تنفيذ النقل بنجاح");
                    $this->info("     الحلقة السابقة: {$oldCircleId}");
                    $this->info("     الحلقة الجديدة: {$teacher->quran_circle_id}");
                    $this->info("     المسجد السابق: {$oldMosqueId}");
                    $this->info("     المسجد الجديد: {$teacher->mosque_id}");
                } else {
                    $this->error("   ❌ فشل تنفيذ النقل");
                }
            } else {
                // التنفيذ اليدوي
                $teacher->quran_circle_id = $transferRequest->requested_circle_id;
                if ($transferRequest->requested_mosque_id) {
                    $teacher->mosque_id = $transferRequest->requested_mosque_id;
                }
                $teacher->save();

                $transferRequest->status = 'تم النقل';
                $transferRequest->transfer_date = now();
                $transferRequest->save();
                
                $this->info("   ✅ تم تنفيذ النقل يدوياً");
                $this->warn("   ⚠️ method executeTransfer غير موجود في النموذج");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ فشل تنفيذ النقل: " . $e->getMessage());
        }
        $this->newLine();
    }
}
