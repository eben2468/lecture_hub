<?php

namespace Database\Migrations;

use Database\Migration;

class M0004CreateRolesAndPermissionsTables extends Migration
{
    public function up(): void
    {
        // 1. Roles table
        $sqlRoles = "CREATE TABLE IF NOT EXISTS `roles` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `description` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlRoles);

        // 2. Permissions table
        $sqlPermissions = "CREATE TABLE IF NOT EXISTS `permissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `group_name` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlPermissions);

        // 3. Role-Permissions Pivot table
        $sqlRolePermissions = "CREATE TABLE IF NOT EXISTS `role_permissions` (
            `role_id` INT UNSIGNED NOT NULL,
            `permission_id` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`role_id`, `permission_id`),
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->execute($sqlRolePermissions);
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `role_permissions`;");
        $this->execute("DROP TABLE IF EXISTS `permissions`;");
        $this->execute("DROP TABLE IF EXISTS `roles`;");
    }
}
