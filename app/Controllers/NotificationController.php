<?php
/**
 * Nadics LectureHub — Notification Controller
 * Handles: listing, mark-read, mark-all-read, and delete notifications.
 */

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Auth;
use Core\QueryBuilder;

class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $user = Auth::getInstance()->user();
        $role = Auth::getInstance()->role();

        $notifications = [];
        try {
            $notifications = QueryBuilder::table('notifications')
                ->where('user_id', '=', $user['id'])
                ->orderBy('created_at', 'DESC')
                ->limit(50)
                ->get();
        } catch (\Exception $e) {
            // Table might not exist yet — show empty state
        }

        $this->view('notifications.index', [
            'page_title'       => 'Notifications',
            'page_description' => 'View all your system and academic notifications.',
            'user'             => $user,
            'user_role'        => $role,
            'notifications'    => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read (AJAX)
     */
    public function markRead(Request $request, int $id): void
    {
        $user = Auth::getInstance()->user();

        try {
            QueryBuilder::table('notifications')
                ->where('id', '=', $id)
                ->where('user_id', '=', $user['id'])
                ->update(['is_read' => 1]);
        } catch (\Exception $e) {
            // Silently fail
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllRead(Request $request): void
    {
        $user = Auth::getInstance()->user();

        try {
            QueryBuilder::table('notifications')
                ->where('user_id', '=', $user['id'])
                ->where('is_read', '=', 0)
                ->update(['is_read' => 1]);
        } catch (\Exception $e) {
            // Silently fail
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Delete a notification (AJAX)
     */
    public function delete(Request $request, int $id): void
    {
        $user = Auth::getInstance()->user();

        try {
            QueryBuilder::table('notifications')
                ->where('id', '=', $id)
                ->where('user_id', '=', $user['id'])
                ->delete();
        } catch (\Exception $e) {
            // Silently fail
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
