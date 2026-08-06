<?php

namespace Database\Migrations;

use Database\Migration;

class M0013CreateQuizzesAndAttemptsTables extends Migration
{
    public function up(): void
    {
        // 1. Quizzes
        $sqlQuizzes = "CREATE TABLE IF NOT EXISTS `quizzes` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `lecture_id` BIGINT UNSIGNED NULL,
            `title` VARCHAR(255) NOT NULL,
            `duration_minutes` INT UNSIGNED DEFAULT 15,
            `total_questions` INT UNSIGNED DEFAULT 5,
            `pass_score` INT UNSIGNED DEFAULT 60,
            `status` ENUM('draft', 'published', 'closed') DEFAULT 'published',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlQuizzes);

        // 2. Quiz Questions
        $sqlQuestions = "CREATE TABLE IF NOT EXISTS `quiz_questions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `quiz_id` BIGINT UNSIGNED NOT NULL,
            `question_text` TEXT NOT NULL,
            `question_type` ENUM('multiple_choice', 'true_false', 'short_answer') DEFAULT 'multiple_choice',
            `points` INT UNSIGNED DEFAULT 10,
            `options_json` JSON NOT NULL, -- Array of options
            `correct_answer` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlQuestions);

        // 3. Quiz Attempts
        $sqlAttempts = "CREATE TABLE IF NOT EXISTS `quiz_attempts` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `quiz_id` BIGINT UNSIGNED NOT NULL,
            `student_id` BIGINT UNSIGNED NOT NULL,
            `score` DECIMAL(5, 2) NOT NULL,
            `total_possible` DECIMAL(5, 2) NOT NULL,
            `answers_json` JSON NULL,
            `started_at` DATETIME NOT NULL,
            `completed_at` DATETIME NOT NULL,
            FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlAttempts);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `quiz_attempts`;");
        $this->execute("DROP TABLE IF EXISTS `quiz_questions`;");
        $this->execute("DROP TABLE IF EXISTS `quizzes`;");
    }
}
