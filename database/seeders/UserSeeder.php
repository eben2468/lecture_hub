<?php

namespace Database\Seeders;

use Core\QueryBuilder;

class UserSeeder
{
    public function run(): void
    {
        $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);

        $users = [
            // Super Admin
            [
                'role_id'     => 1, // super_admin
                'first_name'  => 'Nadics',
                'last_name'   => 'Administrator',
                'email'       => 'admin@nadics.com',
                'password'    => $passwordHash,
                'is_active'   => 1,
            ],
            // Lecturer
            [
                'university_id' => 1,
                'department_id' => 1,
                'role_id'       => 3, // lecturer
                'matric_staff_id' => 'STF/CSC/001',
                'first_name'    => 'Oluwaseun',
                'last_name'     => 'Adebayo',
                'email'         => 'lecturer@unilag.edu.ng',
                'password'      => $passwordHash,
                'is_active'     => 1,
            ],
            // Student
            [
                'university_id' => 1,
                'department_id' => 1,
                'role_id'       => 4, // student
                'matric_staff_id' => '210407001',
                'first_name'    => 'Chidi',
                'last_name'     => 'Okonkwo',
                'email'         => 'student@unilag.edu.ng',
                'password'      => $passwordHash,
                'is_active'     => 1,
            ],
        ];

        foreach ($users as $user) {
            if (!QueryBuilder::table('users')->where('email', '=', $user['email'])->exists()) {
                QueryBuilder::table('users')->insert($user);
            }
        }

        echo "   ✔ Default Users Seeded (Admin, Lecturer, Student — Password: Password123!).\n";
    }
}
