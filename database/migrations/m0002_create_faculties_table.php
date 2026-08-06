<?php

namespace Database\Migrations;

use Database\Migration;

class M0002CreateFacultiesTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `faculties` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `university_id` BIGINT UNSIGNED NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) NOT NULL,
            `dean_name` VARCHAR(150) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_univ_faculty_code` (`university_id`, `code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `faculties`;");
    }
}
