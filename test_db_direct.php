<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "garb_project";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // فحص وجود الجداول
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM circle_groups");
    $circleGroupsCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quran_circles");
    $quranCirclesCount = $stmt->fetch()['count'];
    
    echo "عدد الحلقات الفرعية: " . $circleGroupsCount . "\n";
    echo "عدد الحلقات القرآنية: " . $quranCirclesCount . "\n";
    
    // إنشاء حلقة فرعية للاختبار إذا لم توجد
    if ($circleGroupsCount == 0 && $quranCirclesCount > 0) {
        // الحصول على أول حلقة قرآنية
        $stmt = $pdo->query("SELECT id, name FROM quran_circles LIMIT 1");
        $circle = $stmt->fetch();
        
        if ($circle) {
            echo "إنشاء حلقة فرعية تجريبية...\n";
            $stmt = $pdo->prepare("INSERT INTO circle_groups (quran_circle_id, name, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$circle['id'], 'حلقة فرعية تجريبية', 'نشطة']);
            echo "تم إنشاء الحلقة الفرعية بنجاح!\n";
        }
    }
    
    // عرض الحلقات الفرعية الموجودة
    $stmt = $pdo->query("SELECT cg.id, cg.name, cg.status, qc.name as circle_name 
                         FROM circle_groups cg 
                         JOIN quran_circles qc ON cg.quran_circle_id = qc.id");
    $groups = $stmt->fetchAll();
    
    echo "\nالحلقات الفرعية الموجودة:\n";
    foreach ($groups as $group) {
        echo "- " . $group['name'] . " (في " . $group['circle_name'] . ") - " . $group['status'] . "\n";
    }
    
} catch(PDOException $e) {
    echo "خطأ في الاتصال: " . $e->getMessage() . "\n";
}
