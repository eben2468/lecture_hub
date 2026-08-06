<?php

namespace Database\Migrations;

use Database\Migration;

class M0001CreateUniversitiesTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `universities` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `code` VARCHAR(50) NOT NULL UNIQUE,
            `domain` VARCHAR(255) NULL UNIQUE,
            `logo_url` VARCHAR(255) NULL,
            `address` TEXT NULL,
            `city` VARCHAR(100) NULL,
            `state` VARCHAR(100) NULL,
            `country` VARCHAR(100) DEFAULT 'Nigeria',
            `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_universities_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `universities`;");
    }
}
