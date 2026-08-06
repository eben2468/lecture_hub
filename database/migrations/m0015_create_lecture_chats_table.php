<?php

namespace Database\Migrations;

use Database\Migration;

class M0015CreateLectureChatsTable extends Migration
{
    public function up(): void
    {
        $sqlChats = "CREATE TABLE IF NOT EXISTS `lecture_chats` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lecture_id` BIGINT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `message` TEXT NOT NULL,
            `is_question` TINYINT(1) DEFAULT 0,
            `is_answered` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_chats_lecture` (`lecture_id`),
            INDEX `idx_chats_question` (`is_question`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlChats);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `lecture_chats`;");
    }
}
