<?php

namespace Database\Migrations;

use Database\Migration;

class M0011CreateAttendanceTables extends Migration
{
    public function up(): void
    {
        // 1. Attendance Sessions (QR code generation per lecture)
        $sqlSessions = "CREATE TABLE IF NOT EXISTS `attendance_sessions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `lecture_id` BIGINT UNSIGNED NOT NULL,
            `qr_code_hash` VARCHAR(100) NOT NULL UNIQUE,
            `expires_at` DATETIME NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`lecture_id`) REFERENCES `lectures`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlSessions);

        // 2. Attendance Records
        $sqlRecords = "CREATE TABLE IF NOT EXISTS `attendance_records` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `attendance_session_id` BIGINT UNSIGNED NOT NULL,
            `student_id` BIGINT UNSIGNED NOT NULL,
            `verification_method` ENUM('qr_scan', 'geolocation', 'manual', 'facial') DEFAULT 'qr_scan',
            `gps_lat` DECIMAL(10, 8) NULL,
            `gps_lng` DECIMAL(11, 8) NULL,
            `status` ENUM('present', 'late', 'absent', 'excused') DEFAULT 'present',
            `verified_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`attendance_session_id`) REFERENCES `attendance_sessions`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_student_attendance_session` (`attendance_session_id`, `student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlRecords);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `attendance_records`;");
        $this->execute("DROP TABLE IF EXISTS `attendance_sessions`;");
    }
}
