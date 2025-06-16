<?php
// ملف للتحقق من اتصال قاعدة البيانات

$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';
$database = 'garb_project';

echo "<h2>اختبار اتصال قاعدة البيانات</h2>";

// محاولة الاتصال بالخادم أولاً (بدون تحديد قاعدة بيانات)
try {
    $conn = new PDO("mysql:host=$host;port=$port", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ الاتصال بخادم MySQL ناجح!</p>";
    
    // التحقق من وجود قاعدة البيانات
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "<p style='color:green'>✓ قاعدة البيانات '$database' موجودة!</p>";
        
        // محاولة الاتصال بقاعدة البيانات
        try {
            $dbConn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
            $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p style='color:green'>✓ الاتصال بقاعدة البيانات '$database' ناجح!</p>";
            
            // عرض قائمة الجداول
            $tables = $dbConn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (count($tables) > 0) {
                echo "<p style='color:green'>✓ الجداول الموجودة: " . implode(", ", $tables) . "</p>";
            } else {
                echo "<p style='color:orange'>! لا توجد جداول في قاعدة البيانات.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red'>✗ فشل الاتصال بقاعدة البيانات '$database': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:orange'>! قاعدة البيانات '$database' غير موجودة.</p>";
        echo "<p>هل ترغب في إنشاء قاعدة البيانات الآن؟ <a href='?create_db=1' style='background-color:#4CAF50;color:white;padding:8px 16px;text-decoration:none;border-radius:4px;'>إنشاء قاعدة البيانات</a></p>";
    }
    
    // إنشاء قاعدة البيانات إذا طلب المستخدم ذلك
    if (isset($_GET['create_db'])) {
        try {
            $conn->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p style='color:green'>✓ تم إنشاء قاعدة البيانات '$database' بنجاح!</p>";
            echo "<p>يمكنك الآن <a href='?migrate=1' style='background-color:#2196F3;color:white;padding:8px 16px;text-decoration:none;border-radius:4px;'>ترحيل الجداول</a></p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>✗ فشل إنشاء قاعدة البيانات: " . $e->getMessage() . "</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<p style='color:red'>✗ فشل الاتصال بخادم MySQL: " . $e->getMessage() . "</p>";
    echo "<p>تأكد من تشغيل خدمة MySQL على المنفذ $port</p>";
}

// تنفيذ ترحيل الجداول عند الطلب (باستخدام أمر PHP لتشغيل Artisan)
if (isset($_GET['migrate'])) {
    echo "<h3>جاري ترحيل الجداول...</h3>";
    echo "<pre>";
    system('php artisan migrate --force');
    echo "</pre>";
    echo "<p><a href='check_db_connection.php' style='background-color:#673AB7;color:white;padding:8px 16px;text-decoration:none;border-radius:4px;'>تحديث الصفحة</a></p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 20px;
    direction: rtl;
    text-align: right;
}
h2 {
    color: #333;
}
pre {
    background: #f4f4f4;
    padding: 10px;
    border-radius: 5px;
    overflow: auto;
    direction: ltr;
    text-align: left;
}
</style>