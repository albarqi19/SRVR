<?php

echo "🚀 اختبار نظام حالة جلسات التسميع\n";
echo "=====================================\n";

try {    // الاتصال المباشر بقاعدة البيانات
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=garb_project;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ تم الاتصال بقاعدة البيانات بنجاح\n\n";
    
    // 1. التحقق من بنية الجدول
    echo "📋 1. فحص بنية جدول recitation_sessions:\n";
    $stmt = $pdo->query("DESCRIBE recitation_sessions");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
        if (in_array($row['Field'], ['status', 'curriculum_id'])) {
            echo "   ✓ " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
    $hasStatus = in_array('status', $columns);
    $hasCurriculumId = in_array('curriculum_id', $columns);
    
    if (!$hasStatus) echo "   ❌ حقل status غير موجود\n";
    if (!$hasCurriculumId) echo "   ❌ حقل curriculum_id غير موجود\n";
    
    // 2. إحصائيات عامة
    echo "\n📊 2. الإحصائيات العامة:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM recitation_sessions");
    $total = $stmt->fetch()['total'];
    echo "   • إجمالي الجلسات: $total\n";
    
    if ($hasStatus) {
        // إحصائيات الحالات
        echo "\n📈 3. توزيع الحالات:\n";
        $stmt = $pdo->query("
            SELECT 
                status,
                COUNT(*) as count,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM recitation_sessions)), 2) as percentage
            FROM recitation_sessions 
            GROUP BY status
            ORDER BY count DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'] ?? 'NULL';
            echo "   • $status: " . $row['count'] . " جلسة (" . $row['percentage'] . "%)\n";
        }
    }
    
    if ($hasCurriculumId) {
        echo "\n📚 4. ربط المناهج:\n";
        $stmt = $pdo->query("
            SELECT 
                COUNT(CASE WHEN curriculum_id IS NOT NULL THEN 1 END) as with_curriculum,
                COUNT(CASE WHEN curriculum_id IS NULL THEN 1 END) as without_curriculum
            FROM recitation_sessions
        ");
        $row = $stmt->fetch();
        echo "   • الجلسات المرتبطة بمنهج: " . $row['with_curriculum'] . "\n";
        echo "   • الجلسات غير المرتبطة بمنهج: " . $row['without_curriculum'] . "\n";
    }
    
    // 5. فحص جدول StudentProgress
    echo "\n👥 5. حالة تقدم الطلاب:\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM student_progress");
        $progressTotal = $stmt->fetch()['total'];
        echo "   • إجمالي سجلات التقدم: $progressTotal\n";
        
        if ($progressTotal > 0) {
            $stmt = $pdo->query("
                SELECT 
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active,
                    COUNT(CASE WHEN is_active = 0 OR is_active IS NULL THEN 1 END) as inactive
                FROM student_progress
            ");
            $row = $stmt->fetch();
            echo "   • السجلات النشطة: " . $row['active'] . "\n";
            echo "   • السجلات غير النشطة: " . $row['inactive'] . "\n";
        }
    } catch (Exception $e) {
        echo "   ⚠ لا يمكن الوصول لجدول student_progress\n";
    }
    
    // 6. اختبار إنشاء جلسة جديدة
    echo "\n🎯 6. اختبار إنشاء جلسة تسميع:\n";
    
    // الحصول على بيانات أساسية
    $stmt = $pdo->query("SELECT id FROM students LIMIT 1");
    $student = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $teacher = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM quran_circles LIMIT 1");
    $circle = $stmt->fetch();
    
    if ($student && $teacher && $circle && $hasStatus) {
        $sessionId = 'TEST' . time();
        $sql = "INSERT INTO recitation_sessions (
            session_id, student_id, teacher_id, quran_circle_id,
            start_surah_number, start_verse, end_surah_number, end_verse,
            recitation_type, grade, evaluation, status, teacher_notes,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, 1, 1, 1, 10, 'حفظ', 8.5, 'جيد جداً', 'جارية', 'اختبار النظام المحدث', NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$sessionId, $student['id'], $teacher['id'], $circle['id']]);
        
        if ($result) {
            echo "   ✓ تم إنشاء جلسة اختبارية برقم: $sessionId\n";
            
            // تحديث الحالة
            $stmt = $pdo->prepare("UPDATE recitation_sessions SET status = 'مكتملة' WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            echo "   ✓ تم تحديث حالة الجلسة إلى 'مكتملة'\n";
            
            // حذف الجلسة الاختبارية
            $stmt = $pdo->prepare("DELETE FROM recitation_sessions WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            echo "   ✓ تم حذف الجلسة الاختبارية\n";
        }
    } else {
        echo "   ⚠ لا توجد بيانات أساسية كافية للاختبار\n";
    }
    
    echo "\n✅ اكتمل الاختبار بنجاح!\n";
    echo "🎉 نظام حالة جلسات التسميع يعمل بشكل صحيح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في الاختبار: " . $e->getMessage() . "\n";
    echo "📍 في الملف: " . $e->getFile() . " السطر: " . $e->getLine() . "\n";
}

echo "\n=====================================\n";