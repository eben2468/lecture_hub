<?php
/**
 * Nadics LectureHub — Quiz Data Seeder
 */

namespace Database\Seeders;

use Core\QueryBuilder;

class QuizSeeder
{
    public function run(): void
    {
        $course = QueryBuilder::table('courses')->where('code', '=', 'CSC 301')->first();
        if (!$course) return;

        // Check if quiz exists
        $existing = QueryBuilder::table('quizzes')->where('title', '=', 'AVL Tree Rotations Quiz')->exists();
        if ($existing) return;

        // 1. Insert AVL Quiz
        $quizId = QueryBuilder::table('quizzes')->insertGetId([
            'course_id'        => $course['id'],
            'title'            => 'AVL Tree Rotations Quiz',
            'duration_minutes' => 10,
            'total_questions'  => 3,
            'pass_score'       => 60,
            'status'           => 'published',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        // Questions for AVL Tree Quiz
        $questions = [
            [
                'quiz_id'        => $quizId,
                'question_text'  => 'What is the worst-case time complexity of searching in a balanced AVL tree?',
                'question_type'  => 'multiple_choice',
                'points'         => 10,
                'options_json'   => json_encode([
                    'a' => 'O(1)',
                    'b' => 'O(log N)',
                    'c' => 'O(N)',
                    'd' => 'O(N log N)'
                ]),
                'correct_answer' => 'b',
            ],
            [
                'quiz_id'        => $quizId,
                'question_text'  => 'An AVL tree requires rotation when the balance factor of any node becomes:',
                'question_type'  => 'multiple_choice',
                'points'         => 10,
                'options_json'   => json_encode([
                    'a' => 'Greater than 1 or less than -1',
                    'b' => 'Exactly 0',
                    'c' => 'Equal to 1 or -1',
                    'd' => 'Greater than 2'
                ]),
                'correct_answer' => 'a',
            ],
            [
                'quiz_id'        => $quizId,
                'question_text'  => 'True or False: A single left rotation (L rotation) is performed when an imbalance is caused by an insertion into the right subtree of the right child.',
                'question_type'  => 'true_false',
                'points'         => 10,
                'options_json'   => json_encode([
                    'true' => 'True',
                    'false' => 'False'
                ]),
                'correct_answer' => 'true',
            ],
        ];

        foreach ($questions as $q) {
            QueryBuilder::table('quiz_questions')->insert($q);
        }

        echo "   ✔ AVL Tree Rotations Quiz & Questions Seeded.\n";
    }
}
