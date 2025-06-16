<?php
echo "Starting test...\n";

try {
    require_once __DIR__ . '/bootstrap/app.php';
    echo "Laravel bootstrapped successfully\n";
    
    echo "Testing database connection...\n";
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=garb_project', 'root', '');
    echo "Database connected successfully\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM teachers');
    $result = $stmt->fetch();
    echo "Teachers count: " . $result['count'] . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM students');
    $result = $stmt->fetch();
    echo "Students count: " . $result['count'] . "\n";
    
    $stmt = $pdo->query('SELECT id, name, quran_circle_id, mosque_id FROM teachers LIMIT 1');
    $teacher = $stmt->fetch();
    if ($teacher) {
        echo "First teacher:\n";
        echo "- ID: " . $teacher['id'] . "\n";
        echo "- Name: " . $teacher['name'] . "\n";
        echo "- Circle ID: " . ($teacher['quran_circle_id'] ?? 'NULL') . "\n";
        echo "- Mosque ID: " . ($teacher['mosque_id'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
