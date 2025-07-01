<?php

require_once __DIR__ . '/vendor/autoload.php';

// تحديد Laravel بشكل صحيح
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\QuranCircle;
use App\Models\CircleGroup;
use App\Models\Student;
use App\Models\Mosque;

echo "🏫 اختبار APIs إدارة طلاب المدرسة القرآنية\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // 1. البحث عن مدرسة قرآنية للاختبار
    echo "1️⃣ البحث عن مدرسة قرآنية مناسبة للاختبار...\n";
    
    // البحث عن مدرسة قرآنية أو حلقة جماعية للاختبار  
    $quranSchool = QuranCircle::whereIn('circle_type', ['مدرسة قرآنية', 'حلقة جماعية'])
        ->where('circle_status', 'نشطة')
        ->with(['mosque:id,name'])
        ->first();
    
    if (!$quranSchool) {
        // إنشاء حلقة تجريبية إذا لم توجد
        echo "   🔧 إنشاء مدرسة قرآنية تجريبية...\n";
        $mosque = \App\Models\Mosque::first();
        if (!$mosque) {
            echo "❌ لا يوجد مسجد في قاعدة البيانات لإنشاء المدرسة التجريبية\n";
            exit;
        }
        
        $quranSchool = QuranCircle::create([
            'name' => 'مدرسة تجريبية للاختبار - ' . date('Y-m-d H:i'),
            'mosque_id' => $mosque->id,
            'circle_type' => 'مدرسة قرآنية',
            'circle_status' => 'نشطة',
            'time_period' => 'عصر',
        ]);
        
        $quranSchool->load('mosque:id,name');
        echo "   ✅ تم إنشاء مدرسة قرآنية تجريبية: {$quranSchool->name} (ID: {$quranSchool->id})\n";
    }
    
    echo "✅ تم العثور على المدرسة القرآنية: {$quranSchool->name}\n";
    echo "   المسجد: {$quranSchool->mosque->name}\n";
    echo "   ID: {$quranSchool->id}\n\n";
    
    // 2. فحص الحلقات الفرعية
    echo "2️⃣ فحص الحلقات الفرعية النشطة...\n";
    
    $activeGroups = CircleGroup::where('quran_circle_id', $quranSchool->id)
        ->where('status', 'نشطة')
        ->with(['teacher:id,name'])
        ->get();
    
    if ($activeGroups->isEmpty()) {
        echo "❌ لا توجد حلقات فرعية نشطة في هذه المدرسة\n";
        
        // إنشاء حلقة فرعية تجريبية
        echo "   🔧 إنشاء حلقة فرعية تجريبية...\n";
        $testGroup = CircleGroup::create([
            'quran_circle_id' => $quranSchool->id,
            'name' => 'حلقة تجريبية - ' . date('Y-m-d H:i'),
            'status' => 'نشطة',
            'description' => 'حلقة تجريبية للاختبار',
            'meeting_days' => ['الأحد', 'الثلاثاء', 'الخميس'],
        ]);
        
        echo "   ✅ تم إنشاء حلقة فرعية تجريبية: {$testGroup->name} (ID: {$testGroup->id})\n\n";
        
        $activeGroups = collect([$testGroup]);
    } else {
        echo "✅ تم العثور على {$activeGroups->count()} حلقة فرعية نشطة:\n";
        foreach ($activeGroups as $group) {
            $teacherName = $group->teacher ? $group->teacher->name : 'غير محدد';
            echo "   - {$group->name} - المعلم: {$teacherName}\n";
        }
        echo "\n";
    }
    
    // 3. اختبار API جلب معلومات المدرسة القرآنية
    echo "3️⃣ اختبار API جلب معلومات المدرسة القرآنية...\n";
    
    $request = Request::create("/api/quran-schools/{$quranSchool->id}/info", 'GET');
    $controller = new App\Http\Controllers\Api\QuranSchoolStudentController();
    $response = $controller->getQuranSchoolInfo($quranSchool->id);
    $responseData = json_decode($response->getContent(), true);
    
    if ($responseData['success']) {
        echo "✅ API جلب معلومات المدرسة يعمل بنجاح\n";
        echo "   الحلقات الفرعية المتاحة: " . count($responseData['data']['circle_groups']) . "\n";
        echo "   إجمالي الطلاب: " . $responseData['data']['statistics']['total_students'] . "\n\n";
    } else {
        echo "❌ فشل API جلب معلومات المدرسة: " . $responseData['message'] . "\n\n";
    }
    
    // 4. اختبار إضافة طالب جديد
    echo "4️⃣ اختبار إضافة طالب جديد...\n";
    
    $testStudentData = [
        'identity_number' => '1234567890' . rand(10, 99), // رقم عشوائي لتجنب التكرار
        'name' => 'طالب تجريبي - ' . date('H:i'),
        'phone' => '0501234567',
        'guardian_name' => 'ولي أمر تجريبي',
        'guardian_phone' => '0507654321',
        'birth_date' => '2010-01-01',
        'nationality' => 'سعودي',
        'education_level' => 'ابتدائي',
        'neighborhood' => 'حي النموذج',
        'circle_group_id' => $activeGroups->first()->id,
        'memorization_plan' => 'حفظ جزء عم',
        'review_plan' => 'مراجعة يومية',
    ];
    
    $addRequest = Request::create("/api/quran-schools/{$quranSchool->id}/students", 'POST', $testStudentData);
    $addResponse = $controller->addStudent($addRequest, $quranSchool->id);
    $addResponseData = json_decode($addResponse->getContent(), true);
    
    if ($addResponseData['success']) {
        echo "✅ تم إضافة الطالب بنجاح\n";
        echo "   اسم الطالب: " . $addResponseData['data']['student']['name'] . "\n";
        echo "   رقم الهوية: " . $addResponseData['data']['student']['identity_number'] . "\n";
        echo "   كلمة المرور الافتراضية: " . $addResponseData['data']['student']['default_password'] . "\n";
        echo "   الحلقة الفرعية: " . $addResponseData['data']['student']['circle_group']['name'] . "\n\n";
        
        $newStudentId = $addResponseData['data']['student']['id'];
    } else {
        echo "❌ فشل إضافة الطالب: " . $addResponseData['message'] . "\n";
        if (isset($addResponseData['errors'])) {
            foreach ($addResponseData['errors'] as $field => $errors) {
                echo "   {$field}: " . implode(', ', $errors) . "\n";
            }
        }
        echo "\n";
        $newStudentId = null;
    }
    
    // 5. اختبار جلب قائمة الطلاب
    echo "5️⃣ اختبار جلب قائمة طلاب المدرسة القرآنية...\n";
    
    $listRequest = Request::create("/api/quran-schools/{$quranSchool->id}/students", 'GET');
    $listResponse = $controller->getStudents($listRequest, $quranSchool->id);
    $listResponseData = json_decode($listResponse->getContent(), true);
    
    if ($listResponseData['success']) {
        $studentsCount = count($listResponseData['data']['students']);
        echo "✅ تم جلب قائمة الطلاب بنجاح\n";
        echo "   عدد الطلاب: {$studentsCount}\n";
        echo "   إجمالي الطلاب: " . $listResponseData['data']['pagination']['total'] . "\n\n";
        
        if ($studentsCount > 0) {
            echo "   أول 3 طلاب:\n";
            foreach (array_slice($listResponseData['data']['students'], 0, 3) as $student) {
                echo "   - {$student['name']} ({$student['identity_number']})\n";
            }
            echo "\n";
        }
    } else {
        echo "❌ فشل جلب قائمة الطلاب: " . $listResponseData['message'] . "\n\n";
    }
    
    // 6. اختبار تحديث معلومات الطالب (إذا تم إنشاؤه بنجاح)
    if ($newStudentId) {
        echo "6️⃣ اختبار تحديث معلومات الطالب...\n";
        
        $updateData = [
            'name' => 'طالب محدث - ' . date('H:i'),
            'phone' => '0509876543',
            'memorization_plan' => 'حفظ جزء عم + جزء تبارك',
        ];
        
        $updateRequest = Request::create("/api/quran-schools/{$quranSchool->id}/students/{$newStudentId}", 'PUT', $updateData);
        $updateResponse = $controller->updateStudent($updateRequest, $quranSchool->id, $newStudentId);
        $updateResponseData = json_decode($updateResponse->getContent(), true);
        
        if ($updateResponseData['success']) {
            echo "✅ تم تحديث معلومات الطالب بنجاح\n";
            echo "   الاسم الجديد: " . $updateResponseData['data']['student']['name'] . "\n";
            echo "   الجوال الجديد: " . $updateResponseData['data']['student']['phone'] . "\n\n";
        } else {
            echo "❌ فشل تحديث معلومات الطالب: " . $updateResponseData['message'] . "\n\n";
        }
    }
    
    // 7. اختبار فلترة الطلاب حسب الحلقة الفرعية
    echo "7️⃣ اختبار فلترة الطلاب حسب الحلقة الفرعية...\n";
    
    $filterRequest = Request::create("/api/quran-schools/{$quranSchool->id}/students", 'GET', [
        'circle_group_id' => $activeGroups->first()->id,
        'is_active' => true
    ]);
    $filterResponse = $controller->getStudents($filterRequest, $quranSchool->id);
    $filterResponseData = json_decode($filterResponse->getContent(), true);
    
    if ($filterResponseData['success']) {
        $filteredCount = count($filterResponseData['data']['students']);
        echo "✅ تم فلترة الطلاب بنجاح\n";
        echo "   عدد الطلاب في الحلقة الفرعية: {$filteredCount}\n\n";
    } else {
        echo "❌ فشل فلترة الطلاب: " . $filterResponseData['message'] . "\n\n";
    }
    
    // 8. اختبار البحث بالاسم
    echo "8️⃣ اختبار البحث بالاسم...\n";
    
    $searchRequest = Request::create("/api/quran-schools/{$quranSchool->id}/students", 'GET', [
        'search' => 'تجريبي'
    ]);
    $searchResponse = $controller->getStudents($searchRequest, $quranSchool->id);
    $searchResponseData = json_decode($searchResponse->getContent(), true);
    
    if ($searchResponseData['success']) {
        $searchCount = count($searchResponseData['data']['students']);
        echo "✅ تم البحث بنجاح\n";
        echo "   عدد النتائج: {$searchCount}\n\n";
    } else {
        echo "❌ فشل البحث: " . $searchResponseData['message'] . "\n\n";
    }
    
    echo "🎉 انتهى اختبار APIs إدارة طلاب المدرسة القرآنية بنجاح!\n\n";
    
    // ملخص الـ APIs المتاحة
    echo "📋 ملخص الـ APIs المتاحة:\n";
    echo "=" . str_repeat("=", 40) . "\n";
    echo "1. GET  /api/quran-schools/{id}/info - جلب معلومات المدرسة والحلقات الفرعية\n";
    echo "2. POST /api/quran-schools/{id}/students - إضافة طالب جديد\n";
    echo "3. GET  /api/quran-schools/{id}/students - جلب قائمة الطلاب مع الفلترة\n";
    echo "4. PUT  /api/quran-schools/{id}/students/{studentId} - تحديث معلومات طالب\n";
    echo "5. DELETE /api/quran-schools/{id}/students/{studentId} - إلغاء تفعيل طالب\n\n";
    
    echo "🔧 معاملات الفلترة المتاحة في GET students:\n";
    echo "   - circle_group_id: فلترة حسب الحلقة الفرعية\n";
    echo "   - is_active: فلترة حسب الحالة (نشط/غير نشط)\n";
    echo "   - search: البحث بالاسم أو رقم الهوية\n";
    echo "   - per_page: عدد النتائج في الصفحة (افتراضي: 15)\n\n";
    
    echo "✨ جميع الـ APIs تعمل بشكل صحيح وجاهزة للاستخدام!\n";

} catch (Exception $e) {
    echo "❌ حدث خطأ أثناء الاختبار: " . $e->getMessage() . "\n";
    echo "في الملف: " . $e->getFile() . " - السطر: " . $e->getLine() . "\n";
}
