<?php

/**
 * اختبار مباشر وسريع لـ API مساجد المعلم
 * يستخدم cURL لاختبار API بشكل مباشر
 */

echo "🚀 اختبار سريع لـ API مساجد المعلم\n";
echo "=====================================\n\n";

// إعدادات الاختبار
$baseUrl = 'http://localhost:8000/api';  // تعديل الرابط حسب إعدادك
$teacherId = 1;  // معرف المعلم للاختبار

/**
 * دالة لإرسال طلب cURL
 */
function sendCurlRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000; // بالميلي ثانية
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode,
            'response_time' => $responseTime
        ];
    }
    
    curl_close($ch);
    
    return [
        'success' => true,
        'data' => json_decode($response, true),
        'raw_response' => $response,
        'http_code' => $httpCode,
        'response_time' => $responseTime
    ];
}

/**
 * دالة لعرض نتيجة الاختبار
 */
function showResult($testName, $passed, $message, $details = null) {
    $status = $passed ? "✅" : "❌";
    echo "$status $testName: $message\n";
    
    if ($details) {
        foreach ($details as $detail) {
            echo "   • $detail\n";
        }
    }
    echo "\n";
}

// اختبار 1: اختبار الاتصال الأساسي
echo "🧪 اختبار 1: الاتصال الأساسي بـ API\n";
$url = "$baseUrl/teachers/$teacherId/mosques";
echo "📡 إرسال طلب إلى: $url\n\n";

$result = sendCurlRequest($url);

if (!$result['success']) {
    showResult("الاتصال", false, "فشل الاتصال: " . $result['error']);
    echo "💡 تأكد من:\n";
    echo "   • تشغيل خادم Laravel: php artisan serve\n";
    echo "   • صحة رابط API: $baseUrl\n";
    echo "   • عدم وجود مشاكل في جدار الحماية\n\n";
    exit(1);
}

// التحقق من كود HTTP
if ($result['http_code'] === 200) {
    showResult("كود HTTP", true, "200 OK - الطلب نجح", [
        "زمن الاستجابة: " . round($result['response_time']) . "ms"
    ]);
} else {
    showResult("كود HTTP", false, "كود غير متوقع: " . $result['http_code']);
}

// اختبار 2: تحليل البيانات المُستجابة
echo "🧪 اختبار 2: تحليل البيانات المُستجابة\n";

$data = $result['data'];

if ($data === null) {
    showResult("تنسيق JSON", false, "الاستجابة ليست JSON صحيح");
    echo "الاستجابة الخام:\n" . substr($result['raw_response'], 0, 500) . "...\n\n";
} else {
    showResult("تنسيق JSON", true, "JSON صحيح ومقروء");
}

// التحقق من الحقول الأساسية
if (isset($data['نجح'])) {
    if ($data['نجح'] === true) {
        showResult("حالة النجاح", true, "API يعيد نجح = true");
    } else {
        showResult("حالة النجاح", false, "API يعيد نجح = false", [
            "الرسالة: " . ($data['رسالة'] ?? 'غير محددة')
        ]);
    }
} else {
    showResult("حالة النجاح", false, "لا يوجد حقل 'نجح' في الاستجابة");
}

// التحقق من البيانات
if (isset($data['البيانات'])) {
    showResult("وجود البيانات", true, "حقل البيانات موجود");
    
    $responseData = $data['البيانات'];
    
    // التحقق من معلومات المعلم
    if (isset($responseData['معلومات_المعلم'])) {
        $teacher = $responseData['معلومات_المعلم'];
        showResult("معلومات المعلم", true, "متوفرة", [
            "الاسم: " . ($teacher['الاسم'] ?? 'غير محدد'),
            "رقم الهوية: " . ($teacher['رقم_الهوية'] ?? 'غير محدد'),
            "رقم الهاتف: " . ($teacher['رقم_الهاتف'] ?? 'غير محدد')
        ]);
    } else {
        showResult("معلومات المعلم", false, "غير متوفرة");
    }
    
    // التحقق من الإحصائيات
    if (isset($responseData['الإحصائيات'])) {
        $stats = $responseData['الإحصائيات'];
        showResult("الإحصائيات", true, "متوفرة", [
            "عدد المساجد: " . ($stats['عدد_المساجد'] ?? 0),
            "عدد الحلقات: " . ($stats['عدد_الحلقات'] ?? 0),
            "إجمالي الطلاب: " . ($stats['إجمالي_الطلاب'] ?? 0)
        ]);
    } else {
        showResult("الإحصائيات", false, "غير متوفرة");
    }
    
    // التحقق من المساجد
    if (isset($responseData['المساجد']) && is_array($responseData['المساجد'])) {
        $mosques = $responseData['المساجد'];
        showResult("قائمة المساجد", true, "متوفرة", [
            "عدد المساجد: " . count($mosques)
        ]);
        
        // عرض تفاصيل المساجد
        echo "🕌 تفاصيل المساجد:\n";
        foreach ($mosques as $index => $mosque) {
            echo "   " . ($index + 1) . ". " . ($mosque['اسم_المسجد'] ?? 'غير محدد') . "\n";
            echo "      النوع: " . ($mosque['النوع'] ?? 'غير محدد') . "\n";
            echo "      العنوان: " . ($mosque['العنوان'] ?? 'غير محدد') . "\n";
            echo "      عدد الحلقات: " . (isset($mosque['الحلقات']) ? count($mosque['الحلقات']) : 0) . "\n";
            echo "      عدد الجداول: " . (isset($mosque['الجداول']) ? count($mosque['الجداول']) : 0) . "\n";
            echo "\n";
        }
    } else {
        showResult("قائمة المساجد", false, "غير متوفرة أو فارغة");
    }
    
} else {
    showResult("وجود البيانات", false, "حقل البيانات غير موجود");
}

// اختبار 3: اختبار معرف غير موجود
echo "🧪 اختبار 3: معرف معلم غير موجود\n";
$invalidUrl = "$baseUrl/teachers/99999/mosques";
$invalidResult = sendCurlRequest($invalidUrl);

if ($invalidResult['success']) {
    $invalidData = $invalidResult['data'];
    if (isset($invalidData['نجح']) && $invalidData['نجح'] === false) {
        showResult("معالجة خطأ المعرف", true, "API يتعامل مع المعرف غير الموجود بشكل صحيح", [
            "رسالة الخطأ: " . ($invalidData['رسالة'] ?? 'غير محددة')
        ]);
    } else {
        showResult("معالجة خطأ المعرف", false, "API لا يتعامل مع المعرف غير الموجود بشكل صحيح");
    }
} else {
    showResult("معالجة خطأ المعرف", false, "خطأ في الاتصال أثناء اختبار المعرف غير الموجود");
}

// اختبار 4: اختبار الأداء
echo "🧪 اختبار 4: اختبار الأداء\n";
$times = [];
for ($i = 0; $i < 3; $i++) {
    $perfResult = sendCurlRequest($url);
    if ($perfResult['success']) {
        $times[] = $perfResult['response_time'];
    }
}

if (!empty($times)) {
    $avgTime = array_sum($times) / count($times);
    $maxTime = max($times);
    $minTime = min($times);
    
    showResult("أداء API", true, "تم قياس الأداء", [
        "متوسط زمن الاستجابة: " . round($avgTime) . "ms",
        "أسرع استجابة: " . round($minTime) . "ms",
        "أبطأ استجابة: " . round($maxTime) . "ms"
    ]);
    
    if ($avgTime < 500) {
        echo "🚀 الأداء ممتاز!\n\n";
    } elseif ($avgTime < 1000) {
        echo "✅ الأداء جيد\n\n";
    } else {
        echo "⚠️  الأداء قابل للتحسين\n\n";
    }
} else {
    showResult("أداء API", false, "فشل في قياس الأداء");
}

// النتائج النهائية
echo "========================================\n";
echo "📊 ملخص الاختبار\n";
echo "========================================\n";
echo "🎯 API المختبر: GET /api/teachers/{id}/mosques\n";
echo "📅 تاريخ الاختبار: " . date('Y-m-d H:i:s') . "\n";
echo "🔗 رابط الاختبار: $url\n\n";

echo "📋 معلومات للمطورين:\n";
echo "   • API يعيد جميع المساجد التي يعمل بها المعلم\n";
echo "   • يتضمن المسجد الأساسي والمساجد الإضافية\n";
echo "   • يعرض تفاصيل الحلقات والطلاب في كل مسجد\n";
echo "   • يحتوي على جداول العمل في المساجد المختلفة\n";
echo "   • يقدم إحصائيات شاملة للمعلم\n\n";

echo "🔧 أمثلة للاستخدام:\n";
echo "   curl -X GET '$baseUrl/teachers/1/mosques'\n";
echo "   curl -H 'Accept: application/json' '$baseUrl/teachers/1/mosques'\n\n";

echo "✨ انتهى الاختبار بنجاح!\n";
