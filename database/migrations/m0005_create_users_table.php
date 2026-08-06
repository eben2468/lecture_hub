<?php

namespace Database\Migrations;

use Database\Migration;

class M0005CreateUsersTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `university_id` BIGINT UNSIGNED NULL,
            `department_id` BIGINT UNSIGNED NULL,
            `role_id` INT UNSIGNED NOT NULL,
            `matric_staff_id` VARCHAR(100) NULL,
            `first_name` VARCHAR(100) NOT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `phone` VARCHAR(30) NULL,
            `password` VARCHAR(255) NOT NULL,
            `gender` ENUM('male', 'female', 'other') NULL,
            `profile_photo` VARCHAR(255) NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `last_login_at` DATETIME NULL,
            `last_login_ip` VARCHAR(45) NULL,
            `remember_token` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
            INDEX `idx_users_email` (`email`),
            INDEX `idx_users_matric_staff` (`matric_staff_id`),
            INDEX `idx_users_role` (`role_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `users`;");
    }
}
