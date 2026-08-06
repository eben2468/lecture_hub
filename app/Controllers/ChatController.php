<?php
/**
 * ============================================================
 * Nadics LectureHub — Live Chat & Q&A Controller
 * ============================================================
 *
 * Real-time classroom message exchange and question moderation.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Controllers
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-22
 * ============================================================
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\QueryBuilder;
use Core\Auth;

class ChatController extends Controller
{
    /**
     * Fetch messages for a live lecture.
     */
    public function index(Request $request, string $lectureId): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer', 'student']);

        $chats = QueryBuilder::table('lecture_chats')
            ->join('users', 'lecture_chats.user_id', '=', 'users.id')
            ->where('lecture_id', '=', $lectureId)
            ->select([
                'lecture_chats.*',
                'users.first_name',
                'users.last_name',
            ])
            ->orderBy('lecture_chats.id', 'ASC')
            ->get();

        $this->jsonSuccess('Chats fetched', ['chats' => $chats]);
    }

    /**
     * Send a live message or question.
     */
    public function send(Request $request, string $lectureId): void
    {
        $this->authorize(['super_admin', 'university_admin', 'lecturer', 'student']);

        $validated = $this->validate($request, [
            'message'     => 'required|min:1|max:1000',
            'is_question' => 'nullable|integer',
        ]);

        $auth = Auth::getInstance();

        $chatId = QueryBuilder::table('lecture_chats')->insert([
            'lecture_id'  => $lectureId,
            'user_id'     => $auth->id(),
            'message'     => $validated['message'],
            'is_question' => $validated['is_question'] ?? 0,
            'is_answered' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $user = $auth->user();

        $this->jsonSuccess('Message sent', [
            'id'          => $chatId,
            'first_name'  => $user['first_name'] ?? 'User',
            'last_name'   => $user['last_name'] ?? '',
            'message'     => $validated['message'],
            'is_question' => $validated['is_question'] ?? 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark a question as answered.
     */
    public function markAnswered(Request $request, string $chatId): void
    {
        $this->authorize(['lecturer', 'university_admin', 'super_admin']);

        QueryBuilder::table('lecture_chats')
            ->where('id', '=', $chatId)
            ->update(['is_answered' => 1]);

        $this->jsonSuccess('Question marked as answered.');
    }
}
