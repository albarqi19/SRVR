<?php

// Drop and recreate tables
DB::statement('DROP TABLE IF EXISTS student_curriculum_progress');

echo "Tabla student_curriculum_progress eliminada correctamente.\n";

// Verificar si todas las tablas necesarias existen
echo "Verificando tablas dependientes...\n";
$tablesExist = DB::select("SHOW TABLES LIKE 'curriculum_plans'");
if (empty($tablesExist)) {
    echo "ERROR: La tabla curriculum_plans no existe.\n";
} else {
    echo "Tabla curriculum_plans encontrada.\n";
}

$tablesExist = DB::select("SHOW TABLES LIKE 'student_curricula'");
if (empty($tablesExist)) {
    echo "ERROR: La tabla student_curricula no existe.\n";
} else {
    echo "Tabla student_curricula encontrada.\n";
}

// Crear la tabla
DB::statement("CREATE TABLE student_curriculum_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_curriculum_id BIGINT UNSIGNED NOT NULL,
    curriculum_plan_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    completion_date DATE NULL,
    status ENUM('قيد التنفيذ', 'مكتمل') NOT NULL DEFAULT 'قيد التنفيذ',
    completion_percentage FLOAT NOT NULL DEFAULT '0',
    teacher_notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

echo "Tabla student_curriculum_progress creada sin restricciones.\n";

// Añadir las restricciones de clave foránea
DB::statement("ALTER TABLE student_curriculum_progress
    ADD CONSTRAINT student_curriculum_progress_student_curriculum_id_foreign
    FOREIGN KEY (student_curriculum_id) REFERENCES student_curricula (id) ON DELETE CASCADE");

echo "Clave foránea student_curriculum_id añadida.\n";

DB::statement("ALTER TABLE student_curriculum_progress
    ADD CONSTRAINT student_curriculum_progress_curriculum_plan_id_foreign
    FOREIGN KEY (curriculum_plan_id) REFERENCES curriculum_plans (id) ON DELETE CASCADE");

echo "Clave foránea curriculum_plan_id añadida.\n";

echo "¡Proceso completado con éxito!\n";
