<?php

// Conexión a la base de datos usando las credenciales del archivo .env
$host = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: 'garb_project';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    echo "Intentando conectar a la base de datos...\n";
    echo "Host: $host, Database: $database, Username: $username\n";
    
    $db = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexión exitosa a la base de datos.\n";
    
    // Eliminar la tabla si existe
    $db->exec("DROP TABLE IF EXISTS student_curriculum_progress");
    echo "Tabla student_curriculum_progress eliminada correctamente.\n";
    
    // Crear la tabla con la referencia correcta
    $sql = "CREATE TABLE student_curriculum_progress (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_curriculum_id BIGINT UNSIGNED NOT NULL,
        curriculum_plan_id BIGINT UNSIGNED NOT NULL,
        start_date DATE NOT NULL,
        completion_date DATE NULL,
        status ENUM('قيد التنفيذ', 'مكتمل') NOT NULL DEFAULT 'قيد التنفيذ',
        completion_percentage FLOAT NOT NULL DEFAULT '0',
        teacher_notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        CONSTRAINT student_curriculum_progress_student_curriculum_id_foreign
            FOREIGN KEY (student_curriculum_id) REFERENCES student_curricula (id) ON DELETE CASCADE,
        CONSTRAINT student_curriculum_progress_curriculum_plan_id_foreign
            FOREIGN KEY (curriculum_plan_id) REFERENCES curriculum_plans (id) ON DELETE CASCADE
    )";
    
    $db->exec($sql);
    echo "Tabla student_curriculum_progress creada correctamente con las restricciones de llave foránea.\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
