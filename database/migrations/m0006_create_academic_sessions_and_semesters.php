<?php

namespace Database\Migrations;

use Database\Migration;

class M0006CreateAcademicSessionsAndSemesters extends Migration
{
    public function up(): void
    {
        // 1. Academic Sessions
        $sqlSessions = "CREATE TABLE IF NOT EXISTS `academic_sessions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `university_id` BIGINT UNSIGNED NOT NULL,
            `name` VARCHAR(100) NOT NULL, -- e.g. 2025/2026
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `is_current` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlSessions);

        // 2. Semesters
        $sqlSemesters = "CREATE TABLE IF NOT EXISTS `semesters` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `academic_session_id` BIGINT UNSIGNED NOT NULL,
            `name` VARCHAR(100) NOT NULL, -- e.g. First Semester
            `semester_number` TINYINT NOT NULL DEFAULT 1, -- 1 or 2
            `is_current` TINYINT(1) DEFAULT 0,
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlSemesters);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `semesters`;");
        $this->execute("DROP TABLE IF EXISTS `academic_sessions`;");
    }
}
