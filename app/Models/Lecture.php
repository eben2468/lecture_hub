<?php

namespace App\Models;

use Core\Model;
use Core\QueryBuilder;
use App\Services\StreamingService;

class Lecture extends Model
{
    protected string $table = 'lectures';

    protected array $fillable = [
        'course_id',
        'lecturer_id',
        'lecture_hall_id',
        'title',
        'description',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'is_live',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (isset($attributes['id'])) {
            $this->attributes['id'] = $attributes['id'];
        }
    }

    /**
     * Get lecture ID safely.
     */
    public function getId(): int
    {
        return (int)($this->attributes['id'] ?? $this->id ?? 0);
    }

    /**
     * Studio audio intake constraints for WebRTC audio broadcast.
     */
    public static function getAudioIntakeConstraints(): array
    {
        return [
            'echoCancellation'   => true,
            'noiseSuppression'   => true,
            'autoGainControl'    => true,
            'sampleRate'         => 48000,
            'channelCount'       => 2,
            'audioBitsPerSecond' => 128000,
        ];
    }

    /**
     * Check if lecture is currently live streaming.
     */
    public function isLive(): bool
    {
        $status = $this->attributes['status'] ?? '';
        $isLive = (int)($this->attributes['is_live'] ?? 0);
        $stream = $this->recording();
        $streamStatus = $stream['status'] ?? '';

        return ($status === 'live' || $isLive === 1 || $streamStatus === 'streaming');
    }

    /**
     * Alias for isLive().
     */
    public function isCurrentlyLive(): bool
    {
        return $this->isLive();
    }

    /**
     * Get associated course details.
     */
    public function course(): ?array
    {
        return QueryBuilder::table('courses')
            ->where('id', '=', $this->attributes['course_id'] ?? 0)
            ->first();
    }

    /**
     * Get associated lecturer details.
     */
    public function lecturer(): ?array
    {
        return QueryBuilder::table('users')
            ->where('id', '=', $this->attributes['lecturer_id'] ?? 0)
            ->first();
    }

    /**
     * Get or initialize the recording/stream record for this lecture.
     */
    public function recording(): ?array
    {
        $lectureId = $this->getId();
        if (!$lectureId) {
            return null;
        }

        $stream = QueryBuilder::table('lecture_audio_streams')
            ->where('lecture_id', '=', $lectureId)
            ->first();

        // If completed or stream record missing, ensure stream record exists
        if (!$stream && ($this->attributes['status'] ?? '') === 'completed') {
            $streamKey = 'stream_' . $lectureId . '_' . bin2hex(random_bytes(8));
            $streamId = QueryBuilder::table('lecture_audio_streams')->insertGetId([
                'lecture_id'       => $lectureId,
                'stream_key'       => $streamKey,
                'quality_kbps'     => 128,
                'listeners_count'  => 0,
                'status'           => 'ended',
                'audio_file_path'  => null,
                'duration_seconds' => 45,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
            $stream = QueryBuilder::table('lecture_audio_streams')->where('id', '=', $streamId)->first();
        }

        return $stream;
    }

    /**
     * Check if a valid audio recording file exists for this lecture.
     */
    public function hasRecording(): bool
    {
        $rec = $this->recording();
        if (!$rec || empty($rec['audio_file_path'])) {
            return false;
        }
        $fullPath = BASE_PATH . '/public/' . ltrim($rec['audio_file_path'], '/');
        return file_exists($fullPath) && filesize($fullPath) > 0;
    }

    /**
     * Get the public URL for the audio recording.
     */
    public function getAudioUrl(): string
    {
        $rec = $this->recording();
        if ($rec && !empty($rec['audio_file_path'])) {
            return url(ltrim($rec['audio_file_path'], '/'));
        }
        return '';
    }

    /**
     * Get configured audio quality bitrate in kbps.
     */
    public function getAudioBitrate(): int
    {
        $rec = $this->recording();
        return (int)($rec['quality_kbps'] ?? 128);
    }

    /**
     * Get formatted recording duration (HH:MM:SS).
     */
    public function getFormattedDuration(): string
    {
        $rec = $this->recording();
        $dur = (int)($rec['duration_seconds'] ?? 0);
        if ($dur <= 0) return '00:00:00';
        $h = floor($dur / 3600);
        $m = floor(($dur % 3600) / 60);
        $s = $dur % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    /**
     * Get formatted recording file size in MB.
     */
    public function getFormattedFileSize(): string
    {
        $rec = $this->recording();
        $size = (int)($rec['recording_file_size'] ?? 0);
        if ($size <= 0 && $rec && !empty($rec['audio_file_path'])) {
            $fullPath = BASE_PATH . '/public/' . ltrim($rec['audio_file_path'], '/');
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
            }
        }
        if ($size <= 0) return '—';
        return number_format($size / (1024 * 1024), 2) . ' MB';
    }

    /**
     * Start live broadcast for this lecture.
     */
    public function startBroadcast(): bool
    {
        $service = new StreamingService();
        return $service->updateStreamStatus($this->getId(), 'live');
    }

    /**
     * Stop broadcast for this lecture.
     */
    public function stopBroadcast(): bool
    {
        $service = new StreamingService();
        return $service->updateStreamStatus($this->getId(), 'completed');
    }

    /**
     * Update active listener telemetry count.
     */
    public function updateStreamTelemetry(int $listenerCount): bool
    {
        $service = new StreamingService();
        return $service->updateListenerCount($this->getId(), $listenerCount);
    }

    /**
     * Fetch active live lectures across the system.
     */
    public static function getLiveLectures(): array
    {
        return QueryBuilder::table('lectures')
            ->leftJoin('lecture_audio_streams', 'lectures.id', '=', 'lecture_audio_streams.lecture_id')
            ->whereRaw("(lectures.status = 'live' OR lectures.is_live = 1 OR lecture_audio_streams.status = 'streaming')")
            ->select(['lectures.*', 'lecture_audio_streams.status as stream_status'])
            ->get();
    }
}
