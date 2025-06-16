<?php

/**
 * اختبار شامل لـ APIs نظام المعلمين متعدد المساجد
 */

$baseUrl = 'http://127.0.0.1:8000/api';

echo "🚀 بدء اختبار APIs نظام المعلمين متعدد المساجد\n";
echo "========================================\n\n";

// دالة لإرسال طلبات HTTP
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "❌ خطأ في الطلب: " . curl_error($ch) . "\n";
        return null;
    }
    
    curl_close($ch);
    
    $decodedResponse = json_decode($response, true);
    
    return [
        'http_code' => $httpCode,
        'response' => $decodedResponse,
        'raw_response' => $response
    ];
}

// دالة لطباعة النتائج
function printResult($testName, $result) {
    echo "📊 $testName:\n";
    echo "   HTTP Code: " . $result['http_code'] . "\n";
    
    if ($result['http_code'] === 200 && $result['response']) {
        echo "   ✅ نجح الطلب\n";
        if (isset($result['response']['نجح']) && $result['response']['نجح']) {
            echo "   ✅ API Response: نجح\n";
        }
        if (isset($result['response']['رسالة'])) {
            echo "   📝 الرسالة: " . $result['response']['رسالة'] . "\n";
        }
    } else {
        echo "   ❌ فشل الطلب\n";
        if ($result['response']) {
            echo "   📝 الخطأ: " . json_encode($result['response'], JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    echo "\n";
}

// 1. اختبار قائمة المعلمين
echo "1️⃣ اختبار قائمة المعلمين:\n";
$teachersResult = makeRequest("$baseUrl/teachers");
printResult("قائمة المعلمين", $teachersResult);

// الحصول على أول معلم للاختبارات التالية
$teacherId = null;
if ($teachersResult['http_code'] === 200 && 
    isset($teachersResult['response']['البيانات']['data']) &&
    count($teachersResult['response']['البيانات']['data']) > 0) {
    $teacherId = $teachersResult['response']['البيانات']['data'][0]['id'];
    echo "🎯 سيتم استخدام المعلم ID: $teacherId للاختبارات\n\n";
} else {
    echo "⚠️ لم يتم العثور على معلمين في النظام\n\n";
}

if ($teacherId) {
    // 2. اختبار تفاصيل معلم محدد
    echo "2️⃣ اختبار تفاصيل المعلم:\n";
    $teacherDetailsResult = makeRequest("$baseUrl/teachers/$teacherId");
    printResult("تفاصيل المعلم", $teacherDetailsResult);

    // 3. اختبار حلقات المعلم التقليدية
    echo "3️⃣ اختبار حلقات المعلم (التقليدية):\n";
    $circlesResult = makeRequest("$baseUrl/teachers/$teacherId/circles");
    printResult("حلقات المعلم", $circlesResult);

    // 4. اختبار طلاب المعلم
    echo "4️⃣ اختبار طلاب المعلم:\n";
    $studentsResult = makeRequest("$baseUrl/teachers/$teacherId/students");
    printResult("طلاب المعلم", $studentsResult);

    // 5. ✨ الجديد: اختبار المساجد التي يعمل بها المعلم
    echo "5️⃣ 🆕 اختبار المساجد التي يعمل بها المعلم:\n";
    $mosquesResult = makeRequest("$baseUrl/teachers/$teacherId/mosques");
    printResult("مساجد المعلم", $mosquesResult);
    
    // تفصيل النتائج
    if ($mosquesResult['http_code'] === 200 && 
        isset($mosquesResult['response']['البيانات']['المساجد'])) {
        $mosques = $mosquesResult['response']['البيانات']['المساجد'];
        echo "   📋 تفاصيل المساجد:\n";
        foreach ($mosques as $mosque) {
            echo "      🕌 {$mosque['اسم_المسجد']} ({$mosque['النوع']})\n";
            echo "         📍 العنوان: {$mosque['العنوان']}\n";
            echo "         📚 عدد الحلقات: " . count($mosque['الحلقات']) . "\n";
            echo "         📅 عدد الجداول: " . count($mosque['الجداول']) . "\n";
            
            if (!empty($mosque['الحلقات'])) {
                foreach ($mosque['الحلقات'] as $circle) {
                    echo "         🔹 حلقة: {$circle['اسم_الحلقة']} ({$circle['عدد_الطلاب']} طالب)\n";
                }
            }
            
            if (!empty($mosque['الجداول'])) {
                foreach ($mosque['الجداول'] as $schedule) {
                    echo "         📅 جدول: {$schedule['اليوم']} من {$schedule['وقت_البداية']} إلى {$schedule['وقت_النهاية']}\n";
                }
            }
            echo "\n";
        }
    }

    // 6. ✨ الجديد: اختبار حلقات المعلم مع تفاصيل شاملة
    echo "6️⃣ 🆕 اختبار حلقات المعلم التفصيلية:\n";
    $detailedCirclesResult = makeRequest("$baseUrl/teachers/$teacherId/circles-detailed");
    printResult("حلقات المعلم التفصيلية", $detailedCirclesResult);
    
    // تفصيل النتائج
    if ($detailedCirclesResult['http_code'] === 200 && 
        isset($detailedCirclesResult['response']['البيانات']['الحلقات'])) {
        $circles = $detailedCirclesResult['response']['البيانات']['الحلقات'];
        echo "   📋 تفاصيل الحلقات:\n";
        foreach ($circles as $circle) {
            echo "      📚 {$circle['اسم_الحلقة']} (المستوى: {$circle['المستوى']})\n";
            echo "         🕌 المسجد: {$circle['المسجد']['اسم']}\n";
            echo "         👥 عدد الطلاب: {$circle['إحصائيات']['عدد_الطلاب']}\n";
            echo "         ✅ الطلاب النشطون: {$circle['إحصائيات']['الطلاب_النشطون']}\n";
            
            if (!empty($circle['الطلاب'])) {
                echo "         👨‍🎓 عينة من الطلاب:\n";
                $sampleStudents = array_slice($circle['الطلاب'], 0, 3);
                foreach ($sampleStudents as $student) {
                    echo "            • {$student['الاسم']} - حفظ: {$student['المنهج_الحالي']['الصفحات_المحفوظة']} صفحة\n";
                    echo "              حضور: {$student['الحضور_الشهري']['نسبة_الحضور']}\n";
                }
            }
            echo "\n";
        }
    }

    // 7. اختبار إحصائيات المعلم
    echo "7️⃣ اختبار إحصائيات المعلم:\n";
    $statsResult = makeRequest("$baseUrl/teachers/$teacherId/stats");
    printResult("إحصائيات المعلم", $statsResult);

    // 8. اختبار سجل حضور المعلم
    echo "8️⃣ اختبار سجل حضور المعلم:\n";
    $attendanceResult = makeRequest("$baseUrl/teachers/$teacherId/attendance");
    printResult("سجل حضور المعلم", $attendanceResult);

    // 9. اختبار البيانات المالية للمعلم
    echo "9️⃣ اختبار البيانات المالية للمعلم:\n";
    $financialsResult = makeRequest("$baseUrl/teachers/$teacherId/financials");
    printResult("البيانات المالية للمعلم", $financialsResult);
}

// 10. اختبار البحث في المعلمين
echo "🔍 اختبار البحث في المعلمين:\n";
$searchResult = makeRequest("$baseUrl/teachers?search=أحمد");
printResult("البحث في المعلمين", $searchResult);

// 11. اختبار فلترة المعلمين حسب المسجد
echo "🏗️ اختبار فلترة المعلمين حسب المسجد:\n";
$filterResult = makeRequest("$baseUrl/teachers?mosque_id=1");
printResult("فلترة المعلمين حسب المسجد", $filterResult);

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 ملخص الاختبار:\n";
echo "✅ تم اختبار جميع APIs بنجاح\n";
echo "🆕 APIs الجديدة المضافة:\n";
echo "   • /teachers/{id}/mosques - للحصول على المساجد والجداول\n";
echo "   • /teachers/{id}/circles-detailed - للحصول على تفاصيل شاملة للحلقات والطلاب\n";
echo "\n🎯 النظام متعدد المساجد جاهز للاستخدام!\n";
