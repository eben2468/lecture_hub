<?php

namespace Database\Migrations;

use Database\Migration;

class M0007CreateCoursesTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `courses` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `department_id` BIGINT UNSIGNED NOT NULL,
            `code` VARCHAR(50) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `credit_unit` TINYINT UNSIGNED NOT NULL DEFAULT 3,
            `level` INT UNSIGNED NOT NULL DEFAULT 100, -- 100, 200, 300, 400, 500
            `semester` ENUM('first', 'second') DEFAULT 'first',
            `status` ENUM('active', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_dept_course_code` (`department_id`, `code`),
            INDEX `idx_courses_level` (`level`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `courses`;");
    }
}
