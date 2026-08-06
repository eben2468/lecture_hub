<?php

namespace Database\Migrations;

use Database\Migration;

class M0003CreateDepartmentsTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `departments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `faculty_id` BIGINT UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) NOT NULL,
            `hod_name` VARCHAR(150) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`faculty_id`) REFERENCES `faculties`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_faculty_dept_code` (`faculty_id`, `code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `departments`;");
    }
}
