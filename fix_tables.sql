-- Eliminar la tabla si existe
DROP TABLE IF EXISTS student_curriculum_progress;

-- Crear la tabla con las restricciones de clave foránea correctas
CREATE TABLE student_curriculum_progress (
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
);
