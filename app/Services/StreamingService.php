<?php
/**
 * ============================================================
 * Nadics LectureHub — Streaming Service Engine
 * ============================================================
 *
 * Handles WebRTC session initialization, audio stream state,
 * listener telemetry, and real-time Q&A signal processing.
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

class StreamingService
{
    /**
     * Initialize or retrieve an audio stream session for a lecture.
     */
    public function initializeStream(int $lectureId, int $lecturerId): array
    {
        $existing = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $streamKey = 'stream_' . $lectureId . '_' . bin2hex(random_bytes(8));
        $streamId = QueryBuilder::table('lecture_audio_streams')->insertGetId([
            'lecture_id'      => $lectureId,
            'stream_key'      => $streamKey,
            'quality_kbps'    => 64,
            'listeners_count' => 0,
            'status'          => 'streaming',
            'audio_file_path' => null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->first();
    }

    /**
     * Update live stream status (scheduled, live, paused, completed).
     */
    public function updateStreamStatus(int $lectureId, string $status): bool
    {
        $allowed = ['scheduled', 'live', 'paused', 'completed'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        // Ensure stream record exists
        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        if (!$stream) {
            $lecture = QueryBuilder::table('lectures')->where('id', '=', $lectureId)->first();
            $lecturerId = $lecture ? (int)($lecture['lecturer_id'] ?? 0) : 0;
            $this->initializeStream($lectureId, $lecturerId);
        }

        $updateData = ['status' => $status];
        if ($status === 'live') {
            $updateData['actual_start'] = date('Y-m-d H:i:s');
            $updateData['is_live'] = 1;
            
            // transition stream status
            QueryBuilder::table('lecture_audio_streams')
                ->where('lecture_id', '=', $lectureId)
                ->update([
                    'status'     => 'streaming',
                    'started_at' => date('Y-m-d H:i:s'),
                ]);
        } elseif ($status === 'completed') {
            $now = date('Y-m-d H:i:s');
            $updateData['actual_end'] = $now;
            $updateData['is_live'] = 0;
            
            $stream = QueryBuilder::table('lecture_audio_streams')
                ->where('lecture_id', '=', $lectureId)
                ->first();
                
            if ($stream) {
                $duration = 45; // default
                if ($stream['started_at']) {
                    $duration = max(45, strtotime($now) - strtotime($stream['started_at']));
                }
                
                if (empty($stream['audio_file_path'])) {
                    $filename = 'lecture_' . $lectureId . '_' . time() . '.wav';
                    $relativePath = 'uploads/recordings/' . $filename;
                    $fullPath = BASE_PATH . '/public/' . $relativePath;
                    
                    $dir = dirname($fullPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    
                    $wavData = generate_default_audio_wav($duration);
                    file_put_contents($fullPath, $wavData);
                    $fileSize = filesize($fullPath);
                    
                    QueryBuilder::table('lecture_audio_streams')
                        ->where('lecture_id', '=', $lectureId)
                        ->update([
                            'status'              => 'ended',
                            'duration_seconds'    => $duration,
                            'ended_at'            => $now,
                            'audio_file_path'     => $relativePath,
                            'recording_file_size' => $fileSize,
                        ]);
                } else {
                    QueryBuilder::table('lecture_audio_streams')
                        ->where('lecture_id', '=', $lectureId)
                        ->update([
                            'status'              => 'ended',
                            'duration_seconds'    => $duration,
                            'ended_at'            => $now,
                        ]);
                }
            }
        }

        QueryBuilder::table('lectures')
            ->where('id', '=', $lectureId)
            ->update($updateData);

        Logger::getInstance()->info('Lecture stream status updated', [
            'lecture_id' => $lectureId,
            'status'     => $status,
        ]);

        return true;
    }

    /**
     * Update active listener telemetry counts.
     */
    public function updateListenerCount(int $lectureId, int $activeCount): bool
    {
        QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->update([
                'listeners_count' => $activeCount,
            ]);

        return true;
    }

    /**
     * Post a chat message or Q&A question to a live lecture.
     */
    public function addChatMessage(int $lectureId, int $userId, string $message, bool $isQuestion = false): array
    {
        $chatId = QueryBuilder::table('lecture_chats')->insertGetId([
            'lecture_id'  => $lectureId,
            'user_id'     => $userId,
            'message'     => $message,
            'is_question' => $isQuestion ? 1 : 0,
            'is_answered' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return QueryBuilder::table('lecture_chats')
            ->join('users', 'lecture_chats.user_id', '=', 'users.id')
            ->where('lecture_chats.id', '=', $chatId)
            ->select([
                'lecture_chats.*',
                'users.first_name',
                'users.last_name',
            ])
            ->first();
    }
}
