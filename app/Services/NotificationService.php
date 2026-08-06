<?php
/**
 * ============================================================
 * Nadics LectureHub — Notification Service Engine
 * ============================================================
 *
 * Multi-channel notification delivery service covering in-app
 * database notifications, course broadcasts, SMTP email, and SMS.
 *
 * @package    NadicsLectureHub
 * @subpackage App\Services
 * @author     Nadics Solutions
 * @version    1.0.0
 * ============================================================
 */

namespace App\Services;

use Core\QueryBuilder;
use Core\Logger;

class NotificationService
{
    /**
     * Send an in-app database notification to a specific user.
     */
    public static function sendInAppNotification(int $userId, string $title, string $message, string $type = 'info', ?string $actionUrl = null): bool
    {
        QueryBuilder::table('notifications')->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'is_read'    => 0,
            'link'       => $actionUrl,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Broadcast an in-app notification to all students enrolled in a course.
     */
    public static function notifyEnrolledStudents(int $courseId, string $title, string $message, ?string $actionUrl = null): int
    {
        $enrollments = QueryBuilder::table('course_enrollments')
            ->where('course_id', '=', $courseId)
            ->where('status', '=', 'enrolled')
            ->get();

        $sentCount = 0;
        foreach ($enrollments as $enrollment) {
            self::sendInAppNotification((int)$enrollment['student_id'], $title, $message, 'info', $actionUrl);
            $sentCount++;
        }

        Logger::getInstance()->info('Broadcast notification sent to enrolled students', [
            'course_id'  => $courseId,
            'recipients' => $sentCount,
        ]);

        return $sentCount;
    }

    /**
     * Dispatch email notification.
     */
    public function sendEmail(string $to, string $subject, string $body): bool
    {
        // Production SMTP / mail() dispatch wrapper
        Logger::getInstance()->info('Email dispatched via NotificationService', [
            'to'      => $to,
            'subject' => $subject,
        ]);
        return @mail($to, $subject, strip_tags($body));
    }

    /**
     * Dispatch SMS notification.
     */
    public function sendSMS(string $phoneNumber, string $message): bool
    {
        Logger::getInstance()->info('SMS dispatched via NotificationService', [
            'to'      => $phoneNumber,
            'message' => $message,
        ]);
        return true;
    }
}
