<?php

namespace Database\Migrations;

use Database\Migration;

class M0010CreateLecturesAndStreamsTables extends Migration
{
    public function up(): void
    {
        // 1. Lectures Table
        $sqlLectures = "CREATE TABLE IF NOT EXISTS `lectures` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `course_id` BIGINT UNSIGNED NOT NULL,
            `lecturer_id` BIGINT UNSIGNED NOT NULL,
            `lecture_hall_id` BIGINT UNSIGNED NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `scheduled_start` DATETIME NOT NULL,
            `scheduled_end` DATETIME NOT NULL,
            `actual_start` DATETIME NULL,
            `actual_end` DATETIME NULL,
            `status` ENUM('scheduled', 'live', 'completed', 'cancelled') DEFAULT 'scheduled',
            `is_live` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`lecture_hall_id`) REFERENCES `lecture_halls`(`id`) ON DELETE SET NULL,
            INDEX `idx_lectures_status` (`status`),
            INDEX `idx_lectures_start` (`scheduled_start`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlLectures);

        // 2. Lecture Audio Streams Table
        $sqlStreams = "CREATE TABLE IF NOT EXISTS `lecture_audio_streams` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lecture_id` BIGINT UNSIGNED NOT NULL,
            `stream_key` VARCHAR(100) NOT NULL UNIQUE,
            `quality_kbps` INT UNSIGNED DEFAULT 64,
            `listeners_count` INT UNSIGNED DEFAULT 0,
            `status` ENUM('idle', 'streaming', 'ended', 'failed') DEFAULT 'idle',
            `audio_file_path` VARCHAR(255) NULL,
            `recording_file_size` BIGINT UNSIGNED DEFAULT 0,
            `duration_seconds` INT UNSIGNED DEFAULT 0,
            `started_at` DATETIME NULL,
            `ended_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlStreams);

        // 3. Lecture Transcripts Table (AI)
        $sqlTranscripts = "CREATE TABLE IF NOT EXISTS `lecture_transcripts` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lecture_id` BIGINT UNSIGNED NOT NULL,
            `full_text` LONGTEXT NULL,
            `word_count` INT UNSIGNED DEFAULT 0,
            `summary` TEXT NULL,
            `ai_key_points` JSON NULL,
            `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlTranscripts);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `lecture_transcripts`;");
        $this->execute("DROP TABLE IF EXISTS `lecture_audio_streams`;");
        $this->execute("DROP TABLE IF EXISTS `lectures`;");
    }
}
