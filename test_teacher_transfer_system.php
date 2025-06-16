<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TeacherTransferRequest;
use App\Models\Teacher;
use App\Models\QuranCircle;
use App\Models\Mosque;
use App\Models\User;

// تهيئة Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "================================================================================\n";
echo "                    🔍 فحص نظام نقل المعلمين الحالي\n";
echo "================================================================================\n";

try {
    echo "📊 1. فحص هيكل جدول طلبات نقل المعلمين...\n";
    
    // فحص وجود الجدول
    $tableExists = DB::getSchemaBuilder()->hasTable('teacher_transfer_requests');
    echo "   🗃️  جدول teacher_transfer_requests موجود: " . ($tableExists ? "✅ نعم" : "❌ لا") . "\n";
    
    if ($tableExists) {
        // فحص الأعمدة
        $columns = DB::getSchemaBuilder()->getColumnListing('teacher_transfer_requests');
        echo "   📋 الأعمدة الموجودة (" . count($columns) . " عمود):\n";
        foreach ($columns as $column) {
            echo "      - $column\n";
        }
        
        // فحص عدد السجلات
        $totalRequests = DB::table('teacher_transfer_requests')->count();
        echo "   📈 إجمالي طلبات النقل المسجلة: $totalRequests\n";
        
        if ($totalRequests > 0) {
            echo "\n📋 2. عرض آخر 5 طلبات نقل:\n";
            $recentRequests = DB::table('teacher_transfer_requests')
                ->join('teachers', 'teacher_transfer_requests.teacher_id', '=', 'teachers.id')
                ->leftJoin('quran_circles as current_circle', 'teacher_transfer_requests.current_circle_id', '=', 'current_circle.id')
                ->leftJoin('quran_circles as requested_circle', 'teacher_transfer_requests.requested_circle_id', '=', 'requested_circle.id')
                ->select(
                    'teacher_transfer_requests.id',
                    'teachers.name as teacher_name',
                    'current_circle.name as current_circle_name',
                    'requested_circle.name as requested_circle_name',
                    'teacher_transfer_requests.status',
                    'teacher_transfer_requests.request_date',
                    'teacher_transfer_requests.transfer_reason'
                )
                ->orderBy('teacher_transfer_requests.id', 'desc')
                ->limit(5)
                ->get();
            
            foreach ($recentRequests as $request) {
                echo "   🔄 طلب رقم {$request->id}:\n";
                echo "      👨‍🏫 المعلم: {$request->teacher_name}\n";
                echo "      🏫 من الحلقة: " . ($request->current_circle_name ?? 'غير محدد') . "\n";
                echo "      🎯 إلى الحلقة: " . ($request->requested_circle_name ?? 'غير محدد') . "\n";
                echo "      📊 الحالة: {$request->status}\n";
                echo "      📅 تاريخ الطلب: {$request->request_date}\n";
                echo "      📝 السبب: " . (substr($request->transfer_reason, 0, 50) . (strlen($request->transfer_reason) > 50 ? '...' : '')) . "\n";
                echo "      ─────────────────────────────────────────\n";
            }
        }
    }
    
    echo "\n🧪 3. اختبار إنشاء طلب نقل جديد...\n";
    
    // فحص البيانات المطلوبة للاختبار
    $testTeacher = DB::table('teachers')->first();
    $sourceCircle = DB::table('quran_circles')->first();
    $targetCircle = DB::table('quran_circles')->skip(1)->first();
    
    if (!$testTeacher) {
        echo "   ❌ لا يوجد معلمين في النظام للاختبار\n";
    } elseif (!$sourceCircle || !$targetCircle) {
        echo "   ❌ لا توجد حلقات كافية في النظام للاختبار (نحتاج حلقتين على الأقل)\n";
    } else {
        echo "   📋 بيانات الاختبار:\n";
        echo "      👨‍🏫 المعلم: {$testTeacher->name} (ID: {$testTeacher->id})\n";
        echo "      🏫 من الحلقة: {$sourceCircle->name} (ID: {$sourceCircle->id})\n";
        echo "      🎯 إلى الحلقة: {$targetCircle->name} (ID: {$targetCircle->id})\n";
        
        try {
            // محاولة إنشاء طلب نقل جديد
            $transferData = [
                'teacher_id' => $testTeacher->id,
                'current_circle_id' => $sourceCircle->id,
                'requested_circle_id' => $targetCircle->id,
                'request_date' => now(),
                'transfer_reason' => 'اختبار نظام نقل المعلمين - تجربة تقنية',
                'status' => 'pending',
                'notes' => 'هذا طلب اختبار تم إنشاؤه تلقائياً للتحقق من عمل النظام'
            ];
            
            // التحقق من إمكانية الإدراج
            $insertId = DB::table('teacher_transfer_requests')->insertGetId($transferData);
            
            if ($insertId) {
                echo "   ✅ تم إنشاء طلب نقل تجريبي بنجاح (ID: $insertId)\n";
                
                // التحقق من الطلب المُنشأ
                $createdRequest = DB::table('teacher_transfer_requests')->where('id', $insertId)->first();
                echo "   📋 تفاصيل الطلب المُنشأ:\n";
                echo "      🆔 رقم الطلب: {$createdRequest->id}\n";
                echo "      📊 الحالة: {$createdRequest->status}\n";
                echo "      📅 تاريخ الإنشاء: {$createdRequest->created_at}\n";
                
                // محاولة تحديث حالة الطلب
                echo "\n   🔄 اختبار تحديث حالة الطلب...\n";
                $updated = DB::table('teacher_transfer_requests')
                    ->where('id', $insertId)
                    ->update([
                        'status' => 'approved',
                        'response_date' => now(),
                        'response_notes' => 'تم اعتماد الطلب كجزء من الاختبار'
                    ]);
                
                if ($updated) {
                    echo "   ✅ تم تحديث حالة الطلب بنجاح\n";
                } else {
                    echo "   ❌ فشل في تحديث حالة الطلب\n";
                }
                
                // حذف الطلب التجريبي
                echo "   🗑️  حذف الطلب التجريبي...\n";
                $deleted = DB::table('teacher_transfer_requests')->where('id', $insertId)->delete();
                if ($deleted) {
                    echo "   ✅ تم حذف الطلب التجريبي بنجاح\n";
                } else {
                    echo "   ❌ فشل في حذف الطلب التجريبي\n";
                }
                
            } else {
                echo "   ❌ فشل في إنشاء طلب النقل التجريبي\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ خطأ في اختبار إنشاء الطلب: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 4. فحص نموذج TeacherTransferRequest...\n";
    
    try {
        // التحقق من وجود النموذج
        if (class_exists('App\Models\TeacherTransferRequest')) {
            echo "   ✅ نموذج TeacherTransferRequest موجود\n";
            
            // اختبار إنشاء instance
            $model = new TeacherTransferRequest();
            echo "   ✅ يمكن إنشاء instance من النموذج\n";
            
            // فحص الخصائص المهمة
            $fillable = $model->getFillable();
            echo "   📋 الخصائص القابلة للتعبئة (" . count($fillable) . " خاصية):\n";
            foreach ($fillable as $field) {
                echo "      - $field\n";
            }
            
            // فحص العلاقات
            echo "\n   🔗 اختبار العلاقات:\n";
            try {
                $teacherRelation = $model->teacher();
                echo "      ✅ علاقة teacher متاحة\n";
            } catch (Exception $e) {
                echo "      ❌ خطأ في علاقة teacher: " . $e->getMessage() . "\n";
            }
            
            try {
                $currentCircleRelation = $model->currentCircle();
                echo "      ✅ علاقة currentCircle متاحة\n";
            } catch (Exception $e) {
                echo "      ❌ خطأ في علاقة currentCircle: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "   ❌ نموذج TeacherTransferRequest غير موجود\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ خطأ في فحص النموذج: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎛️ 5. فحص Filament Resource...\n";
    
    try {
        if (class_exists('App\Filament\Admin\Resources\TeacherTransferRequestResource')) {
            echo "   ✅ Filament Resource موجود\n";
            
            // فحص المسارات المتاحة
            $resource = new App\Filament\Admin\Resources\TeacherTransferRequestResource();
            echo "   📋 Resource متاح في لوحة الإدارة\n";
            
        } else {
            echo "   ❌ Filament Resource غير موجود\n";
        }
    } catch (Exception $e) {
        echo "   ❌ خطأ في فحص Filament Resource: " . $e->getMessage() . "\n";
    }
    
    echo "\n📊 6. إحصائيات النظام الحالي:\n";
    
    // إحصائيات العملات
    $statusStats = DB::table('teacher_transfer_requests')
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get();
    
    if ($statusStats->isEmpty()) {
        echo "   📈 لا توجد طلبات نقل في النظام\n";
    } else {
        echo "   📈 توزيع الطلبات حسب الحالة:\n";
        foreach ($statusStats as $stat) {
            echo "      📊 {$stat->status}: {$stat->count} طلب\n";
        }
    }
    
    // إحصائيات شهرية
    $monthlyStats = DB::table('teacher_transfer_requests')
        ->select(DB::raw('DATE_FORMAT(request_date, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
        ->groupBy(DB::raw('DATE_FORMAT(request_date, "%Y-%m")'))
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();
    
    if (!$monthlyStats->isEmpty()) {
        echo "\n   📅 الطلبات الشهرية (آخر 6 أشهر):\n";
        foreach ($monthlyStats as $stat) {
            echo "      📅 {$stat->month}: {$stat->count} طلب\n";
        }
    }
    
    echo "\n✅ 7. التقييم النهائي:\n";
    
    $workingComponents = [];
    $brokenComponents = [];
    
    // تقييم المكونات
    if ($tableExists) {
        $workingComponents[] = "جدول قاعدة البيانات";
    } else {
        $brokenComponents[] = "جدول قاعدة البيانات";
    }
    
    if (class_exists('App\Models\TeacherTransferRequest')) {
        $workingComponents[] = "نموذج Laravel";
    } else {
        $brokenComponents[] = "نموذج Laravel";
    }
    
    if (class_exists('App\Filament\Admin\Resources\TeacherTransferRequestResource')) {
        $workingComponents[] = "واجهة Filament";
    } else {
        $brokenComponents[] = "واجهة Filament";
    }
    
    echo "\n   ✅ المكونات التي تعمل:\n";
    foreach ($workingComponents as $component) {
        echo "      ✅ $component\n";
    }
    
    if (!empty($brokenComponents)) {
        echo "\n   ❌ المكونات التي لا تعمل:\n";
        foreach ($brokenComponents as $component) {
            echo "      ❌ $component\n";
        }
    }
    
    // النتيجة النهائية
    $totalComponents = count($workingComponents) + count($brokenComponents);
    $workingPercentage = round((count($workingComponents) / $totalComponents) * 100);
    
    echo "\n📊 نسبة نجاح النظام: {$workingPercentage}%\n";
    
    if ($workingPercentage >= 80) {
        echo "🎉 النظام يعمل بشكل جيد! يمكن الاعتماد عليه لبناء نظام نقل الطلاب\n";
    } elseif ($workingPercentage >= 50) {
        echo "⚠️  النظام يعمل جزئياً - يحتاج بعض الإصلاحات\n";
    } else {
        echo "❌ النظام لا يعمل بشكل صحيح - يحتاج إعادة تطوير\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ عام في الفحص: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . "\n";
    echo "📍 السطر: " . $e->getLine() . "\n";
}

echo "\n================================================================================\n";
echo "                            🏁 انتهى الفحص\n";
echo "================================================================================\n";

?>
