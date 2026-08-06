<?php
/**
 * ============================================================
 * Nadics LectureHub — Quiz Controller
 * ============================================================
 *
 * Handles student quiz listing, active quiz attempts, automatic
 * grading, and student performance metrics tracking.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    1.0.0
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class QuizController extends Controller
{
    /**
     * Display all active quizzes and student's past attempts.
     */
    public function index(Request $request): void
    {
        $this->authorize(['student', 'lecturer', 'university_admin', 'super_admin']);

        $auth = Auth::getInstance();
        $userId = $auth->id();
        $role = $auth->role();

        // Retrieve quizzes (optionally filtered by course)
        $courseFilter = $request->query('course');
        $selectedCourse = null;

        $quizzesQuery = QueryBuilder::table('quizzes')
            ->join('courses', 'quizzes.course_id', '=', 'courses.id')
            ->select([
                'quizzes.*',
                'courses.code as course_code',
                'courses.title as course_title',
            ]);

        if ($role === 'student') {
            $enrolledCourseIds = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $userId)
                ->where('status', '=', 'enrolled')
                ->get();
            $ids = array_column($enrolledCourseIds, 'course_id');
            if (!empty($ids)) {
                $quizzesQuery->whereIn('quizzes.course_id', $ids);
            } else {
                $quizzesQuery->where('quizzes.course_id', '=', 0);
            }
            $quizzesQuery->where('quizzes.status', '=', 'published');
        } elseif ($role === 'lecturer') {
            $taughtCourses = QueryBuilder::table('course_lecturers')
                ->where('lecturer_id', '=', $userId)
                ->get();
            $courseIds = array_column($taughtCourses, 'course_id');
            if (!empty($courseIds)) {
                $quizzesQuery->whereIn('quizzes.course_id', $courseIds);
            }
        }

        if ($courseFilter) {
            $quizzesQuery->where('quizzes.course_id', '=', $courseFilter);
            $selectedCourse = QueryBuilder::table('courses')->where('id', '=', $courseFilter)->first();
        }

        $quizzes = $quizzesQuery->get();

        // Get past attempts for student
        $attempts = [];
        if ($role === 'student') {
            $attempts = QueryBuilder::table('quiz_attempts')
                ->where('student_id', '=', $userId)
                ->get();
        }

        $attemptsMap = [];
        foreach ($attempts as $att) {
            $attemptsMap[$att['quiz_id']] = $att;
        }

        // Get courses for creation modal
        $courses = [];
        if (in_array($role, ['lecturer', 'university_admin', 'super_admin'])) {
            if ($role === 'lecturer') {
                $courses = QueryBuilder::table('course_lecturers')
                    ->join('courses', 'course_lecturers.course_id', '=', 'courses.id')
                    ->where('course_lecturers.lecturer_id', '=', $userId)
                    ->select(['courses.id', 'courses.code', 'courses.title'])
                    ->get();
                if (empty($courses)) {
                    $courses = QueryBuilder::table('courses')
                        ->where('status', '=', 'active')
                        ->select(['id', 'code', 'title'])
                        ->get();
                }
            } else {
                $courses = QueryBuilder::table('courses')
                    ->where('status', '=', 'active')
                    ->select(['id', 'code', 'title'])
                    ->get();
            }
        }

        $this->view('quizzes.index', [
            'page_title'       => 'Online Quizzes',
            'page_description' => 'Assess your course knowledge with automated grading.',
            'quizzes'          => $quizzes,
            'attemptsMap'      => $attemptsMap,
            'courses'          => $courses,
            'userRole'         => $role,
            'selectedCourse'   => $selectedCourse,
        ]);
    }

    /**
     * Store a newly created quiz and its questions.
     */
    public function store(Request $request): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $validated = $this->validate($request, [
            'course_id'        => 'required|integer|exists:courses,id',
            'title'            => 'required|min:3|max:255',
            'duration_minutes' => 'required|integer|min:1|max:120',
            'pass_score'       => 'required|integer|min:1|max:100',
        ]);

        $status = $request->input('status', 'published');
        if (!in_array($status, ['draft', 'published'])) {
            $status = 'published';
        }

        $auth = Auth::getInstance();
        $lecturerId = $auth->id();

        // Ensure lecturer course mapping exists
        try {
            $mappingExists = QueryBuilder::table('course_lecturers')
                ->where('course_id', '=', $validated['course_id'])
                ->where('lecturer_id', '=', $lecturerId)
                ->exists();
            if (!$mappingExists) {
                $sessionRecord = QueryBuilder::table('academic_sessions')
                    ->where('is_current', '=', 1)
                    ->first() ?? QueryBuilder::table('academic_sessions')->first();
                $sessionId = $sessionRecord ? $sessionRecord['id'] : 1;

                QueryBuilder::table('course_lecturers')->insert([
                    'course_id'           => $validated['course_id'],
                    'lecturer_id'         => $lecturerId,
                    'academic_session_id' => $sessionId,
                    'is_coordinator'      => 1,
                    'created_at'          => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {}

        $quizId = QueryBuilder::table('quizzes')->insertGetId([
            'course_id'        => $validated['course_id'],
            'title'            => $validated['title'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_questions'  => 0, // Will be updated below
            'pass_score'       => $validated['pass_score'],
            'status'           => $status,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $totalQuestionsCount = 0;
        // Insert questions dynamically
        for ($i = 1; $i <= 100; $i++) {
            $questionText = $request->input('q' . $i . '_text');
            if ($questionText === null) {
                if ($i > 3 && !$request->has('q' . ($i + 1) . '_text')) {
                    break;
                }
                continue;
            }
            if (empty(trim($questionText))) continue;

            $options = [
                'a' => $request->input('q' . $i . '_opt_a', 'Option A'),
                'b' => $request->input('q' . $i . '_opt_b', 'Option B'),
                'c' => $request->input('q' . $i . '_opt_c', 'Option C'),
                'd' => $request->input('q' . $i . '_opt_d', 'Option D'),
            ];

            QueryBuilder::table('quiz_questions')->insert([
                'quiz_id'        => $quizId,
                'question_text'  => $questionText,
                'question_type'  => 'multiple_choice',
                'points'         => 10,
                'options_json'   => json_encode($options),
                'correct_answer' => $request->input('q' . $i . '_correct', 'a'),
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
            $totalQuestionsCount++;
        }

        // Update the total questions count in the quiz row
        QueryBuilder::table('quizzes')->where('id', '=', $quizId)->update([
            'total_questions' => $totalQuestionsCount
        ]);

        if ($status === 'published') {
            $notifService = new \App\Services\NotificationService();
            $notifService->notifyEnrolledStudents(
                (int)$validated['course_id'],
                'New Quiz Published: ' . $validated['title'],
                'A new online quiz has been published for your course. Duration: ' . $validated['duration_minutes'] . ' mins.'
            );
        }

        $msg = ($status === 'draft')
            ? 'Quiz saved as draft successfully. Students cannot see it until published.'
            : 'Quiz created and published to students successfully.';

        $this->redirectWithSuccess(url('/quizzes'), $msg);
    }

    /**
     * Publish or unpublish a quiz.
     */
    public function publish(Request $request, string $id): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $quiz = QueryBuilder::table('quizzes')->where('id', '=', $id)->first();
        if (!$quiz) {
            $this->redirectWithError(url('/quizzes'), 'Quiz not found.');
            return;
        }

        $newStatus = ($quiz['status'] === 'published') ? 'draft' : 'published';
        if ($request->has('status')) {
            $requestedStatus = $request->input('status');
            if (in_array($requestedStatus, ['draft', 'published', 'closed'])) {
                $newStatus = $requestedStatus;
            }
        }

        QueryBuilder::table('quizzes')->where('id', '=', $id)->update([
            'status' => $newStatus,
        ]);

        if ($newStatus === 'published') {
            $notifService = new \App\Services\NotificationService();
            $notifService->notifyEnrolledStudents(
                (int)$quiz['course_id'],
                'Quiz Published: ' . $quiz['title'],
                'An online quiz has been published for your course. Check your Quiz portal to take it.'
            );
        }

        $msg = ($newStatus === 'published')
            ? 'Quiz published to enrolled students successfully!'
            : 'Quiz is now saved as ' . ucfirst($newStatus) . '.';

        $referer = $_SERVER['HTTP_REFERER'] ?? url('/quizzes');
        $this->redirectWithSuccess($referer, $msg);
    }

    /**
     * Delete a quiz.
     */
    public function destroy(string $id): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);
        QueryBuilder::table('quizzes')->where('id', '=', $id)->delete();
        QueryBuilder::table('quiz_questions')->where('quiz_id', '=', $id)->delete();
        $this->redirectWithSuccess(url('/quizzes'), 'Quiz deleted successfully.');
    }

    /**
     * Display a specific quiz or its result if already attempted.
     */
    public function show(Request $request, string $id): void
    {
        $this->authorize(['student', 'lecturer', 'university_admin', 'super_admin']);

        $auth = Auth::getInstance();
        $studentId = $auth->id();
        $role = $auth->role();

        $quiz = QueryBuilder::table('quizzes')
            ->join('courses', 'quizzes.course_id', '=', 'courses.id')
            ->where('quizzes.id', '=', $id)
            ->select([
                'quizzes.*',
                'courses.code as course_code',
                'courses.title as course_title',
            ])
            ->first();

        if (!$quiz) {
            abort(404, 'Quiz not found.');
        }

        // Student access protection: draft quizzes & enrollment validation
        if ($role === 'student') {
            if (($quiz['status'] ?? 'published') !== 'published') {
                $this->redirectWithError(url('/quizzes'), 'This quiz is currently in draft mode and not available.');
                return;
            }

            $isEnrolled = QueryBuilder::table('course_enrollments')
                ->where('student_id', '=', $studentId)
                ->where('course_id', '=', $quiz['course_id'])
                ->where('status', '=', 'enrolled')
                ->exists();

            if (!$isEnrolled) {
                $this->redirectWithError(url('/quizzes'), 'You are not enrolled in the course for this quiz.');
                return;
            }
        }

        $attempt = QueryBuilder::table('quiz_attempts')
            ->where('quiz_id', '=', $id)
            ->where('student_id', '=', $studentId)
            ->first();

        $questions = QueryBuilder::table('quiz_questions')
            ->where('quiz_id', '=', $id)
            ->get();

        // Format options_json fields
        foreach ($questions as &$q) {
            if (is_string($q['options_json'])) {
                $q['options_json'] = json_decode($q['options_json'], true);
            }
        }

        $attempts = [];
        if (in_array($role, ['lecturer', 'university_admin', 'super_admin'])) {
            $attempts = QueryBuilder::table('quiz_attempts')
                ->join('users', 'quiz_attempts.student_id', '=', 'users.id')
                ->where('quiz_attempts.quiz_id', '=', $id)
                ->select([
                    'quiz_attempts.*',
                    'users.first_name',
                    'users.last_name',
                    'users.matric_staff_id',
                ])
                ->orderBy('quiz_attempts.completed_at', 'DESC')
                ->get();
        }

        $this->view('quizzes.show', [
            'page_title'       => $quiz['title'],
            'page_description' => 'Online multiple-choice assessment.',
            'quiz'             => $quiz,
            'questions'        => $questions,
            'attempt'          => $attempt,
            'attempts'         => $attempts,
            'userRole'         => $role,
        ]);
    }

    /**
     * Add a single question to an existing quiz (for lecturers).
     */
    public function addQuestion(Request $request, string $id): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $quiz = QueryBuilder::table('quizzes')->where('id', '=', $id)->first();
        if (!$quiz) {
            abort(404, 'Quiz not found.');
        }

        $validated = $this->validate($request, [
            'question_text' => 'required|min:3',
            'opt_a'         => 'required',
            'opt_b'         => 'required',
            'opt_c'         => 'required',
            'opt_d'         => 'required',
            'correct'       => 'required|in:a,b,c,d',
            'points'        => 'required|integer|min:1',
        ]);

        $options = [
            'a' => $validated['opt_a'],
            'b' => $validated['opt_b'],
            'c' => $validated['opt_c'],
            'd' => $validated['opt_d'],
        ];

        QueryBuilder::table('quiz_questions')->insert([
            'quiz_id'        => $id,
            'question_text'  => $validated['question_text'],
            'question_type'  => 'multiple_choice',
            'points'         => $validated['points'],
            'options_json'   => json_encode($options),
            'correct_answer' => $validated['correct'],
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        // Update total questions count in the quiz
        $currentCount = QueryBuilder::table('quiz_questions')->where('quiz_id', '=', $id)->count();
        QueryBuilder::table('quizzes')->where('id', '=', $id)->update([
            'total_questions' => $currentCount
        ]);

        $this->redirectWithSuccess(url('/quizzes/' . $id), 'Question added successfully.');
    }

    /**
     * Submit quiz responses and grade automatically.
     */
    public function submit(Request $request, string $id): void
    {
        $this->authorize(['student', 'super_admin']);

        $auth = Auth::getInstance();
        $studentId = $auth->id();

        $quiz = QueryBuilder::table('quizzes')->where('id', '=', $id)->first();
        if (!$quiz) {
            abort(404, 'Quiz not found.');
        }

        // Prevent multiple attempts
        $existing = QueryBuilder::table('quiz_attempts')
            ->where('quiz_id', '=', $id)
            ->where('student_id', '=', $studentId)
            ->exists();

        if ($existing) {
            $this->redirectWithError(url('/quizzes/' . $id), 'You have already attempted this quiz.');
            return;
        }

        $questions = QueryBuilder::table('quiz_questions')
            ->where('quiz_id', '=', $id)
            ->get();

        $score = 0;
        $totalPossible = 0;
        $submittedAnswers = [];

        foreach ($questions as $q) {
            $answerKey = 'question_' . $q['id'];
            $studentAnswer = $request->input($answerKey);
            
            $submittedAnswers[$q['id']] = $studentAnswer;
            $points = (int)($q['points'] ?? 10);
            $totalPossible += $points;

            if ($studentAnswer !== null && strtolower(trim($studentAnswer)) === strtolower(trim($q['correct_answer']))) {
                $score += $points;
            }
        }

        QueryBuilder::table('quiz_attempts')->insert([
            'quiz_id'        => $id,
            'student_id'     => $studentId,
            'score'          => $score,
            'total_possible' => $totalPossible,
            'answers_json'   => json_encode($submittedAnswers),
            'started_at'     => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            'completed_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->redirectWithSuccess(
            url('/quizzes/' . $id),
            "Quiz submitted successfully! You scored {$score} / {$totalPossible} points."
        );
    }

    /**
     * Delete a single question from a quiz.
     */
    public function deleteQuestion(Request $request, string $quizId, string $questionId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        $quiz = QueryBuilder::table('quizzes')->where('id', '=', $quizId)->first();
        if (!$quiz) {
            $this->redirectWithError(url('/quizzes'), 'Quiz not found.');
            return;
        }

        QueryBuilder::table('quiz_questions')
            ->where('id', '=', $questionId)
            ->where('quiz_id', '=', $quizId)
            ->delete();

        $currentCount = QueryBuilder::table('quiz_questions')->where('quiz_id', '=', $quizId)->count();
        QueryBuilder::table('quizzes')->where('id', '=', $quizId)->update([
            'total_questions' => $currentCount
        ]);

        $this->redirectWithSuccess(url('/quizzes/' . $quizId), 'Question removed from quiz.');
    }
}
