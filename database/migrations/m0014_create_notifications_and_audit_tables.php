<?php

namespace Database\Migrations;

use Database\Migration;

class M0014CreateNotificationsAndAuditTables extends Migration
{
    public function up(): void
    {
        // 1. Notifications
        $sqlNotifications = "CREATE TABLE IF NOT EXISTS `notifications` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `type` VARCHAR(50) DEFAULT 'info', -- info, success, warning, error
            `is_read` TINYINT(1) DEFAULT 0,
            `link` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            INDEX `idx_notifications_user_unread` (`user_id`, `is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlNotifications);

        // 2. Activity Logs (Audit Trail)
        $sqlActivity = "CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NULL,
            `action` VARCHAR(100) NOT NULL,
            `entity_type` VARCHAR(100) NULL,
            `entity_id` BIGINT UNSIGNED NULL,
            `description` TEXT NULL,
            `ip_address` VARCHAR(45) NULL,
            `user_agent` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
            INDEX `idx_activity_user` (`user_id`),
            INDEX `idx_activity_action` (`action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlActivity);

        // 3. System Settings
        $sqlSettings = "CREATE TABLE IF NOT EXISTS `system_settings` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT NULL,
            `group_name` VARCHAR(50) DEFAULT 'general',
            `description` VARCHAR(255) NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlSettings);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `system_settings`;");
        $this->execute("DROP TABLE IF EXISTS `activity_logs`;");
        $this->execute("DROP TABLE IF EXISTS `notifications`;");
    }
}
