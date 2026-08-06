<?php

namespace Database\Seeders;

class DatabaseSeeder
{
    public function run(): void
    {
        (new RoleAndPermissionSeeder())->run();
        (new UniversitySeeder())->run();
        (new UserSeeder())->run();
        (new CourseSeeder())->run();
        (new QuizSeeder())->run();
    }
}
