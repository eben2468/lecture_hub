<?php

namespace Database\Seeders;

use Core\QueryBuilder;

class CourseSeeder
{
    public function run(): void
    {
        $dept = QueryBuilder::table('departments')->first();
        if (!$dept) return;

        $deptId = $dept['id'];

        $courses = [
            [
                'department_id' => $deptId,
                'code'          => 'CSC 301',
                'title'         => 'Data Structures and Algorithms',
                'description'   => 'Comprehensive study of stacks, queues, trees, graphs, and sorting algorithms.',
                'credit_unit'   => 3,
                'level'         => 300,
                'semester'      => 'first',
            ],
            [
                'department_id' => $deptId,
                'code'          => 'CSC 305',
                'title'         => 'Database Systems Architecture',
                'description'   => 'Relational model, SQL optimization, transactions, and indexing strategies.',
                'credit_unit'   => 3,
                'level'         => 300,
                'semester'      => 'first',
            ],
            [
                'department_id' => $deptId,
                'code'          => 'CSC 401',
                'title'         => 'Artificial Intelligence & Neural Networks',
                'description'   => 'Introduction to machine learning, search space optimization, and deep learning.',
                'credit_unit'   => 4,
                'level'         => 400,
                'semester'      => 'first',
            ],
        ];

        foreach ($courses as $c) {
            if (!QueryBuilder::table('courses')->where('code', '=', $c['code'])->exists()) {
                QueryBuilder::table('courses')->insert($c);
            }
        }

        echo "   ✔ Sample Courses Seeded.\n";
    }
}
