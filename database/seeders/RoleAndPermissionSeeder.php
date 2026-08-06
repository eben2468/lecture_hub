<?php

namespace Database\Seeders;

use Core\QueryBuilder;

class RoleAndPermissionSeeder
{
    public function run(): void
    {
        // 1. Insert Roles
        $roles = [
            ['id' => 1, 'name' => 'Super Administrator', 'slug' => 'super_admin', 'description' => 'Full platform control'],
            ['id' => 2, 'name' => 'University Administrator', 'slug' => 'university_admin', 'description' => 'University level management'],
            ['id' => 3, 'name' => 'Lecturer', 'slug' => 'lecturer', 'description' => 'Faculty member / Academic staff'],
            ['id' => 4, 'name' => 'Student', 'slug' => 'student', 'description' => 'Enrolled university student'],
        ];

        foreach ($roles as $role) {
            if (!QueryBuilder::table('roles')->where('slug', '=', $role['slug'])->exists()) {
                QueryBuilder::table('roles')->insert($role);
            }
        }

        // 2. Insert Permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'user.view', 'group_name' => 'User Management'],
            ['name' => 'Create Users', 'slug' => 'user.create', 'group_name' => 'User Management'],
            ['name' => 'Edit Users', 'slug' => 'user.edit', 'group_name' => 'User Management'],
            ['name' => 'Delete Users', 'slug' => 'user.delete', 'group_name' => 'User Management'],

            // Course Management
            ['name' => 'View Courses', 'slug' => 'course.view', 'group_name' => 'Course Management'],
            ['name' => 'Create Courses', 'slug' => 'course.create', 'group_name' => 'Course Management'],
            ['name' => 'Edit Courses', 'slug' => 'course.edit', 'group_name' => 'Course Management'],
            ['name' => 'Delete Courses', 'slug' => 'course.delete', 'group_name' => 'Course Management'],

            // Lecture Management
            ['name' => 'View Lectures', 'slug' => 'lecture.view', 'group_name' => 'Lecture Management'],
            ['name' => 'Create Lectures', 'slug' => 'lecture.create', 'group_name' => 'Lecture Management'],
            ['name' => 'Stream Audio', 'slug' => 'lecture.stream', 'group_name' => 'Lecture Management'],
            ['name' => 'Listen Audio', 'slug' => 'lecture.listen', 'group_name' => 'Lecture Management'],

            // Attendance
            ['name' => 'Generate QR Attendance', 'slug' => 'attendance.generate', 'group_name' => 'Attendance'],
            ['name' => 'Scan QR Attendance', 'slug' => 'attendance.scan', 'group_name' => 'Attendance'],
            ['name' => 'View Attendance Reports', 'slug' => 'attendance.report', 'group_name' => 'Attendance'],
        ];

        foreach ($permissions as $p) {
            if (!QueryBuilder::table('permissions')->where('slug', '=', $p['slug'])->exists()) {
                QueryBuilder::table('permissions')->insert($p);
            }
        }

        echo "   ✔ Roles & Permissions Seeded.\n";
    }
}
