<?php

namespace Database\Migrations;

use Database\Migration;

class M0009CreateLectureHallsTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `lecture_halls` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `university_id` BIGINT UNSIGNED NOT NULL,
            `building_name` VARCHAR(255) NOT NULL,
            `hall_number` VARCHAR(100) NOT NULL,
            `capacity` INT UNSIGNED NOT NULL DEFAULT 100,
            `has_wifi` TINYINT(1) DEFAULT 1,
            `gps_lat` DECIMAL(10, 8) NULL,
            `gps_lng` DECIMAL(11, 8) NULL,
            `status` ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_univ_hall` (`university_id`, `building_name`, `hall_number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->execute($sql);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `lecture_halls`;");
    }
}
