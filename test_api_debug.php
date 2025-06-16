<?php
/**
 * اختبار تشخيصي لـ API مساجد المعلم
 * يساعد في تحديد أسباب الأخطاء
 */

echo "🔍 اختبار تشخيصي لـ API مساجد المعلم\n";
echo "=====================================\n\n";

// معرفات مختلفة للاختبار
$teacherIds = [1, 2, 3, 999];
$baseUrl = "http://localhost:8000/api/teachers";

foreach ($teacherIds as $teacherId) {
    echo "🧪 اختبار المعلم ID: $teacherId\n";
    echo "-----------------------------\n";
    
    $url = "$baseUrl/$teacherId/mosques";
    
    // إنشاء cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    // تنفيذ الطلب
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // عرض النتائج
    echo "📡 رابط: $url\n";
    echo "🏷️  كود HTTP: $httpCode\n";
    
    if ($error) {
        echo "❌ خطأ cURL: $error\n";
    } else {
        echo "✅ طلب نجح\n";
        
        // تحليل JSON
        $jsonData = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON صحيح\n";
            
            // عرض بنية الاستجابة
            if (isset($jsonData['نجح'])) {
                echo "🎯 حالة النجاح: " . ($jsonData['نجح'] ? 'نعم' : 'لا') . "\n";
            }
            
            if (isset($jsonData['الرسالة'])) {
                echo "💬 الرسالة: {$jsonData['الرسالة']}\n";
            }
            
            if (isset($jsonData['البيانات'])) {
                echo "📊 البيانات متوفرة\n";
                
                // إحصائيات مختصرة
                if (isset($jsonData['البيانات']['الإحصائيات'])) {
                    $stats = $jsonData['البيانات']['الإحصائيات'];
                    echo "   📈 إجمالي المساجد: " . ($stats['إجمالي_المساجد'] ?? 'غير محدد') . "\n";
                    echo "   📈 إجمالي الحلقات: " . ($stats['إجمالي_الحلقات'] ?? 'غير محدد') . "\n";
                    echo "   📈 إجمالي الطلاب: " . ($stats['إجمالي_الطلاب'] ?? 'غير محدد') . "\n";
                }
            } else {
                echo "❌ البيانات غير متوفرة\n";
            }
            
            // عرض رسائل الخطأ إذا وُجدت
            if (isset($jsonData['الأخطاء'])) {
                echo "⚠️ الأخطاء:\n";
                foreach ($jsonData['الأخطاء'] as $error) {
                    echo "   • $error\n";
                }
            }
            
        } else {
            echo "❌ JSON غير صحيح\n";
            echo "📄 محتوى الاستجابة (أول 200 حرف):\n";
            echo substr($response, 0, 200) . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "🏁 انتهى التشخيص\n";
