<?php

namespace Database\Migrations;

use Database\Migration;

class M0016AddPerformanceIndexes extends Migration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `course_enrollments` ADD INDEX `idx_enrollments_student` (`student_id`);");
        $this->execute("ALTER TABLE `course_lecturers` ADD INDEX `idx_lecturers_user` (`lecturer_id`);");
        $this->execute("ALTER TABLE `course_materials` ADD INDEX `idx_materials_uploader` (`uploaded_by`);");
        $this->execute("ALTER TABLE `course_materials` ADD INDEX `idx_materials_course` (`course_id`);");
        $this->execute("ALTER TABLE `assignments` ADD INDEX `idx_assignments_creator` (`created_by`);");
        $this->execute("ALTER TABLE `assignments` ADD INDEX `idx_assignments_due` (`due_date`);");
        $this->execute("ALTER TABLE `assignment_submissions` ADD INDEX `idx_submissions_submitted` (`submitted_at`);");
        $this->execute("ALTER TABLE `lecture_chats` ADD INDEX `idx_chats_created` (`created_at`);");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE `lecture_chats` DROP INDEX `idx_chats_created`;");
        $this->execute("ALTER TABLE `assignment_submissions` DROP INDEX `idx_submissions_submitted`;");
        $this->execute("ALTER TABLE `assignments` DROP INDEX `idx_assignments_due`;");
        $this->execute("ALTER TABLE `assignments` DROP INDEX `idx_assignments_creator`;");
        $this->execute("ALTER TABLE `course_materials` DROP INDEX `idx_materials_course`;");
        $this->execute("ALTER TABLE `course_materials` DROP INDEX `idx_materials_uploader`;");
        $this->execute("ALTER TABLE `course_lecturers` DROP INDEX `idx_lecturers_user`;");
        $this->execute("ALTER TABLE `course_enrollments` DROP INDEX `idx_enrollments_student`;");
    }
}
