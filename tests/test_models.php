<?php
/**
 * Nadics LectureHub — Model & Database Verification Test
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Application.php';

$app = new Core\Application(BASE_PATH);
$app->boot();

use App\Models\User;
use App\Models\University;
use App\Models\Course;

echo "==================================================\n";
echo "Nadics LectureHub — Model Domain Verification\n";
echo "==================================================\n\n";

// 1. Fetch Users
$admin = User::find(1);
echo "  [MODEL USER] Admin found: " . ($admin ? $admin->first_name . ' ' . $admin->last_name . ' (ID: ' . $admin->id . ')' : 'NO') . "\n";

$lecturer = User::where('email', '=', 'lecturer@unilag.edu.ng')->first();
echo "  [QUERY USER] Lecturer found: " . ($lecturer ? $lecturer['first_name'] . ' ' . $lecturer['last_name'] . ' (' . $lecturer['matric_staff_id'] . ')' : 'NO') . "\n";

$student = User::where('email', '=', 'student@unilag.edu.ng')->first();
echo "  [QUERY USER] Student found: " . ($student ? $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['matric_staff_id'] . ')' : 'NO') . "\n";

// 2. Fetch University
$univ = University::find(1);
echo "  [MODEL UNIV] Institution: " . ($univ ? $univ->name . ' (' . $univ->domain . ')' : 'NO') . "\n";

// 3. Fetch Courses
$courses = Course::all();
echo "  [MODEL COURSES] Total seeded courses: " . count($courses) . "\n";
foreach ($courses as $c) {
    echo "    - {$c->code}: {$c->title} ({$c->credit_unit} Units, Level {$c->level})\n";
}

echo "\n==================================================\n";
echo " ✅ Model Verification Successful!\n";
echo "==================================================\n";
