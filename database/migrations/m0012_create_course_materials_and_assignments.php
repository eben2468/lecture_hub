<?php

namespace Database\Migrations;

use Database\Migration;

class M0012CreateCourseMaterialsAndAssignments extends Migration
{
    public function up(): void
    {
        // 1. Course Materials
        $sqlMaterials = "CREATE TABLE IF NOT EXISTS `course_materials` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `lecture_id` BIGINT UNSIGNED NULL,
            `uploaded_by` BIGINT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `file_size` INT UNSIGNED NOT NULL,
            `mime_type` VARCHAR(100) NOT NULL,
            `download_count` INT UNSIGNED DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlMaterials);

        // 2. Assignments
        $sqlAssignments = "CREATE TABLE IF NOT EXISTS `assignments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `max_score` INT UNSIGNED NOT NULL DEFAULT 100,
            `due_date` DATETIME NOT NULL,
            `file_attachment` VARCHAR(255) NULL,
            `status` ENUM('draft', 'published', 'closed') DEFAULT 'published',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlAssignments);

        // 3. Assignment Submissions
        $sqlSubmissions = "CREATE TABLE IF NOT EXISTS `assignment_submissions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `assignment_id` BIGINT UNSIGNED NOT NULL,
            `student_id` BIGINT UNSIGNED NOT NULL,
            `file_path` VARCHAR(255) NOT NULL,
            `score` DECIMAL(5, 2) NULL,
            `feedback` TEXT NULL,
            `graded_by` BIGINT UNSIGNED NULL,
            `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `graded_at` DATETIME NULL,
            FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            UNIQUE KEY `unique_student_assignment` (`assignment_id`, `student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlSubmissions);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `assignment_submissions`;");
        $this->execute("DROP TABLE IF EXISTS `assignments`;");
        $this->execute("DROP TABLE IF EXISTS `course_materials`;");
    }
}
