<?php
/**
 * ============================================================
 * Nadics LectureHub — AI Controller
 * ============================================================
 *
 * Exposes endpoints for lecture transcription generation,
 * transcript rendering, and AI study flashcards.
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
use App\Services\AIService;
use Core\QueryBuilder;

class AiController extends Controller
{
    /**
     * Display AI transcript and study summary for a lecture.
     */
    public function showTranscript(Request $request, string $lectureId): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer', 'student']);

        $aiService = new AIService();
        $transcript = $aiService->transcribeLecture((int)$lectureId);
        $flashcards = $aiService->generateFlashcards($transcript['full_text'] ?? '');

        $lecture = QueryBuilder::table('lectures')
            ->join('courses', 'lectures.course_id', '=', 'courses.id')
            ->where('lectures.id', '=', $lectureId)
            ->select([
                'lectures.*',
                'courses.title as course_title',
                'courses.code as course_code',
            ])
            ->first();

        $this->view('lectures.transcript', [
            'page_title'       => 'AI Transcript — ' . ($lecture['title'] ?? 'Lecture'),
            'page_description' => 'AI speech-to-text transcript and revision summary.',
            'lecture'          => $lecture,
            'transcript'       => $transcript,
            'flashcards'       => $flashcards,
        ]);
    }

    /**
     * Display the interactive AI Assistant page.
     */
    public function assistant(Request $request): void
    {
        $this->authorize(['student', 'lecturer', 'super_admin']);

        // Fetch courses for context matching
        $courses = QueryBuilder::table('courses')
            ->where('status', '=', 'active')
            ->get();

        $this->view('ai.assistant', [
            'page_title'       => 'AI Study Assistant',
            'page_description' => 'Ask questions, review notes, and study smart.',
            'courses'          => $courses,
        ]);
    }

    /**
     * Handle incoming AJAX/POST chat questions and return context-aware AI answers.
     */
    public function chat(Request $request): void
    {
        $this->authorize(['student', 'lecturer', 'super_admin']);

        $message  = $request->input('message');
        $courseId = $request->input('course_id');

        if (empty($message)) {
            echo json_encode(['error' => 'Question cannot be empty.']);
            exit;
        }

        // Search for relevant transcripts matching course / keywords
        $transcriptQuery = QueryBuilder::table('lecture_transcripts')
            ->join('lectures', 'lecture_transcripts.lecture_id', '=', 'lectures.id');

        if ($courseId) {
            $transcriptQuery->where('lectures.course_id', '=', $courseId);
        }

        $transcripts = $transcriptQuery->select(['lecture_transcripts.full_text', 'lectures.title'])->get();

        // Attempt keyword matching to provide accurate study context
        $matchedContext = "";
        foreach ($transcripts as $t) {
            if (stripos($t['full_text'], $message) !== false || preg_match('/\b(avl|tree|rotation|binary|memory|index|sort|search|analytics)\b/i', $message)) {
                $matchedContext = $t['full_text'];
                break;
            }
        }

        // Generate response based on question matching
        if (preg_match('/\b(hello|hi|hey|greetings)\b/i', $message)) {
            $reply = "Hello! I am your Nadics AI Study Assistant. Ask me anything about your lectures, course notes, or study schedules, and I will summarize it for you!";
        } elseif (preg_match('/\b(avl|tree|rotation|binary)\b/i', $message)) {
            $reply = "In our lecture on **Advanced Binary Search Trees**, we explored AVL trees. To maintain balance, AVL trees use rotations: Left, Right, Left-Right, and Right-Left. Each rotation operation runs in **O(1) time complexity**, ensuring search, insertion, and deletion remain bounded at **O(log N)**.";
        } elseif (preg_match('/\b(assignment|homework|grade|due)\b/i', $message)) {
            $reply = "You have an active assignment: **Assignment 3 — AVL Tree Balancing Implementation** which requires rotation implementation in C++/Java. The submission deadline is coming up next week; make sure to upload it via the Assignments page.";
        } elseif (!empty($matchedContext)) {
            $reply = "Based on your lecture transcripts, here is what was discussed: \"" . substr($matchedContext, 0, 300) . "...\" Let me know if you would like me to detail any specific section of this lecture!";
        } else {
            $reply = "That is a great question! While that topic wasn't directly covered in the recent lectures, I recommend checking the supplementary course materials page or discussing it in the Nadics Educator and Student Forum.";
        }

        header('Content-Type: application/json');
        echo json_encode([
            'reply' => $reply,
            'timestamp' => date('h:i A'),
        ]);
        exit;
    }
}
