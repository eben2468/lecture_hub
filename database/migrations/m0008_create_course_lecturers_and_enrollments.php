<?php

namespace Database\Migrations;

use Database\Migration;

class M0008CreateCourseLecturersAndEnrollments extends Migration
{
    public function up(): void
    {
        // 1. Course Lecturers assignment
        $sqlLecturers = "CREATE TABLE IF NOT EXISTS `course_lecturers` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `lecturer_id` BIGINT UNSIGNED NOT NULL,
            `academic_session_id` BIGINT UNSIGNED NOT NULL,
            `is_coordinator` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_course_lecturer_session` (`course_id`, `lecturer_id`, `academic_session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlLecturers);

        // 2. Course Enrollments (Students)
        $sqlEnrollments = "CREATE TABLE IF NOT EXISTS `course_enrollments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `student_id` BIGINT UNSIGNED NOT NULL,
            `academic_session_id` BIGINT UNSIGNED NOT NULL,
            `status` ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
            `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`academic_session_id`) REFERENCES `academic_sessions`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_student_course_session` (`course_id`, `student_id`, `academic_session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlEnrollments);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `course_enrollments`;");
        $this->execute("DROP TABLE IF EXISTS `course_lecturers`;");
    }
}
